<?php
/**
 * 通知メールを送る
 *
 * web 上処理と分離する
 * 
 */
App::uses('AppShell','Console/Command');
App::uses('ComponentCollection', 'Controller'); 
App::uses('CommonComponent', 'Controller/Component'); 

class FileDeleteShell extends AppShell {
	public $uses = array(	'User',
							'Content',
							'Uploadfile',
							'Eventlog',
						);

//	public $Controller = null;
	public $me = null;
	
	public function startup() {
        /**
        * CommonComponent.phpの呼び出し
        */
        $collection = new ComponentCollection(); 
        $this->Common = new CommonComponent($collection); 
		parent::startup();
		$this->me = $this->getShellId();
        CakeSession::write('auth',$this->me);
	}
	
    public function main() {
        //ログ
        $additional_text = '(前)';
        $free_space = $this->Common->getDiskFree(Configure::read('Upfile.dir'),true);
        $this->writeDiskSpace(
            array(
                'event_action' => 'ディスク残容量'.$additional_text,
                'remark' => 'Shell',
                'result' => $free_space ,
        )); 


		$msg = ">>>>>>>>>> The batch processing is starting.  [".date("F d Y H:i:s", time() )."]";
		$this->out($msg,1,Shell::NORMAL);
		$dirpass =  $this->Uploadfile->getTargetPath();
		$this->out("upload dir :".$dirpass,1,Shell::NORMAL );
		$this->_proc();
		$msg = ">>>>>>>>>> The batch processing is　ending.  [".date("F d Y H:i:s", time() )."]";
		$this->out($msg,1,Shell::NORMAL);
		// ログ書き込み
		$logid = $this->writeLog(
			array(
				'event_action' => 'クリーンアップ',
				'remark' => 'Shell',
				'result' => '完了',
            ));
            
        //ログ
        $additional_text = '(後)';
        $free_space = $this->Common->getDiskFree(Configure::read('Upfile.dir'),true);
        $this->writeDiskSpace(
            array(
                'event_action' => 'ディスク残容量'.$additional_text,
                'remark' => 'Shell',
                'result' => $free_space ,
        )); 
    }

	/**
	 * getShellId
	 * @todo 	shell 起動のときのID（管理者）
	 * 
	 */
	public function getShellId(){
		try{
			$me = $this->User->find('first',
				array(	'conditions' => array ('group_id' => 1),
						'recursive' => -1));
//$this->log($me);
			return $me['User'];			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}
	
	/**
	 * getOptionParser
	 * @todo 	パラメータ解析
	 * -d yyyy-mm-dd で指定の日付までを期限切れとする
	 * AppShell にいれてみる
	 */
	 
//	public function getOptionParser(){
//		$parser = parent::getOptionParser();
//		$parser->addOption('date' , array(
//			'short' => 'd',
//			'required' => false,
//			'default' => null,
//			'help' => __('Target date. default = today'),
//			));
//		return $parser;	
//	}
	/**
	 * _proc
	 * @todo 	メインプロセス
	 *
	 */
	public function _proc(){
		try{
			$date = null;
$this->log($this->params,LOG_DEBUG);
			if(isset($this->params['date'])){
$this->log('--- param ',LOG_DEBUG);
				$date = $this->params['date'];
//			} else {
//				$date = date('Y-m-d');
//$this->log('--- NO param ['.$date.']',LOG_DEBUG);
			}
			// 承認があればチェック
			$this->loadModel('Approval');
			$clist = $this->Approval->getExpirationList($date);
$this->log('clist start ===================[',LOG_DEBUG);
$this->log($clist,LOG_DEBUG);
$this->log(']=================== clist end',LOG_DEBUG);
$this->log($date,LOG_DEBUG);
			foreach($clist as $id => $cid){
				$data = array('Approval' => array('id' => $id));
//$this->log($data,LOG_DEBUG);
//$this->log($this->me,LOG_DEBUG);
				// 期限切れ却下のときは、承認依頼された人が却下したことにする
				$msg = 'Rejected by System (Because of Expdate)';
				$rc = $this->Approval->aprv_ng($data,'shell',$msg);

				if($rc){
					// 送信ステータス：却下
					$this->loadModel('Content');
					$this->Content->setStatus($cid,VALUE_Status_Aprv_RjctAuto);

					$logid = $this->writeLog(
						array(
							'type' => 'Approval',
							'content_id' => $rc['Approval']['content_id'],
							'event_action' => '却下',
							'remark' => 'Shell',
							'result' => '成功',
						));
					// 依頼メール送信
					$cid = $rc['Approval']['content_id'];
					$this->loadModel('Mailqueue');
					$rc = $this->Mailqueue->putQueue('aprv_x',$cid, $logid);
					$this->out('aprv_ng finish OK',1,Shell::NORMAL); 
				} else {
					$logid = $this->writeLog(
						array(
							'type' => 'Approval',
							'content_id' => $rc['Approval']['content_id'],
							'event_action' => '却下',
							'remark' => 'Shell',
							'result' => '失敗',
						));
					$this->out('aprv_ng finish NG',1,Shell::NORMAL); 
					// 失敗したらメールを送らない
				}					
//				$cnt++;	
			}
			
			// 指定の日付（デフォルトは今日）で期限切れ処理
			$list = $this->Content->getExpirationList($date);
$this->log($list,LOG_DEBUG);
			if(count($list)>0){
				// 期限切れのコンテンツがあればフラグを立てる
				$msg = ">>>>>>>>>> The Expend Contents list.";
				$this->out($msg,1,Shell::NORMAL); 
				$this->Content->setExpFlg($list);
			}
			// DBから有効なファイル一覧を拾う
			$list_del = $this->Uploadfile->getDeleteFileListFromDb($list);
			$this->out($list_del,1,Shell::VERBOSE);

			$list_alive = $this->Uploadfile->getFileListFromDb();
			$this->out($list_alive,1,Shell::VERBOSE);	

			$list_files = $this->Uploadfile->getFileListFromDir();
			$this->out($list_files,1,Shell::VERBOSE);

			$list = $this->Uploadfile->getUunlinkFiles($list_del,$list_alive,$list_files);
			$this->out($list,1,Shell::VERBOSE);
		} catch(Exception $e){
$this->out(__FILE__ .':'. __LINE__ .': '. $e->getMessage(),1,Shell::QUIET);
		}
	}
	

}
