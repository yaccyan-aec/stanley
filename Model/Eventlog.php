<?php
App::uses('AppModel', 'Model');
//App::uses('Group', 'Model');
//App::uses('Datalog', 'Model');

/**
 * Eventlog Model
 *
 */
class Eventlog extends AppModel {

	/**
	 * Display field
	 *
	 * @var string
	 */
	public $name = 'Eventlog';
	
	//The Associations below have been created with all possible keys, those that are not needed can be removed
	/**
	* $order find時のデフォルトソート順
	*/
	var $order = array("Eventlog.created" => "desc");
	public $actsAs = array( 
			'Common',
			'Search.Searchable'
			);


	//The Associations below have been created with all possible keys, those that are not needed can be removed
/**
 * sanitizeItems
 * 		sanitize したい項目を定義すると、appModel で自動的にやってくれる。
 * @var array : フィールド名 => html (true = タグを削除 / false = タグをエスケープ)
 *             or array( 'html' => true ,       // true / false / 'info' = 一部タブ許容
 *                       'serialize' => false ,  // true / false
 *                       'encode' => 'base64'   // 'base64' / 'none' 
 *                     )
 *
 */
	var $sanitizeItems = array(
								'user_name' => array('html' => true, ),
								'user_division' => array('html' => true, ),
								'event_data' => array('html' => false ,'serialize' => true),
								);	
	

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
 		'Maillog' => array(
			'className' => 'Maillog',
			'foreignKey' => 'eventlog_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Datalog' => array(
			'className' => 'Datalog',
			'foreignKey' => 'eventlog_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

	/**
	* $myAgent Agent情報
	*/
	var $myAgent;
	/**
	* $groupAry グループ情報
	*/
	var $groupAry;

	function setAgent($agent = null){
		if(is_null($agent)) return;
		$this->myAgent = $agent;
	}

	function getGroupName($gid){
		if(empty($this->groupAry)){
			$this->loadModel('Group');
			$this->groupAry = $this->Group->find('list');
		}
		return($this->groupAry[$gid]);
	}

	/**
	* function _getRemote
	* リモートサーバを調べる
	* 時間がかかるため、セッションに保存し、ないときだけ
	* 改めて取得する
	*
	* @return array $remote 
	*/
	function _getRemote(){
		try{
			if(isset($_SERVER['HTTP_HOST'])){
		
				$remote = CakeSession::read('remote');
				if(empty($remote)) $remote = array();
				$readflg = false;
				if(isset($remote['addr'])){
					if($remote['addr'] != $this->myAgent['server']['REMOTE_ADDR']){
						$readflg = true;
					}
				} else {
					$readflg = true;
				}
				if($readflg) {
					$remote['addr']= $this->myAgent['server']['REMOTE_ADDR'];
					try{
						$remote['host']= gethostbyaddr($remote['addr']);
					} catch (Exception $e0){
						$remote['host']= $remote['addr'];
$this->log(__FILE__ .':'. __LINE__ .': '. $e0->__toString());
					}

					$remote = CakeSession::write('remote',$remote);
				}
				return $remote;
			} else {
				// shell 起動のときは決め打ち
				$remote = array( 	'addr' => '127.0.0.1',
									'host' => '127.0.0.1',
								);
				return $remote;				
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. $e->__toString());
			return array();
		}

	}

	/**
	* function insertLog
	* @todo writeLog から呼ばれる
	*
	* @param array $args 登録内容
	* @param array $me ユーザ情報
	* @param array $sess セッションID
	* 
	*/
// args の type,id は　ダウンロード時のタイプなどを指定する予定		
//		$_type = $args['type'];
//		$_id   = $args['id'];


	function insertLog($args, $me = null, $sess = null){
		$remote = $this->_getRemote();
		if(count($args) == 0){
			$this->log(__FILE__ .':'. __LINE__ .': args err ['.$sess.']',LOG_DEBUG);
			return;
		}
		// サーバを調べる
//$this->log($args,LOG_DEBUG);
		$new = $this->Newdata($remote,$args);

		$new = $this->setUser($new,$me,$sess);
		$new = $this->setEvent($new,$args);
		if(Hash::check($new,'Eventlog.event_action')){
			if($new['Eventlog']['event_action'] == 'ログアウト'){
				if(!Hash::check($new,'Eventlog.login_id')) {
					// ログインＩＤ　の記載のないログアウトは意味がないので書かない
					return;
				}
			}
		}
		$new[$this->name]['session_id'] = $sess;
		$new[$this->name]['version'] = VERSION;
		if(isset($_SERVER['HTTP_HOST'])){
		} else {
			// shell 起動のとき
			$new[$this->name]['useragent'] = 'SHELL';
		}
				// domain 追加　2014.11.27
		if(!empty($new[$this->name]['login_id']) &&
			empty($new[$this->name]['domain'])){
				$domain = preg_replace('/^(.+)@/','',$new[$this->name]['login_id']);
				$new[$this->name]['domain'] = $domain;
		}
		if($this->saveAll($new)){
			return $this->getLastInsertID();
		}
		return false;
	}
		
	/**
	* function Newdata
	* @todo リモート情報設定
	*
	* @param array $remote リモートデータ
	* @return array $newdata
	*/
	function Newdata($remote = array(), $args = array()){
		$new = $this->create();
		$new[$this->name]['created'] = null;
		$new[$this->name]['referer'] = @$this->myAgent['server']['HTTP_REFERER'];
		$new[$this->name]['useragent'] = $this->myAgent['server']['HTTP_USER_AGENT'];
		$new[$this->name]['remoteaddr'] = $remote['addr'];

		$new[$this->name]['remotehost'] = $remote['host'];
		$new[$this->name]['uri'] = $this->myAgent['server']['REQUEST_URI'];
		$new[$this->name]['lang'] = $this->getLang();		// 言語モード
//		$new[$this->name]['lang'] = Configure::read('Config.language');		// 言語モード
		
		// マージはこちらで
		foreach($args as $key => $val){
			$new[$this->name][$key] = $val;
		}
//$this->log('================== syslog',LOG_DEBUG);		
//$this->log($new,LOG_DEBUG);		
		return $new;
	}
	
	/**
	* function setUser
	* @todo user 情報設定
	*
	* @param array $remote リモートデータ
	* @return array $newdata
	*/
	function setUser($data,$user,$sess = null){
		try{
			if(is_null($data)) return $data;
			$_user = null;
			if(is_null($user)){
				$_user = CakeSession::read('auth');
				if(empty($_user)){
$this->log('=========== ここ！ ',LOG_DEBUG);					
//$this->log($data,LOG_DEBUG);
					// ここに来るときは、おそらくログイン前に失敗しているので
					// パラメータから、対象ユーザ情報を取り出す
					if(Hash::check($data,$this->name.'.target_user_id')){
						$this->loadModel('User');
						$_find = $this->User->find('first',array(
							'conditions' => array('id' => $data[$this->name]['target_user_id']),
							'recursive' => -1));
						if(!empty($_find)){
							$_user = $_find['User'];
						}
					}
//$this->log($_user,LOG_DEBUG);					
				}
			}else {
				if(Hash::check($user,'User')){
					$_user = $user['User'];
				} else {
					$_user = $user;
				}
			}
			if(!empty($_user)){
				$data[$this->name]['login_id'] = (isset($data[$this->name]['login_id'])) ? 
					$data[$this->name]['login_id'] : $_user['email'];

				// domain 追加　2014.11.27
//				$domain = preg_replace('/^(.+)@/','',$data[$this->name]['login_id']);
//$this->log('Domain['.$domain.']');	
//				$data[$this->name]['domain'] = $domain;
	
				$data[$this->name]['user_id'] = (isset($data[$this->name]['user_id'])) ? 
					$data[$this->name]['user_id'] : $_user['id'];

				$data[$this->name]['group_id'] = (isset($data[$this->name]['group_id'])) ? 
					$data[$this->name]['group_id'] : $_user['group_id'];

				$data[$this->name]['contract_id'] = (isset($data[$this->name]['contract_id'])) ? 
					$data[$this->name]['contract_id'] : $_user['contract_id'];

				$data[$this->name]['user_name'] = (isset($data[$this->name]['user_name'])) ? 
					$data[$this->name]['user_name'] : $_user['name'];

				$data[$this->name]['user_division'] = (isset($data[$this->name]['user_division'])) ? 
					$data[$this->name]['user_division'] : $_user['division'];

				$data[$this->name]['group_name'] = (isset($data[$this->name]['group_name'])) ? 
					$data[$this->name]['group_name'] : $this->getGroupName($data[$this->name]['group_id']);
			}
			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $data;
	}

	/**
	* function setEvent
	* @todo event 情報設定
	*
	* @param array $remote リモートデータ
	* @return array $newdata
	*/
	function setEvent($data,$args){
		if(is_null($data) || is_null($args)) return $data;
		// merge data
//		foreach($args as $key => $val){
//			$data[$this->name][$key] = $val;
//		}
//$this->log($data);
		if(isset($args['type'])){
			switch($args['type']){
				case 'Content':		// 	送信
//					$data[$this->name]['content_id'] = $args['content_id'];
					$this->loadModel('Datalog');
					$data = $this->Datalog->setSend($data,$args['content_id']);
					break;
				
				case 'Approval':		// 	承認依頼
				case 'ResetPwd':		// 	仮パスワード発行
				case 'MkPass':			//	パスワード発行
				case 'Download':		//	ダウンロード
				case 'Agreement':		//	機密保持制約
				case 'UploadOver':		//	アップロード契約量超過
				case 'AbortPscan':		//	PSCAN　強制キャンセル
				case 'TestMail':        //　　メールサーバテストメール送信
					break;

				case 'Contract':	// 契約
					$event_data = $this->mkContractEvent($data[$this->name]['event_data']);
					$data[$this->name]['event_data'] = $event_data;
					break;

				case 'Lockout':	// ロックアウト
					break;
				default:
					break;
			}
		}
//$this->log($data);		
		return $data;
	}

	/**
	* function mkContractEvent
	* @todo event Contract のイベント情報を編集
	*
	* @param mix $event_data 項目
	* @return string 編集内容
	*/
	function mkContractEvent($event_data = ''){
		try{
			$return_str = '';
			if(isset($event_data['Contract'])){
				$return_str .= $event_data['Contract']['id'];
				$return_str .= ':'. $event_data['Contract']['name'];
				if(isset($event_data['Contract']['is_trial'])){
					if($event_data['Contract']['is_trial'] == VALUE_Contracts_Type_Trial){
						$return_str .= '（お試し）';
					} else {
						$return_str .= '（正規）';
					}
				} else {
					$return_str .= '（お試し）';
				}
			}
			return ($event_data);
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $event_data;
	}
	
}
