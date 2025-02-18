<?php
/**
 * 通知メールを送る
 *
 * web 上処理と分離する
 */
App::uses('AppShell','Console/Command');

class SendNoticeShell extends AppShell {
	public $uses = array(	'Mailqueue',
							'SendMail');
	
    public function main() {
//print_r('FULL_BASE_URL['.FULL_BASE_URL.']');
		$this->out('notice start',1,Shell::NORMAL);
		$this->out(var_dump($this->params),1,Shell::VERBOSE);
		$this->_proc();
    }

	
	/**
	 * getOptionParser
	 * @todo 	パラメータ解析
	 *
	 */
	public function getOptionParser(){
		$parser = parent::getOptionParser();
		$parser->addOption('max' , array(
			'short' => 'm',
			'default' => 1,
			'help' => __('Maximum of queue data to do.'),
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
			$max = 1;
			if(isset($this->params['max'])){
				$max = $this->params['max'];
				$this->out('max is set['.$max.']',1,Shell::VERBOSE);
				if(!is_numeric($max)){
					$this->out('max is set not Numeric['.$max.']',1,Shell::VERBOSE);
					$max = 1;
				}
			}
			$this->out('max ['.$max.']',1,Shell::NORMAL);
			$list = $this->Mailqueue->getQueue($max);
			$this->out($list,1,Shell::NORMAL);
			if(is_array($list)) {
				foreach($list as $k => $v){
					$this->out('send : '.$k,1,Shell::VERBOSE);
					$rtn = $this->SendMail->sendFromQueue($k);
					$this->out('send : '.$rtn,1,Shell::VERBOSE);		
				}
			} else {
				$this->out('mail queue is empty',1,Shell::QUIET);		
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
