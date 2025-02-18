<?php
App::uses('AppModel', 'Model');
/**
 * Filelog Model
 *
 * @property Eventlogs $Eventlogs
 */
class Datalog extends AppModel {

/**
 * Display field
 *
 * @var string
 */
//	public $displayField = 'filename';


	//The Associations below have been created with all possible keys, those that are not needed can be removed
/**
 * sanitizeItems
 * 		sanitize したい項目を定義すると、appModel で自動的にやってくれる。
 * @var array : フィールド名 => html (true = タグを削除 / false = タグをエスケープ)
 *             or array( 'html' => true ,       // true / false / 'info' = 一部タブ許容
 *                       'serialize' => true ,  // true / false
 *                       'encode' => 'base64'   // 'base64' / 'none' 
 *                     )
 *
 */
	var $sanitizeItems = array(	'filename' => true,
								'filepath' => false,	// filepath に変更
								);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Eventlog' => array(
			'className' => 'Eventlog',
			'foreignKey' => 'eventlog_id',
			'conditions' => '',
			'fields' => '',
			'order' => '',
		),
	);

/**
 * setSend
 * 		送信のタイミングでアップロードファイル情報をログに出力する
 * @param array $data : 関連イベントログ
 * @param int   $cid  : コンテンツID
 * @return  array 	整形ログデータ
 *
 */
	function setSend($data,$cid){
		try{
			$this->loadModel('Content');
			$this->Content->recursive = 1;
			$content = $this->Content->findById($cid);
			if(empty($content)) return $data;
			$rtn = array();
			$data['Eventlog']['up_total'] = $content['Content']['uploadfile_totalsize'];
			foreach($content['Uploadfile'] as $k => $v){
				$new = $this->Create();
				if(Hash::check($data,'Eventlog.user_id')){
					$new[$this->name]['user_id'] = $data['Eventlog']['user_id'];
				} else {
					// おそらく shell
					$new[$this->name]['user_id'] = null;
				}
				$new[$this->name]['content_id'] = $cid;
				$new[$this->name]['uploadfile_id'] = $v['id'];
				$new[$this->name]['filename'] = $v['name'];
				$new[$this->name]['filetype'] = $v['mime_type'];
				$new[$this->name]['filesize'] = $v['size'];
				$new[$this->name]['filepath'] = $v['path'];
				$new[$this->name]['status_code'] = $v['error'];	// 2014.12.18追加

				// 0:今回アップロード / 1:すでにあるものを使いまわし　2014.12.25 追加
				$new[$this->name]['is_copy'] = $this->is_first($v['path']);
				$rtn[$k] = $new;
			}
			$data[$this->name] = $rtn;
	//$this->log($data);
	//$this->log(__FILE__ .':'. __LINE__ .': next ');
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $data;
	
	}
/**
 * setUploadLog
 * 		送信エラーを書き込む
 * @param int   $logid  : ログID
 * @param array $data : データ
 * @return  int 	書き込み行数
 *
 */
	function setUploadLog($logid,$data){
		try{
			$this->loadModel('Eventlog');
			$this->Eventlog->recursive = 1;
			$eventlog = $this->Eventlog->findById($logid);
//debug($eventlog);            
			if(empty($eventlog)) return 0;
			$rtn = 0;
			foreach($data['Content']['Uploadfile'] as $k => $v){
                if(($v['Uploadfile']['error'] == UPLOAD_ERR_OK) ||
                   ($v['Uploadfile']['error'] == UPLOAD_ERR_NO_FILE)){
                } else {
				$new = $this->Create();
                    if(Hash::check($eventlog,'Eventlog.user_id')){
                        $new[$this->name]['user_id'] = $eventlog['Eventlog']['user_id'];
                    } else {
                        // おそらく shell
                        $new[$this->name]['user_id'] = null;
                    }
                    $new[$this->name]['eventlog_id'] = $logid;
                    $new[$this->name]['filename'] = Hash::check($v,'Uploadfile.name') ? $v['Uploadfile']['name'] : '';
                    $new[$this->name]['filetype'] = Hash::check($v,'Uploadfile.mime_type') ?  $v['Uploadfile']['mime_type'] : '';
                    $new[$this->name]['filesize'] = Hash::check($v,'Uploadfile.size') ?  $v['Uploadfile']['size'] : 0;
                    $new[$this->name]['filepath'] = Hash::check($v,'Uploadfile.path') ?  $v['Uploadfile']['path'] : '';
                    $new[$this->name]['status_code'] = Hash::check($v,'Uploadfile.error') ?  $v['Uploadfile']['error'] : '';	// 2014.12.18追加

                    // 0:今回アップロード / 1:すでにあるものを使いまわし　2014.12.25 追加
                    $new[$this->name]['is_copy'] = Hash::check($v,'Uploadfile.path') ? $this->is_first($v['Uploadfile']['path']) : 0;
                    if($this->save($new)){
                        $rtn++;
                    }
                }
			}
	//$this->log($data);
	//$this->log(__FILE__ .':'. __LINE__ .': next ');
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $rtn;
	
	}

/**
 * setSend
 * 		ファイルが今回アップロードされたか、すでに存在するか調べる
 *		is_copy 項目に設定
 * @param string $path : 内部ファイル名
 * @return  int ： 0 初めて　/　>0 使いまわし
 *
 */
	function is_first($path = null){
		try{
			$data = $this->find('first',
				array(	'conditions' => array( 'filepath' => $path ),
						'recursive' => -1,
						));
			return(count($data));			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return 0;
	}
	
}
