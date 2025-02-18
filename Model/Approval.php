<?php
App::uses('AppModel', 'Model');
/**
 * Approval Model
 *
 * @property Content $Content
 * @property Contract $Contract
 * @property AprvUser $User
 * @property Queue $Queue
 */
class Approval extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'sno';

	public $order = array("Approval.modified" => "desc");

	var $actsAs = array(
		'SoftDelete' => array('is_deleted' => 'deleted'	),
		'Containable',
		'Search.Searchable');

/**
 * Searchプラグイン設定
 */
	var $filterArgs = array(
		'keyword' => array('type' => 'query', 'method' => 'searchKeyword'),
		'from_created' => array('type' => 'query', 'method' => 'fromCreatedSearch', 'field' => 'Approval.created'),
		'to_created' => array('type' => 'query', 'method' => 'toCreatedSearch', 'field' => 'Approval.created'),
		'search' => array('type' => 'query', 'method' => 'isAllSearch'),
	);

	/**
	 * isAllSearch
	 * @todo 検索条件パラメータkeywordによる検索条件を作成
	 * サニタイズ対象項目は、サニタイズしたkeywordで検索を行う
	 * @param    $data : str または array
	 * @return   $conditions :array()
	 */
	 public function isAllSearch($data = array()){
		$cond = array();
//debug('isAllSearch start!');
//debug($data);
		try{
	        $auth = CakeSession::read('auth');												//	ログインユーザ情報を取得
//debug($data);
			$view = 'index';
			if(Hash::check($data,'view')){
				$view = Hash::get($data,'view');
			}
			$type = 'm';
			if(Hash::check($data,'type')){
				$type = Hash::get($data,'type');
			} elseif ($view == 'index_all' || $view == 'historylist_all'){
				$type = 'a';
			}
//debug($type);

			$aprvMembers = array();
			if(CakePlugin::loaded('Sections')){
				$this->loadModel('Sections.Section');
				$mySecLst = $this->Section->getLowLevel($auth['id']);
				// リーダーになれる権限の人
				$gids = $this->Role->getGroupList(array(
					'controller' => 'sections',
					'action' => 'can_leader'));

				$aprvMembers = @$this->Section->getMyAprvUserId($mySecLst,$gids);
				$aprvMembers[] = $auth['id'];	// 自分が抜けてるかもしれないので入れておく

			}
//debug($aprvMembers);
			switch($view){
				case 'index':
						$cond += array('Approval.aprv_stat' => VALUE_AprvStat_None); // 承認結果が未
						if($type == 'm') {
							$cond += array(	'Approval.aprv_req_user_id' => $auth['id']); // 契約IDが操作ユーザと同じ
						} else {
							if(!empty($aprvMembers)){
								$cond += array(	'Approval.aprv_req_user_id' => $aprvMembers	);	// 部門がユーザと同じ
							} else {
								$cond += array(	'Approval.contract_id' => $auth['contract_id']);	// 契約IDが操作ユーザと同じ
							}
						}
					break;
				case 'index_all':
						$cond += array('Approval.aprv_stat' => VALUE_AprvStat_None); // 承認結果が未
						if($type == 'm'){
							$cond += array(	'Approval.aprv_req_user_id' => $auth['id']); // 契約IDが操作ユーザと同じ
						}
					break;
				case 'historylist':
						// 契約管理者（admin）
						// ユーザ管理ができる人をアドミンとみなす
						$this->loadModel('Role');
						$is_admin = $this->Role->chkRole($auth['group_id'],
								array(	'controller' => 'navi',
										'action' => 'user_manage'));

						$cond += array('Approval.aprv_stat <>' => VALUE_AprvStat_None); // 承認結果が未
						if($type == 'm'){
							// 自分の担当だけ
							$cond += array(	'Approval.aprv_user_id' => $auth['id']); // 契約IDが操作ユーザと同じ
						} else {
							// 全体を見る
							if($is_admin){
								// 管理者は契約内の全部を見る
								$cond += array(	'Approval.contract_id' => $auth['contract_id']); // 契約IDが操作ユーザと同じ
							} else {
								// 部門長は、自分の部門　＋　依頼されたもの
								// index と同じに
								if(!empty($aprvMembers)){
									$cond['or'] = array('Approval.aprv_user_id' => $aprvMembers);
								} else {
									$cond['or'] = array(	'Approval.contract_id' => $auth['contract_id']);	// 契約IDが操作ユーザと同じ
								}
								$cond['or'] += array('Approval.aprv_req_user_id' => $auth['id']);
							}
						}
					break;
				case 'historylist_all':
						$cond += array('Approval.aprv_stat <>' => VALUE_AprvStat_None); // 承認結果が未
						if($type == 'm'){
							$cond += array(	'Approval.aprv_user_id' => $auth['id']); // 契約IDが操作ユーザと同じ
						}
					break;
				default:
					break;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $cond;
	 }

	/**
	 * searchKeyword
	 * @todo 検索条件パラメータkeywordによる検索条件を作成
	 * サニタイズ対象項目は、サニタイズしたkeywordで検索を行う
	 * @param    $data : str または array
	 * @return   $conditions :array()
	 */
	public function searchKeyword($data = array()) {
		$conditions = array();
//debug($data);
		try{
			$keyword = null;
			if(is_array($data)){
				if (!isset($data['keyword']) || empty($data['keyword'])) {
					return $conditions;
				}
				$keyword = @Hash::get($data,'keyword');
			} else {
				$keyword = $data;
			}
			if(strlen(trim($keyword)) > 0){
				//　フリーワード検索項目（増減があるときはここで調整）
				// 承認情報 とりあえず見れそうなのはここまで
				// Content情報や送受信者情報の深いところは、前もってID群にして追加する
				$searchFields = array(	'Approval.sno',				// シリアル番号
//										'AprvReqUser.email',		// 決裁依頼者
//										'AprvReqUser.name',			// 依頼メッセージ
//										'AprvUser.email',			// 決裁者
//										'AprvUser.name',			// 依頼メッセージ
										'Approval.message',			// 依頼メッセージ
										'Approval.request_comment',	// 決裁者コメント
								);
				$conditions['OR'] = $this->mkCondition($searchFields,$keyword);
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $conditions;
	}

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
								'message' => array('html' => false, 'serialize' => true),
								'request_comment' => array('html' => false, 'serialize' => true),
								);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
	);
	var $validationSets = array(
		'admin' => array(
		),
		'register' => array(
			'sno' => array('rule' => 'notBlank',
							'message' => 'The blank is not goodness.'),
			'content_id' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'last' => false,
					'message' => 'The blank is not goodness.',
				),
				'numeric' => array(
					'rule' => array('numeric'),
					'last' => false,
					'message' => 'This item should be a number.',
				),
			),
			'aprv_req_user_id' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'last' => false,
					'message' => 'The blank is not goodness.',
				),
				'numeric' => array(
					'rule' => array('numeric'),
					'last' => false,
					'message' => 'This item should be a number.',
				),
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
		'Content' => array(
			'className' => 'Content',
			'foreignKey' => 'content_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Contract' => array(
			'className' => 'Contract',
			'foreignKey' => 'contract_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'AprvReqUser' => array(
			'className' => 'User',
			'foreignKey' => 'aprv_req_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'AprvUser' => array(
			'className' => 'User',
			'foreignKey' => 'aprv_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)

	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Queue' => array(
			'className' => 'Queue',
			'foreignKey' => 'approval_id',
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
	 * save後処理
	 * @param  boolean $created 新規作成の場合,true
	 */
	public function afterSave($created = true) {
		if (CakePlugin::loaded('Nifco') && $created == true) {
			// Nifcoプラグインが取り込まれてる場合
			if (!MAIL_APPROVAL_FLG) return;
			$MailApproval = ClassRegistry::init('Nifco.MailApproval');
			// メール認証トークン生成
			$this->id = $this->data['Approval']['id'];
			$this->saveField('token', $MailApproval->createToken(), array('validate' => false, 'callbacks' => false));
		}

		return;
	}


	/**
	 * setInitData
	 * @todo 	送信情報から初期データを構築
	 * @param   array  $data 送信データ
	 * @return  array $rtn : 構築したデータを追加
	 */
	public function setInitData($data = array()){
		$rtnary = $data;
		try{
//$this->log('setInitData start!',LOG_DEBUG);
//$this->log($data,LOG_DEBUG);
			if(Hash::check($data,'Content.approval_add')){
				$uid = Hash::get($data,'Content.user_id');
				$this->loadModel('User');
				if($this->User->exists($uid)){
					$new = $this->create(array(
							'contract_id' => Hash::get($data,'Content.aprv_contract_id'),
							'aprv_req_user_id' => Hash::get($data,'Content.approval_add'),
							'request_comment' => Hash::get($data,'Content.request_comment'),
							'expdate' =>  date('Y-m-d', strtotime('+'.Hash::get($data,'Content.approval_limit').' day')),
							'aprv_stat' => VALUE_AprvStat_None
							));
//$this->log($new,LOG_DEBUG);
					$rtnary += $new;
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $rtnary;
	}

	/**
	 * makeSno
	 * @todo 	シリアル番号を生成　'C' + contract_id + 'W' + send user_id + 'B' + request user_id
	 * @param   array  $data 送信データ
	 * @return  array $rtn : 構築したデータを追加
	 */
	public function makeSno($data = array(),$content_id = null){
		$sno = 'C';
		try{
//$this->log('==== makeSno',LOG_DEBUG);

//$this->log($data,LOG_DEBUG);
			$user_id = Hash::get($data,'Reserve.rsv_data.Content.user_id');
			$boss_id = Hash::get($data,'Reserve.rsv_data.Approval.aprv_req_user_id');
			$sno = sprintf("C%dW%dB%d",$content_id,$user_id,$boss_id);
$this->log($sno,LOG_DEBUG);
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $sno;
	}

	/**
	 * aprv_ok
	 * @todo 	承認処理
	 * @param   array  $data 送信データ
	 * @return  array $rtn : 構築したデータを追加
	 */
	public function aprv_ok($data = array(),$auth = null,$msg = null){
		$rc = false;
		try{
			if(!$this->is_valid($data[$this->name]['id'])){
$this->log('aprv_ng 決裁権限なし['.$data[$this->name]['id'].']');
				return false;
			}
			$this->recursive = 0;
			$aprv = $this->findById($data[$this->name]['id']);
$this->log('aprv ok ============================',LOG_DEBUG);
			if($aprv[$this->name]['aprv_stat'] != VALUE_AprvStat_None){
				// おそらくメールで決裁済
				return false;
			}
			$_zip_rtn = true;
			if($aprv['Content']['opt_encryption'] == 'on'){
$this->log('aprv ok パスワード付き　zip オプションあり　=====',LOG_DEBUG);
				if(CakePlugin::loaded('Encrypt')){
					$this->loadModel('Encrypt.Encrypt');
					$cid = $aprv['Content']['id'];
					if($aprv['Content']['opt_tfg'] == 'on'){
$this->log('TFG　zip オプションあり　=====',LOG_DEBUG);
					// デバッグ中はパスワードきめ打ち
						$_zpwd = $this->mkrandamstring(VALUE_Encryption_Pwd_Length,false);
						// ↓　第３パラメータは承認の有無（ここでは承認なししか来ないはず）
						$rtn = $this->Encrypt->mkzip2($cid,$_zpwd);
//$this->log($rtn,LOG_DEBUG);
					} else {
$this->log('zip オプションあり(通常)　=====',LOG_DEBUG);
					// デバッグ中はパスワードきめ打ち
						$_zpwd = $this->mkrandamstring(VALUE_Encryption_Pwd_Length,false);
						// ↓　第３パラメータは承認の有無
						$rtn = $this->Encrypt->mkzip($cid,$_zpwd);
					}
$this->log($rtn,LOG_DEBUG);
					if($rtn < 0){
						$_zip_rtn = false;
					}
				}
			}

			if($_zip_rtn){
				$aprv[$this->name]['aprv_date'] = $this->now();
				$aprv[$this->name]['modified'] = null;
				$aprv[$this->name]['aprv_user_id'] = $auth['id'];

				if(!empty($msg)){
					$message = $this->getAutoMsg($data[$this->name]['id'],$msg);
					$aprv[$this->name]['message'] = $message;
				} else {
					$aprv[$this->name]['message'] = $data[$this->name]['message'];
				}

				$aprv[$this->name]['aprv_stat'] = VALUE_AprvStat_OK;
                $rc = $this->save($aprv);
                // 有効期限を更新
                $this->loadModel('Content');
                $this->Content->setExpdate($aprv[$this->name]['content_id']);
			} else {
				// パスワード付き　ZIP 処理失敗
$this->log('承認されたが パスワード付き　zip に失敗した　=====',LOG_DEBUG);
				$aprv[$this->name]['aprv_date'] = $this->now();
				$aprv[$this->name]['modified'] = null;
				$aprv[$this->name]['aprv_user_id'] = $auth['id'];
				$aprv[$this->name]['aprv_stat'] = VALUE_AprvStat_NG;
				$rc = $this->save($aprv);
			}
//$this->log($rc,LOG_DEBUG);

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $rc;
	}
	/**
	 * aprv_ng
	 * @todo 	却下処理
	 * @param   array  $data 送信データ
	 * @return  array $rtn : 構築したデータを追加
	 */
	public function aprv_ng($data = array(),$auth = null,$msg = null){
		$rc = false;
		try{
            if($auth == 'shell'){
$this->log('aprv_ng shell 起動['.$data[$this->name]['id'].']');
            } else {
                if(!$this->is_valid($data[$this->name]['id'])){
    $this->log('aprv_ng 決裁権限なし['.$data[$this->name]['id'].']');
                    return false;
                }
            }
			$this->recursive = -1;
			$aprv = $this->findById($data[$this->name]['id']);
			if($aprv[$this->name]['aprv_stat'] != VALUE_AprvStat_None){
				// おそらくメールで決裁済
				return false;
			}
			$aprv[$this->name]['aprv_date'] = $this->now();
			$aprv[$this->name]['modified'] = null;
//debug($data);
			// 第3パラメータはシステムが自動で設定するメッセージ
			if(!empty($msg)){
				$message = $this->getAutoMsg($data[$this->name]['id'],$msg);
				$aprv[$this->name]['message'] = $message;
			} else {
				// Form　でメッセージが入っていたとき
				$aprv[$this->name]['message'] = $data[$this->name]['message'];
			}

			if(is_array($auth)){
				$aprv[$this->name]['aprv_user_id'] = $auth['id'];
			} else {
				// 期限切れによる却下のときは、承認を依頼された人が却下したことにする
				$aprv[$this->name]['aprv_user_id'] = $aprv[$this->name]['aprv_req_user_id'];
			}
			$aprv[$this->name]['aprv_stat'] = VALUE_AprvStat_NG;
			$rc = $this->save($aprv);
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $rc;
	}

/**
 * setAutoMsg (自動メッセージを送信元の言語にあわせて設定）
 *　確認画面は出さない
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	function getAutoMsg($id = null, $msg = null){
		$str = '';
		try{
$this->log('----------------setAutoMsg['.$msg.']');
//$this->log($this->data,LOG_DEBUG);
			// 送信元の情報を取り出す
			$contain = array('Content' => array('User'));
			$data = $this->find('first',array(
					'conditions' => array('Approval.id' => $id),
					'contain' => $contain,
					'recursive' => false));
//$this->log($data);
			// 送信元の言語
			$my_theme = Configure::read('Config.theme');
			$target_lang = $data['Content']['User']['lang'];

			// 現在の言語
			$now_lang = $this->getLang();

			// 送信元の言語で変換
			$this->setLang($target_lang);
			$str = __d($my_theme,$msg);

			// 画面表示のため元に戻す
			$this->setLang($now_lang);


		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
$this->log('----------------setAutoMsg['.$str.']');
		return $str;
	}


	/**
	 * getAprv
	 * @todo 	contents に対応する　aprv を求める
	 * @param   array  $data 送信データ
	 * @return  array $rtn : 構築したデータを追加
	 *
	 */
	public function getAprv($contents = array()){
		$aprv = $contents;
		try{
			if(Hash::check($contents,'{n}.Content.id')){
				$cids = Hash::combine($contents,'{n}.Content.id');
				$this->recursive = -1;
				$list1 = $this->find('all',array(
						'conditions' => array( $this->name.'.content_id' => array_keys($cids)),
						'recursive' => -1));
				$list2 = Hash::combine($list1,'{n}.'.$this->name.'.content_id','{n}');
				foreach($aprv as $k => $v){
					$cid = $v['Content']['id'];
					if(isset($list2[$cid])){
						$aprv[$k][$this->name] = $list2[$cid][$this->name];
					} else {
						$aprv[$k][$this->name] = null;
					}
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $aprv;
	}

/**
 * getExpirationList
 * @todo 決裁期限が過ぎても決裁されていないコンテンツを取得
 * @param date $date : 日付（null のときは現在）
 * @var array : ファイル名リスト
 */
	public function getExpirationList($date = null){
		try{
			$expdate = $date;
			// パラメータの指定がないときは現在の日付
			if($expdate == null){
				$expdate = $this->today();
			}

			// 新たに期限が切れたもの(処理中のものは除く)
			$list = $this->find('list',array(
					'fields' => array(
						'id','content_id'
//						,'is_deleted','is_expdate','expdate','aprv_stat'
					),
					'conditions' => array(
						'is_deleted' => false,
						'is_expdate' => 'N',
						'expdate <' => $expdate,
						'aprv_stat' => VALUE_AprvStat_None
					),
					'recursive' => -1
				));
			return $list;
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
$this->log('approval is_valid['.$id.']');
			if(!$this->exists($id)){
$this->log('approval 存在しない['.$id.']');
				return false;
			}
			// データの整合性チェックのため
			$auth = CakeSession::read('auth');
			$_my_id = $auth['id'];
			$this->loadModel('Role');
			$_is_super = $this->Role->isSuper($auth['group_id']);
			$_can_aprv = $this->Role->chkRole($auth['group_id'],array(
				'controller' => 'approvals',
				'action' => 'approval',
			));

			// データの整合性チェックを追加　（スーパーは除外）
			if(!$_is_super){
$this->log('approval not super['.$_my_id.']');
				if(!$_can_aprv){
$this->log('approval not aprv['.$_my_id.']');
					// スーパーでなく、承認権限もなければNG
					return false;
				}
			}
$this->log('approval ok['.$_my_id.']');

			return true;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 * findForView
	 * @todo 	contents に対応する　aprv を求める
	 * @param   $id :ID
	 * @param   $valid_skip :true=正当性チェック　/　false=チェックなし（内部で使用）
	 * @return  array $rtn : 構築したデータを追加
	 *
	 */
	public function findForView($id = null,$valid_skip = false){
	$aprv = array();
		try{
			if($valid_skip){
			} else {
				if(!$this->is_valid($id)){
					// 権限なし
					return array();
				}
			}
			$contain = array(
				'Content' => array(
					'User' => array(
						'UserExtension'
					),
					'Status' => array(
						'User' => array(
							'UserExtension'
						),
					),
					'Uploadfile',
				),
				'AprvReqUser' => array(
						'UserExtension'
				),
				'AprvUser' => array(
						'UserExtension'
				)
			);

			$aprv = $this->find('first',array(
				'conditions' => array('Approval.id' => $id),
				'contain' => $contain,
				'recursive' => false

			));

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $aprv;
	}

	/**
	 * getKariData
	 * @todo 	承認依頼する予定の情報の仮データをつくる（表示のため）
	 * @param   array: $data content data
	 * @return  array $rtn : 構築したデータ
	 *
	 */
	public function getKariData($data = array()){
        $aprv = array();
		try{
            $rdata = array();
            if(!empty($data) && Hash::check($data,'Content.reserve_id')){
                $this->loadModel('Reserve');
                $rdata = $this->Reserve->findById($data['Content']['reserve_id']);
                if(!empty($rdata) && Hash::check($rdata,'Reserve.rsv_data.Approval')){
                    $aprv_req_user_id = Hash::get($rdata,'Reserve.rsv_data.Approval.aprv_req_user_id');
                    $this->loadModel('User');
                    $aprvreq = $this->User->find('first',array(
                        'conditions' => array('User.id' => $aprv_req_user_id),
                        'contain' => array('UserExtension'),
                        'recursive' => false
                        ));
                    $aprv = array('Approval' => Hash::get($rdata,'Reserve.rsv_data.Approval'));
                    $aprv['AprvReqUser'] = $aprvreq['User'];
                    $aprv['AprvReqUser']['UserExtension'] = $aprvreq['UserExtension'];
                 }

            }
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $aprv;
	}

	/**
	 * getHistory
	 * @todo 	contents に対応する　aprv を求める
	 * @param   array  $data 送信データ
	 * @return  array $rtn : 構築したデータを追加
	 *
	 */
	public function hide($id = null){
		$count = 0;
		try{
			$ids = $id;
			// すべて形式を　array にする
			if(!is_array($id)){
				$ids = array($id);
			}
$this->log($ids,LOG_DEBUG);
			foreach($ids as $k => $v){
$this->log('hide ---1 ['.$v.']',LOG_DEBUG);
				if($this->exists($v)){
$this->log('hide ---2 ['.$v.']',LOG_DEBUG);
					// softdelete
					$this->delete($v);
$this->log('hide ---3 ['.$v.']',LOG_DEBUG);
					$count++;
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $count;
	}

/**
 * countMyRequest
 * @todo	当該ユーザに依頼された情報を求める
 * @param	int $uid : ユーザID
 * @var array 	:	'new' => 未決（自分宛）
 * @var array 	:	'all' => 未決（管理者：自分の統括部門、　スーパー：全部）
 */
	public function countMyRequest($uid = null,$is_super = false){
		$data = array('mine' => 0 , 'all' => 0);
		try{
			$this->loadModel('User');
			if($this->User->exists($uid)){
				$user = $this->User->find('first',array('conditions' => array('id' => $uid),'recursive' => -1));
//debug($user);
				$aprvMembers = array();
				if(defined('SECTIONS')){
					/*************************************************
					 *  ここの処理で部門管理が変になってるみたい　2016/07/07
					 **************************************************/

					$this->loadModel('Sections.Section');

					// tree をたどって自分より下位を探す
					$mySecLst = $this->Section->getLowLevel($uid);

					// リーダーになれる権限の人
					$this->loadModel('Role');
					$gids = $this->Role->getGroupList(array(
						'controller' => 'sections',
						'action' => 'can_leader'));

					$aprvMembers = @$this->Section->getMyAprvUserId($mySecLst,$gids);
					$aprvMembers[] = $uid;

				}
				// 自分宛
				$this->recursive = -1;
				$cond_mine = array(	'aprv_req_user_id' => $uid ,
								'aprv_stat' => VALUE_AprvStat_None
								);
				$mine = $this->find('count',array('conditions' => $cond_mine));
				$data['mine'] = $mine;

				if($is_super){
//					// super なら全体の未決数
					$cond_all = array('aprv_stat' => VALUE_AprvStat_None );
				} else {
					// super 以外なら自分が統括する部門の未決数
					if(defined('SECTIONS')){
						// 仮
						$cond_all = array(
									//	'contract_id' => $user['User']['contract_id'],
										'aprv_req_user_id' => $aprvMembers,
										'aprv_stat' => VALUE_AprvStat_None
										);
					} else {
						// 部門がないときは契約内全体
						$cond_all = array(	'contract_id' => $user['User']['contract_id'],
										'aprv_stat' => VALUE_AprvStat_None
										);
					}
				}
				// 全体
				$all = $this->find('count',array('conditions' => $cond_all));
				$data['all'] = $all;
			}
//debug($data);
		} catch (Exception $e){

//debug($e);
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $data;
	}

}
