<?php
/**
 * ユーザの有効期限の残りが一定期間をきったら通知メールを送る
 *
 * web 上処理と分離する
 */
App::uses('AppShell','Console/Command');

class AlertMailShell extends AppShell {
	public $uses = array(	'Mailqueue',
							'SendMail',
							'User',
							'Role');
	private $me = array();
	
    public function main() {
//print_r('FULL_BASE_URL['.FULL_BASE_URL.']');
		$this->out('alert start',1,Shell::NORMAL);
		$this->out(var_dump($this->params),1,Shell::VERBOSE);
		$this->me = $this->getShellId();
		$this->_proc();
    }

	
	/**
	 * getOptionParser
	 * @todo 	パラメータ解析
	 *
	 */
	public function getOptionParser(){
		$parser = parent::getOptionParser();
		$parser->addOption('point' , array(
			'short' => 'p',
			'default' => '1 month',
			'help' => __('Point of alert mail.'),
			));
		return $parser;	
	}
	
	/**
	 * _proc
	 * @todo 	メインプロセス
	 *
	 */
	public function _proc(){
		try{
			$this->out('proc start',1,Shell::VERBOSE);
			$point = 1;
			if(isset($this->params['point'])){
				$point = $this->params['point'];
				if(is_numeric($point)){
					$point = $point. ' day';
				}
				$this->out('point is set['.$point.']',1,Shell::VERBOSE);
			}
			$this->out('point ['.$point.']',1,Shell::NORMAL);
			
			// 調べたい有効期限（今日の日付から換算）
			$glist = $this->Role->getGroupList(array('controller'=>'users','action'=>'expEnd'));
			// １日前にする
			$bingo = date('Y-m-d', strtotime($point . ' -1 day'));
debug($bingo);
			// この日に最終日となるユーザを探す
			$list = $this->User->get_expdate_users($glist,$bingo);
debug($list);			
			$event_action = '期限切れ予告メール';
			$template = 'alert_exp';			// 期限切れ予告テンプレート
			if($point <= 0){
				$event_action = '期限切れ通知メール';
				$template = 'alert_expend';		// 期限切れ通知テンプレート
			}
			// 当該ユーザがいれば送信
			if(count($list) > 0){
				$logid = $this->writeLog(
					array(
						'login_id' => $this->me['email'],
						'type' => 'Alert',
						'event_action' => $event_action,
						'remark' => 'Shell',
						'result' => '成功',
					));
				foreach($list as $k => $user){
					// お知らせメール送信
					$uid = $user['User']['id'];
					$this->loadModel('Mailqueue');
					$rc = $this->Mailqueue->putQueue($template,$uid, $logid);
					$this->out('alert_exp finish OK',1,Shell::NORMAL); 
				}
			}
			
		} catch(Exception $e){
			$this->out(__FILE__ .':'. __LINE__ .': '. $e->getMessage(),1,Shell::QUIET);
		}
	}

	
	// メール送信テスト
	public function _mail_test(){
		$this->Sendmail->test();
	}
	
}
