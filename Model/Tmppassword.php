<?php
App::uses('AppModel', 'Model');
/**
 * Errmail Model
 *
 */
class Tmppassword extends AppModel {

/**
 * Use database config
 *
 * @var string
 */
//	public $useDbConfig = 'default';
	public $displayField = 'email';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'status_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'email' => array(
			'email' => array(
				'rule' => array('email'),
				'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	/**
	 * makeTmppwd
	 * 仮パスワード発行：　
	 *
	 * @param  string	$email   		ユーザID
	 * @return int  	lastid / false				
	 */
	function makeTmppwd($email = null){
		try{
//debug('start ['.$email.']');
			$this->loadModel('User');
			$this->User->recursive = -1;
			$user = $this->User->findByEmail($email);
//debug($user);			
			if(!isset($user['User']['id'])){
//debug('登録されていません');
				return false;
			}
			$this->loadModel('Role');
			$role = $this->Role->chkRole($user['User']['group_id'],
					array(	'controller' => 'users',
							'action' => 'resetpwd',
							));
			if($role == false){
//debug('許可されていません');
				return false;
			}
		
			$this->recursive = -1;
			$find = $this->findByEmail($email);
			$data = array();
			if(empty($find)){
$this->log('new');
				$data = $this->create(array('email' => $email));
			} else {
$this->log('update');
				$data = $find;
			}
//debug($data);
			// とりあえず仮のパスワードを入れておき、メールを送信するタイミングで作り直す
			// あとでもう少しセキュアに設定
			$newpwd = VALUE_DefaultPWD;
			// time limit
			$expdate = $this->getexpday(VALUE_tmppwd_limit_default);
			$data[$this->name]['pwd'] = $newpwd;
			$data[$this->name]['expdate'] = null;
			$data[$this->name]['created'] = null;
			$sv = $this->save($data);
			if($sv){
				$lastid = $sv[$this->name]['id'];
//debug($lastid);
				$this->loadModel('UserExtension');
				$this->UserExtension->recursive = -1;
				
				$this->UserExtension->save(array(
									'id' => $user['User']['id'],
									'tmppassword_id' => $lastid,
										));
				return $lastid;
			}
			return false;
		} catch (Exception $e){
//debug(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}	

	/**
	 * setpwd
	 * 仮パスワード発行：　
	 *
	 * @param  int		$id   		ユーザID
	 * @param  string	$pwd   		ユーザID
	 * @return int  	lastid / false				
	 */
	function setpwd($id = null,$pwd = null){
		try{
			if($id == null) return false;
			if($pwd == null) return false;
			$md5 = md5($pwd);
			$expdate = $this->getexpday(VALUE_tmppwd_limit_default);
			$this->recursive = -1;
			if($this->findById($id)){
				$rc = parent::save(array('id' => $id,
										 'pwd' => $md5,
										 'expdate' => $expdate
									));
				return $rc;
			}
			return false;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 * delete
	 * 仮パスワードテーブル削除　
	 *
	 * @param  string	$email  	ログインID
	 * @return bool 
	 */
	function delete($email = null){
		try{
			$this->recursive = -1;
			$tmppwd = $this->findByEmail($email);
			if($tmppwd){
//debug($tmppwd);	
				$myId = $tmppwd[$this->name]['id'];
				if(parent::delete($myId)){
					$this->loadModel('UserExtension');
					return($this->UserExtension->clearTmppasswordId($myId));
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}


	/**
	 * chk
	 * 仮パスワードの一致状況確認（ログ出力のため）　
	 *
	 * @param  string	$email  	ログインID
	 * @return array['remark']    : ログの remark に記載する内容 
	 * @return array['event_data']: ログの evant_data に記載する内容 
	 */
	function chk($email = null, $pwd = null){
		$rtn = array();
		try{
			$this->recursive = -1;
			$tmppwd = $this->findByEmail($email);
//$this->log($tmppwd);	
			if($tmppwd){
				$md5_pwd = md5($pwd);
				if($tmppwd[$this->name]['pwd'] == $md5_pwd){
					$_now = $this->now();
					if($tmppwd[$this->name]['expdate'] < $_now){
						// 期限切れ
						$rtn['remark'] = '認証コード期限切れ';
						$rtn['event_data'] = '有効期限['.$tmppwd[$this->name]['expdate'].'] < ['.$_now.']';
						return $rtn;
					}
				} else {
					//　不一致
					$rtn['remark'] = '認証コード不一致';
					$rtn['event_data'] = '失敗ID['.$pwd.']['.$md5_pwd.'] != ['.$tmppwd[$this->name]['pwd'].']';
					return $rtn;
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		$rtn['remark'] = '不明';
		$rtn['event_data'] = 'ID['.$email.'] pwd['.$pwd.']';
		return $rtn;
	}



	
	function test(){
		return 'abc';
	}
	
}
