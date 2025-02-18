<?php
App::uses('AppModel', 'Model');
/**
 * Maillog Model
 *
 * @property Eventlog $Eventlog
 */
class Maillog extends AppModel {


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
	var $sanitizeItems = array(	'mail_data' => array('encode' => 'base64'));
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
			'order' => ''
		)
	);

	function insertLog($rtn = null, $headers = null, $data = null){
		try{

			$newData = $this->create($data);
			$charset = '';
			$encode = '';
			$data = '';
			if(empty($rtn)){
				// メール送信エラー
				$newData[$this->name]['result'] = '失敗'; 
				$charset = $headers['Content-Type'];
				$encode = $headers['Content-Transfer-Encoding'];
				$data = $headers['message'];
				if(CakePlugin::loaded('Errmails')){
					$this->loadModel('Errmails.Errmail');
					$r = $this->Errmail->chkSMTPErr($headers);
//debug('--- errmail ['.$r.']');
				}					
			} else {
				$newData[$this->name]['result'] = '成功'; 
				$charset = $headers['Content-Type'];
				$encode = $headers['Content-Transfer-Encoding'];
				$data = $rtn['headers'] . "\n" . $rtn['message'];	
			}
			$newData[$this->name]['mail_charset'] = $charset;
			$newData[$this->name]['encode'] = $encode;
			$newData[$this->name]['mail_data'] = $data;
			$newData[$this->name]['created'] = null;
			// ドメインだけの項目を追加　2014.11.27
			$newData[$this->name]['domain_from'] = preg_replace('/^(.+)@/','',$newData[$this->name]['mail_from']);
			$newData[$this->name]['domain_to'] = preg_replace('/^(.+)@/','',$newData[$this->name]['mail_to']);
			$newData[$this->name]['lang'] = $this->getLang();
			
			// メールを送るが１か所にまとめてテストしたいとき
			if(VALUE_Mail_Transport == 'Smtp' && defined('VALUE_Mail_TEST_TO')){
				$newData[$this->name]['mail_to'] .= '[Debug To:'.VALUE_Mail_TEST_TO.']';
			}

			// バッチ送信かリアルタイム送信かのフラグ
			if(isset($_SERVER['HTTP_HOST'])){
				// この項目があればリアルタイム
				$newData[$this->name]['is_shell'] = false;
			} else {
				// なければバッチ
				$newData[$this->name]['is_shell'] = true;
			}
			
			$this->unbindModel(array('belongsTo' => array('Eventlog')),true); 
			if($this->save($newData)){
				$insertid = $this->getLastInsertID();
				return ($insertid);
			} else {
$this->log(__FILE__ .':'. __LINE__ .': save err');
				return false;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}	

	function setEventlogId($id,$evlogid){
		try{
//$this->log("Eventlog save start id[".$id."] evid[".$evlogid."]");	
			$this->id = $id;
			return($this->saveField('eventlog_id',$evlogid));
		} catch(Exception $e){
$this->log(__FILE__ .':'. __LINE__ .'Eventlog save err:'.$e->getMessage());
			return false;
		}
	}
	
}
