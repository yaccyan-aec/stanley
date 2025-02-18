<?php
App::uses('AppModel', 'Model');
/**
 * Status Model
 *
 * @property User $User
 * @property Content $Content
 * @property Addressbook $Addressbook
 */
class Status extends AppModel {

	public $name = 'Status';
//	var $order = array(	"Content.created" => "desc");
/**
 * Use behavior
 *
 * 削除フラグ（tinyint) => 削除日(datetime) のフィールド名カスタマイズ
 * デフォルトは 'deleted' => 'deleted_date'
 */
	var $actsAs = array('SoftDelete' => array('is_deleted' => 'deleted'), 
						'Search.Searchable');
// サーチ系はのちほど
//	public $actAs = array('Search.Searchable');
//	var $order = array(	"Status.created" => "desc");


	/**
	 * Searchプラグイン設定
	 */
	var $filterArgs = array(
		'keyword' => array('type' => 'query', 'method' => 'searchKeyword'),
		'from_created' => array('type' => 'query', 'method' => 'fromCreatedSearch', 'field' => 'Status.created'),
		'to_created' => array('type' => 'query', 'method' => 'toCreatedSearch', 'field' => 'Status.created'),
	);

	public function searchKeyword($data = array()) {
		$conditions = array();
		//	検索対象となるフィールドを設定（数字やフラグ、シリアライズ項目を除く、varchar や text のすべての項目が対象）
		$searchFields = array('Content.title' );

		//	サニタイズが必要なフィールドには、サニタイズしたキーワードをセット
		$conditions['OR'] = $this->mkCondition($searchFields,$data['keyword']);									
	
		// 送信アドレス検索 とりあえず　email だけで検索するが、
		// name や div なども対象にしたいというときはここの条件を追加すればよさそう
		$user_searchFields = array('User.email', 'User.name','UserExtension.name_jpn','UserExtension.name_eng' );
		$user_cond = $this->User->mkCondition($user_searchFields,$data['keyword']);
		$users = $this->User->find('all',
			array('conditions' => array('OR' => $user_cond),
					'fields' => array('User.id','User.name','User.email','UserExtension.name_jpn','UserExtension.name_eng'),
					'recursive' => 0));

		$u_ids = Hash::combine($users,'{n}.User.id');
		if(count($u_ids) > 0){
			$conditions['OR']['Content.user_id'] = array_keys($u_ids);
		}
		
		// ファイル名検索
		$files = $this->Content->Uploadfile->find('all', array('conditions' => array('name LIKE' => '%'. $data['keyword']. '%'), 'fields' => array('content_id'), 'recursive' => -1));
		foreach($files as $file) {
			$conditions['OR']['Content.id'][] = (int)$file['Uploadfile']['content_id'];
		}
		return $conditions;
	}


/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'content_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'email' => array(
			'email' => array(
				'rule' => array('email'),
				'message' => 'The format is not goodness.',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Content' => array(
			'className' => 'Content',
			'foreignKey' => 'content_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Addressbook' => array(
			'className' => 'Addressbooks.Addressbook',
			'foreignKey' => 'addressbook_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * makeCcAdresses
 * @todo 	送信用にデータを整える
 * @param array $data : リクエストデータ
 * @var array
 */
	function makeCcAdresses($data = null){
		try{
//debug($data);
		if($data == null) return false;
			$emails = array();
			$this->loadModel('Addressbooks.Addressbook',true);
			if(Hash::check($data,'Content.cc') && is_array($data['Content']['cc'])){
				$this->Addressbook->recursive = -1;
				
				foreach($data['Content']['cc'] as $k => $v){
					$add = $v;
					if(is_numeric($v)){
						$add = $this->Addressbook->findById($v);
						$address = strtolower($add['Addressbook']['email']);
						$emails[$address] = $address;
					} else if(is_array($v)){
					} else {
						// たぶん　free address
						$adds = preg_split("/[\s,]+/", $v); // , とスペース
						foreach($adds as $key => $val){
							$tval = trim($val);
							if(Validation::email($tval)){
								// 形式チェック OK
								$address = strtolower($tval);
								$emails[$address] = $address;
							}
						}
					}
				}
			}

			$data['Content']['cc'] = $emails;

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
//	$this->log($data);
			return $data;

	}

/**
 * makeToAdresses
 * @todo 	送信用にデータを整える
 * @param array $data : リクエストデータ
 * @var array
 */
	function makeToAdresses($data = null){
		try{
			if($data == null) return false;
			$new_st = array();
			$emails = array();
			$this->loadModel('Addressbooks.Addressbook',true);
			if(Hash::check($data,'Content.add_list') && is_array($data['Content']['add_list'])){
				$this->Addressbook->recursive = -1;
				foreach($data['Content']['add_list'] as $k => $v){
					$add = $v;
					if(is_numeric($v)){
						$add = $this->Addressbook->findById($v);
					} else if(is_array($v)){
					} else {

						// たぶん　free address
						continue;
					}
					if(!empty($add) && is_numeric($add['Addressbook']['id'])){
						// みつかった
						$add = $this->Addressbook->_setUserID($add);
						// 形式チェック OK
						if(in_array(strtolower($add['Addressbook']['email']),$emails)){
							// アドレスがだぶったらスキップ
							continue;
						}

						$new = $this->create();
						// デフォルトは非表示
						$new[$this->name]['status_code'] = VALUE_StatusStatusCode_Off;
						$new[$this->name]['addressbook_id'] = $v;
						$new[$this->name]['email'] = $add['Addressbook']['email'];
						if(empty($add['Addressbook'][$this->makeFieldName('name','name_')])){
							$new[$this->name]['name'] = $add['Addressbook']['name'];
						} else {
							$new[$this->name]['name'] = $add['Addressbook'][$this->makeFieldName('name','name_')];
						}							
						
						$emails[] = strtolower($new[$this->name]['email']);
						$new_st[] = $new;
					} else {
//$this->log('####################### 3 Addressbook　Not　Fount',LOG_DEBUG);
					}
				}
			}

			if(isset($data['Content']['free_add'])){
				$this->loadModel('User');
				$sender = $this->User->findById($data['Content']['user_id']);
				// フリー入力
				if(is_array($data['Content']['free_add'])){
					foreach($data['Content']['free_add'] as $k => $v){
						$adds = preg_split("/[\s,]+/", $v);	// , とスペース
						foreach($adds as $key => $val){
							$tval = trim($val);
							if(Validation::email($tval)){
								// 形式チェック OK
								if(in_array(strtolower($tval),$emails)){
									// アドレスがだぶったらスキップ
									continue;
								}
								// 代替アドレス帳があるか検索
								$adlist = $this->Addressbook->findEmailToAddressBookID($tval,$sender);
								$new = $this->create();
								$new[$this->name]['status_code'] = VALUE_StatusStatusCode_Off;
								$new[$this->name]['email'] = $tval;
								if(count($adlist) > 0){
									// 見つかったら最初にものを採用
									$add = $adlist[0];
									$new[$this->name]['addressbook_id'] = $add['Addressbook']['id'];
									if(empty($add['Addressbook'][$this->makeFieldName('name','name_')])){
										$new[$this->name]['name'] = $add['Addressbook']['name'];
									} else {
										$new[$this->name]['name'] = $add['Addressbook'][$this->makeFieldName('name','name_')];
									}							
									
								} else {
									// ユーザ登録がないか探す
									$target_user = $this->User->findByEmail($tval);
									if(isset($target_user['User']['id'])){
										// ユーザがあったらそちらの名前を採用
										$rtn = $this->User->getFromNames($target_user['User']['id']);
										$new[$this->name]['name'] = $rtn['name'];
									} else {	
										//　どこにもなかったらメールアドレスを名前とする
										$new[$this->name]['name'] = $tval;
									}
								}
								$emails[] = strtolower($tval);
								$new_st[] = $new;
							} 
						}
					}
				} else {
					// ゲストのときはひとつだけ
					$free_add = trim($data['Content']['free_add']);
					if(Validation::email($free_add)){
						$new = $this->create();
						// デフォルトは非表示
						$new[$this->name]['status_code'] = VALUE_StatusStatusCode_Off;
						$new[$this->name]['email'] = $free_add;
						$new[$this->name]['name'] = $free_add;
						$emails[] = strtolower($free_add);
						$new_st[] = $new;
					}
				}
			}
			$data['Content'][$this->name] = $new_st;

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
			return $data;

	}

/**
 * getToLang
 * @todo 	あて先の言語を決める
 * @param   int $id : リクエストデータ
 * @return  string $lang : 'jpn' | 'eng'
 */
	function getToLang($sid = null){
		$this->recursive = 0;
		$status = $this->findById($sid);
		$_now = $this->getLang();
		// 見つからなかったら現在の言語を返す
		if(empty($status)) return $_now;
		/**
		* user > addressbook > Config の順で尊重
		*
		*/
		if(isset($status['User'])){
			$_lang = trim($status['User']['lang']);
			if(strlen($_lang) == 3){
				return $_lang;	// User に言語指定があった
			}
		}
		
		if(isset($status['Addressbook'])){
			$_lang = trim($status['Addressbook']['lang']);
			if(strlen($_lang) == 3){
				return $_lang;	// Addressbook に言語指定があった
			}
		}
		return $_now;	// 現在の言語
	}

	/**
	 * getToNamesFromUser
	 * @todo 	言語に対応するユーザ名を User　テーブルから求める
	 * @param   array  $sid
	 * @param   string $lang : null = 現在の言語
	 * @return  array $rtn : 言語に対応した名前
	 */
	function getToNamesFromUser($sid,$lang = null){
		$rtn = array();
		try{
			$_lang = ($lang == null) ? $this->getLang() : $lang;
			$this->recursive = 0;
			$data = $this->findById($sid);
			if(empty($data)){
				return($rtn);
			}
			// Addressbook　に関係なく　User　からget
			$this->loadModel('User');
			$rtn = $this->User->getFromNames($data['User']['id'],$_lang);
			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return($rtn);
	}
	/**
	 * getToNames こちらは当面使わないかも
	 * @todo 	言語に対応するユーザ名を求める
	 * @param   array  $sid
	 * @param   string $lang : null = 現在の言語
	 * @return  array $rtn : 言語に対応した名前
	 */
	function getToNames($sid,$lang = null){
		$rtn = array();
		try{
			$_lang = ($lang == null) ? $this->getLang() : $lang;
			$this->recursive = 0;
			$data = $this->findById($sid);
			if(empty($data)){
				return($rtn);
			}

			$_getflg = false;
			$auth = CakeSession::read('auth');
			$this->loadModel('Addressbooks.Addressbook',true);
			// アドレス帳指定があったとき
			$ab_data = array();

			if($data['Status']['addressbook_id'] == null){
				// アドレス帳指定がないときは、自分が見られるアドレス帳の中から探す
				// 個人　＞　共通　
				$adlist = $this->Addressbook->findEmailToAddressBookID($data['Status']['email'],$auth);
				if(count($adlist) > 0){
					//　複数あったら最初に見つかったもの
					$ab_data = $adlist[0];
				}
				
			} else {
				$ab_data = $this->Addressbook->findById($data['Status']['addressbook_id']);
			}
			// Addressbook から get
			$this->loadModel('Addressbook');
			$division = $this->Addressbook->_getDivision($ab_data,$_lang);
			if(strlen(trim($division)) > 0){
				$rtn['division'] = $division;
			}

			$name = $this->Addressbook->_getName($ab_data,$_lang);
			if(strlen(trim($name)) > 0){
				$_getflg = true;
				$rtn['name'] = $name;
			}

			if(!$_getflg){
				// Addressbook　から取れなかったとき　User　からget
				$this->loadModel('User');
				$rtn = $this->User->getFromNames($data['User']['id'],$_lang);
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return($rtn);
	}

	/**
	 * setStatus
	 * @todo 	status_code を設定する
	 * @param   int  $id
	 * @param   int  $code : status code
	 * @return  bool $rtn : 
	 */
	function setStatus($id = null, $code = 0){
		try{
			if($id == null) return false;
			$ary = array();
			if(is_array($id)){
				$ary = $id;
			}else {
				$ary[] = $id;
			}
			$this->recursive = -1;
			foreach($ary as $sid){
				parent::save(array(
							'id' => $sid,
							'status_code' => $code
							));
			}
			return true;			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 * setViewStatus
	 * @todo 	status_code を設定する
	 * @param   int  $id
	 * @param   int  $code : status code
	 * @return  bool $rtn : 
	 */
	function setViewStatus($id = null){
		try{
			if($id == null) return false;
			$this->recursive = 0;
			$data = $this->findById($id);
			if($data['Content']['uploadfile_count'] > 0) return false;
			if(is_null($data[$this->name]['downdate'])){
		
				$this->recursive = -1;
				parent::save(array(
							'id' => $id ,
							'downdate' => $this->now(),
							));
				$this->loadModel('Content');
				$this->Content->addressCountUp($data['Content']['id']);		

				$this->writeLog(
					array(
						'event_action' => '受信',
						'remark' => '閲覧',
						'result' => '成功',
						'login_id' => $data['User']['email'],
						'content_id' => $data['Status']['content_id'],
						'status_id' => $data['Status']['id'],
					));

				return true;			
			}			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}
	 
	/**
	 * Show
	 * @todo 	ステータスを「表示」にする
	 * @param   int  $id
	 * @return  bool $rtn : 
	 */
	function Show($id = null){
		return $this->setStatus($id,VALUE_StatusStatusCode_On);
	}
	/**
	 * Hide
	 * @todo 	ステータスを「非表示」にする
	 * @param   int  $id
	 * @return  bool $rtn : 
	 */
	function Hide($id = null){
		return $this->setStatus($id,VALUE_StatusStatusCode_Off);
	}
	/**
	 * getStatusFromContentId
	 * @todo 	status_code を設定する
	 * @param   int  $id
	 * @param   int  $code : status code
	 * @return  bool $rtn : 
	 */
	function getStatusFromContentId($cid = null){
		try{
			if($cid == null) return array();
			$this->recursive = -1;
			$this->order = '';
			$list = $this->find('list',array(
				'fields' => array('id', 'email'),
				'conditions' => array('content_id' => $cid)));
			return $list;			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}

	
	/**
	 * setDownDate
	 * @todo 	status_code を設定する
	 * @param   int  $id
	 * @param   int  $code : null = 現在の言語
	 * @return  array $rtn : 言語に対応した名前
	 */
	function setDownDate($uid = null, $cid = 0){
		try{
			if($uid == null) return false;
			if($cid == 0) return false;
			
			//ログイン中ユーザと一致していなければ終了
			$auth = CakeSession::read('auth');
			if($uid != $auth['id']) return false;
			
			$this->recursive = -1;
			$cond = array('user_id' => $uid,
						  'content_id' => $cid);
			$data = $this->find('first',array('conditions' => $cond));
			// 承認者がダウンロードしたときは日付を入れる必要はない
			if(Hash::check($data,'Status.id')){
				// 最終ダウンロード日時を入れるなら↓の判定をなくす
				if($data[$this->name]['downdate'] == null){
	//debug('日付をいれます');			
					parent::save(array(
								'id' => $data[$this->name]['id'] ,
								'downdate' => $this->now(),
								));
					// 初めてのダウンロードのとき受信者のカウントアップ			
					$this->loadModel('Content');
					$this->Content->addressCountUp($cid);		
				}
			}
//			return $data[$this->name]['id'] ;			
			return $data ;			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. $e->getMessage());
			return false;
		}
	}

	/**
	 * setCond
	 * 有効なものだけにするか、全部見るかパラメータで切り替え
	 *
	 * @param  mixed  $params   	request->params
	 * @param  array  $cond   		もともとの条件
	 * @return array				検索条件   
	 */
	public function setCond($params,$cond = array()){
		try{
			$today = $this->today();
			if(isset($params['named']['exp'])){
				if($params['named']['exp'] == 'all'){
				} else {
					// 期限内のもの
					$cond['Content.is_expdate'] = 'N';
				}
			}
			// 送信者が消したもの以外
//			$cond['Content.is_deleted'] = false;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $cond;
	}

	/**
	 * makeAddListFromStatuses
	 * 再送のときなど、過去のステータスリストからリストを構築
	 *
	 * @param  array  $params   	request->params
	 * @param  array  $cond   		もともとの条件
	 * @return array				検索条件   
	 */
	public function makeAddListFromStatuses($statuses = array()){
		try{
			$add_list = array();
			$free_add = array();
			$this->recursive = 0;
			$this->loadModel('Addressbook');
			$this->Addressbook->recursive = -1;
			foreach($statuses as $status){
			// 当該IDがまだあるかどうか
				$now = $this->findById($status['id']);
				if($status['email'] == $now['Addressbook']['email']){
					// id と　メールアドレスが合っていればOK
//					$add_list[] = $status['addressbook_id'];
					$add_list[] = $now;
				} else {
					// ほかのIDで登録されているか調べる
					$addr = $this->Addressbook->find('first',
						array( 'conditions' => 
							array( 	'email' => $status['email'],
									'user_id' => $status['user_id'],
						)));
					if(Hash::check($addr,'Addressbook.id')){
						// いま登録されているIDを採用
//						$add_list[] = $addr['Addressbook']['id'];
						$add_list[] = $addr;
					} else {
						// ないときは　free_add 扱い
						$free_add[] = $status['email'];
					}					
				}
			}

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return compact('add_list','free_add');
	}

	public function findForView($id = null){
		try{
			// 正当性チェック
			if(!$this->is_valid($id)){
				return array();
			}
			
			$cond = array('Status.id' => $id);
			
			$contain = array(
			
					'Content' => array(
						'User' => array(
							'Group' => array(
								'fields' => array('id','name','jpn','eng')
							),
							'UserExtension' => array(
							)
						),
						'Status' => array(
							'User' => array(
								'fields' => array('id','name','division','email')
							)
						),
						'Uploadfile' => array(
						),
							
					),
				);
			$data = $this->find('first',array(
							'conditions' => $cond,
							'recursive' => 2,
							'contain' => $contain,
							));			
			if(empty($data)) {
				// ここで見つからないときは権限がないので戻る
				return array();
			}
			
			$this->loadModel('Uploadfile');
			foreach ($data['Content']['Uploadfile'] as $k => $v){
				if(!$v['is_deleted']){
					if($this->Uploadfile->isFileExists($v['path'])){
$this->log('ありました ['.$v['path'].']');
					} else {
$this->log('なかった['.$v['path'].']');
						$data['Content']['Uploadfile'][$k]['is_deleted'] = true;
					}
				}
			}
			return $data;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}
/**
 * is_valid
 * 当該データにアクセス権限があるかチェック
 */
	public function is_valid($id = null){
		try{

			// データの整合性チェックのため
			$auth = CakeSession::read('auth');			
			$_my_id = $auth['id'];
			$this->loadModel('Role');
			$_is_super = $this->Role->isSuper($auth['group_id']);

			// データの整合性チェックを追加　（スーパーは除外）

			// 基本は送信者のみ
			// データの整合性チェックを追加　（スーパーは除外）
			$cond = array($this->name.'.id' => $id, 'Status.user_id' => $_my_id);
			
			if($_is_super){
				// スーパーならIDだけで良い
				$cond = array($this->name.'.id' => $id);
			}
//$this->log($cond);
			$data = $this->find('first', array(
				'conditions' => $cond ,
				'recursive' => -1
			));
//$this->log($data);
			if(empty($data)){
				return false;
			}
			return true;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}
	
/** 
 * countMidoku
 * @todo	当該ユーザに関連する情報を求める
 * @param	int $uid : ユーザID
 * @var array 	:	'new' => 未読・期限内
 *					'old'   => 未読・期限切れ
 */		
	public function countMidoku($uid = null){
		$data = array('new' => 0, 'old' =>0);
		try{
			$this->loadModel('User');
			if($this->User->exists($uid)){
				$this->recursive = 0;
				// 新着（未読＆期限切れでない）
				$cond = array(	'Status.user_id' => $uid ,
								'Status.downdate' => NULL, 
								'Status.status_code' => VALUE_StatusStatusCode_On, 
								'Content.status_code' => VALUE_Status_Done,
								'Content.is_deleted' => false,
								'Content.is_expdate' => 'N');
				$new = $this->find('count',array('conditions' => $cond));
				// 未読＆期限切れ
				$cond['Content.is_expdate'] = 'Y';
				$old = $this->find('count',array('conditions' => $cond));
				$data['new'] = $new;
				$data['old'] = $old;
			}
			// エラーがあるかどうかを設定する（未）
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $data;
	}


	/**
	 * findUserListByKeyword
	 * @todo 検索条件パラメータkeywordによる検索条件を作成
	 * サニタイズ対象項目は、サニタイズしたkeywordで検索を行う
	 * @param    $data : str または array   
	 * @return   $conditions :array()
	 */
	public function findContentIdsByKeyword($data = array()) {
		$list = array();
		try{
			$cond = array();
			// アソシエーションが深いので、まず宛先ユーザの検索になりうるものを探す
			$this->loadModel('User');
			$searchFields = array('User.name', 'UserExtension.name_jpn','UserExtension.name_eng' );
			$user_cond = array();
			$user_cond['OR'] = $this->User->mkCondition($searchFields,$data['keyword']);
			$ulist = $this->User->find('list',array(
					'conditions' => $user_cond,
					'fields' => array('User.id','User.email'),
					'recursive' => 0,
				));
			
			$searchFields = array('Content.title', 'Status.name','Status.email' );

			//	サニタイズが必要なフィールドには、サニタイズしたキーワードをセット
			$cond['OR'] = $this->mkCondition($searchFields,$data['keyword']);									
			// 宛先としてヒットするものを追加
			$cond['OR'] += array('Status.user_id' => array_keys($ulist));
			$list = $this->find('list',array(
			'conditions' => $cond,
			'fields' => array('id','content_id'),
			'recursive' => false));
		
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $list;
	}	

}
