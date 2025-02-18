<?php
App::uses('AppModel', 'Model');
/**
 * Content Model
 */
class Content extends AppModel {

/**
 * Use database config
 *
 * @var string
 */
	public $name = 'Content';
//	var $order = array(	"created" => "desc");
/**
 * Use behavior
 *
 * 削除フラグ（tinyint) => 削除日(datetime) のフィールド名カスタマイズ
 * デフォルトは 'deleted' => 'deleted_date'
 */
	var $actsAs = array(
		'Search.Searchable',
		'Containable',
		'SoftDelete' => array(
			'is_deleted' => 'deleted',
		));

/**
 * Searchプラグイン設定
 */
	var $filterArgs = array(
		'keyword' => array('type' => 'query', 'method' => 'searchKeyword'),
		'from_created' => array('type' => 'query', 'method' => 'fromCreatedSearch', 'field' => 'Content.created'),
		'to_created' => array('type' => 'query', 'method' => 'toCreatedSearch', 'field' => 'Content.created'),
	);

	public function searchKeyword($data = array()) {
		$conditions = array();

		//	検索対象となるフィールドを設定（数字やフラグ、シリアライズ項目を除く、varchar や text のすべての項目が対象）
		$searchFields = array('Content.title', 'Content.cc', 'Content.bcc', 'Content.message');

		//	サニタイズが必要なフィールドには、サニタイズしたキーワードをセット
		$conditions['OR'] = $this->mkCondition($searchFields,$data['keyword']);
//debug($conditions);
		// 宛先検索　（アソシエーションが深いので、先にキーワード検索したID群をつくる
		$content_ids = $this->Status->findContentIdsByKeyword($data);
//debug($content_ids);
		if(!empty($content_ids)){
			$conditions['OR']['Content.id '] = $content_ids;
		}
		// ファイル名検索
		$files = $this->Uploadfile->find('all', array('conditions' => array('name LIKE' => '%'. $data['keyword']. '%'), 'fields' => array('content_id'), 'recursive' => -1));
		foreach($files as $file) {
			$conditions['OR']['Content.id'][] = (int)$file['Uploadfile']['content_id'];
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
	var $sanitizeItems = array(	'title' => array('html' => false),
								'message' => array('html' => false, 'serialize' => true),
								'conv_option' => array('html' => false, 'serialize' => true),
								'cc' => array('html' => false, 'serialize' => true),
								'bcc' => array('html' => false ,'serialize' => true),
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
//			'title' => array('rule' => 'notBlank',
//							'message' => 'The blank is not goodness.'),
			'title' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'last' => false,
					'message' => 'The blank is not goodness.',
				),
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					'last' => true,
					'message' => array('%s characters too long.','Title')
				),
			),
			'user_id' => array(
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
			'owner_id' => array(
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
			'free_add' => array(
				'validEmails' => array(
					'rule' => array('validEmails','free_add',true),
					'allowEmpty' => true,
					'message' => 'There are no valid mail addresses.',
				),
			),
			'cc' => array(
				'validEmails' => array(
					'rule' => array('validEmails','cc',true),
					'allowEmpty' => true,
					'message' => 'There are no valid mail addresses.',
				),
			),
			'bcc' => array(
				'validEmails' => array(
					'rule' => array('validEmails','bcc',true),
					'allowEmpty' => true,
					'message' => 'There are no valid mail addresses.',
				),
			),
			'approval_add' => array(
				'validAprv' => array(
					'rule' => array('validAprv','approval_add'),
					'allowEmpty' => true,
					'message' => 'There are no valid approval addresses.',
				),
			),
		),
		/* ↓使ってないかも2015/11/5 */
		'rsv' => array(
			'title' => array('rule' => 'notBlank',
							'message' => 'The blank is not goodness.'),
			'user_id' => array(
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
			'owner_id' => array(
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
		)
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
/*
		'Owner' => array(
			'className' => 'User',
			'foreignKey' => 'owner_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
*/
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'Status' => array(
			'className' => 'Status',
			'foreignKey' => 'content_id',
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
		'Uploadfile' => array(
			'className' => 'Uploadfile',
			'foreignKey' => 'content_id',
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
 * beforeValidate　validate前処理
 * @todo 	　db save 前に行う共通関数
 * @param    array	$this->data
 * @param    array 	$this->sanitizeItems
 * @return   bool
 */
	function beforeValidate($options = array()) {
		// オプションを設定
		if(!empty($this->data[$this->name]['opt'])){
			foreach($this->data[$this->name]['opt'] as $k => $v){
				$this->data[$this->name][$v] = 'on';
			}
		}
		parent::beforeValidate($options);
		return true;
	}


/**
 * setDefault
 * @todo 	　パラメータにより、初期値があったら設定する
 * @param    array	$this->request->params
 * @req      array	$this->request 　params にない情報を取りたいときのため
 * @return    array 	$content hash
 */
	function setDefault($params,$req = array()){
		try{
$this->log('setDefault -------1');
			if(empty($params['named'])){
				// named が empty のときは確認画面から戻ってきたとき　なので　reserve　を読む
$this->log('setDefault -------2');
				if(count($params['pass']) > 0){
$this->log('setDefault -------3');
					// 再編集　とりあえず最初のしか見ない
					$rid = $params['pass'][0];
					$content = $this->from_rsv($rid);
					return($content);
				}
			} else {
$this->log('setDefault -------5');
$this->log($this->auth);

			// アドレス帳から飛んでくるときやコピー送信などのときはこちら
				// form を解釈してあとで必要情報を追加する
//				$content = $this->newData();
				switch($params['named']['from']){
					case 'Reply':	// Reply (pass は status id)
						$content = $this->reply($params['pass']);
						return $content;
						break;
					case 'FrmStt':	// 受信履歴から一括送信
//$this->log($params['pass']);
						$content = $this->from_stt($params['pass']);
						return $content;
						break;
					case 'AdCopy':	// AddressCopy (pass は content id)
						$content = $this->adcopy($params['pass']);
						return $content;
						break;
					case 'Copy':	// Copy (pass は content id)
						$content = $this->copy($params['pass']);
						return $content;
						break;
					case 'sttm':	// このメンバーに再送
						$content = $this->sttm($req->query);
						return $content;
						break;
					case 'ab':	//　addressbook (pass は　addressbook id)
						$content = $this->from_ab($params['pass']);
						return $content;
						break;
					case 'ag':	//　addressgroup (pass は　addressgroup id)
						// デフォルトは再帰的にグループを検索して含まれるメンバをとりだす
						// もし、再帰の要・不要を振り分ける必要があればここで
						$content = $this->from_ag($params['pass']);
						return $content;
						break;
					case 'Forward':	// Forward (pass は content id)
						$content = $this->forward($params['pass']);
						return $content;
						break;
					case 'User':	// Forward (pass は content id)
						$content = $this->from_user($params['pass']);
						return $content;
						break;

/**********************************
 *  ここから下は未調整
 **********************************/

					default:
$this->log('####################### default');
						break;
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. $e->getMessage());
			return array();
		}
		return $this->newData();
	}

/**
 * reply status_id から 返信用データを作成
 * @todo 	　パラメータにより、初期値があったら設定する
 * @param    array	$this->request->params
 * @return    array 	$content hash
 */
	function reply($pass = array()){
		try{
$this->log('--- reply content');
			$content = $this->newData();
			$addresslist = array();
			$this->recursive = 0;
			$sid = $pass[0];	// 返信はひとつだけ
			$this->loadModel('Status');
			if(!$this->Status->is_valid($sid)){
$this->log('--- reply アクセス権限なし['.$sid.']');
			$content = $this->newData();
				return array();
			}
			$this->Status->recursive = 2;
			$cdata = $this->Status->findById($sid);
//debug($cdata);
			// 自分
			$me = $cdata['User'];
			// 宛先
			$to = $cdata['Content']['User'];
			if($cdata){
				$this->loadModel('Role');
				$flg_select = $this->Role->chkRole($me['group_id'],
						array(	'controller' => 'contents',
								'action' => 'address_select'));
				// 何らかの原因で to ユーザ情報がとれなくなったときの対応
				if(!empty($to)){
					if($flg_select){ // アドレス帳を使用できるユーザ
						// アドレス帳の登録があればリスト
						$this->loadModel('Addressbooks.Addressbook',true);
						// まずは自分のアドレス帳にあるか調べる
						$abdata = $this->Addressbook->findEmailToAddressBookID($to['email'],$me);
						if($abdata){
							// 見つかったアドレス帳の最初のものをセット
							$content[$this->name]['add_list'][0] = $abdata[0];
						} else {
							// アドレス帳の登録がなければフリー宛先
							$content[$this->name]['free_add'] = array($to['email']);
						}
					} else {		// アドレス帳を持たないユーザ（ゲスト）
						// ゲストのときは常に free_add
						$content[$this->name]['free_add'] = array($to['email']);
            			$content[$this->name]['user_id'] = $me['id'];
            			$content[$this->name]['owner_id'] = $cdata['Content']['owner_id'];
					}
				}
			}
			// 何らかの理由で宛先の言語が分からないときはとりあえず現在の言語
			$content[$this->name]['lang'] = isset($to['lang']) ? $to['lang'] : $this->getLang();
			$content[$this->name]['title'] = REPLY_HEAD . $cdata[$this->name]['title'];
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $content;
	}

/**
 * from_stt status_id から 一括送信データを作成
 * @todo 	　パラメータにより、初期値があったら設定する
 * @param    array	$this->request->params
 * @return    array 	$content hash
 */
	function from_stt($pass = array()){
		try{
			$content = $this->newData();
			$addresslist = array();
			$this->recursive = 0;
			$this->loadModel('Status');
			$this->Status->recursive = 2;
			$cdata_ary = $this->Status->find('all',
					array('conditions' => array('Status.id' => $pass)));
			// 自分
			$me = CakeSession::read('auth');									//	ログインユーザ情報を取得
			$this->loadModel('Role');
			$flg_select = $this->Role->chkRole($me['group_id'],
					array(	'controller' => 'contents',
							'action' => 'address_select'));

			$this->loadModel('Addressbooks.Addressbook',true);

			// 宛先
			$add_list = array();
			$free_add = array();
			$owner_id = $me['id'];
			foreach($cdata_ary as $cdata){
				$to = $cdata['Content']['User'];
				$abdata = $this->Addressbook->findEmailToAddressBookID($to['email'],$me);
				if($abdata){
					// 見つかったアドレス帳の最初のものをセット
					if(Hash::check($abdata,'0.Addressbook.id')){
						$ab_id = Hash::get($abdata,'0.Addressbook.id');
						$add_list[$ab_id] = $ab_id;
					}
				} else {
					// アドレス帳の登録がなければフリー宛先
					$free_add[$to['email']] = $to['email'];
				}
				if(!$flg_select){
					$owner_id = $cdata['Content']['owner_id'];
					break;
				}
			}
			$content[$this->name]['add_list'] = array_keys($add_list);
			$content[$this->name]['free_add'] = array_keys($free_add);
//			$content[$this->name]['user_id'] = $me['id'];
//			$content[$this->name]['owner_id'] = $owner_id;
//			$content[$this->name]['lang'] = $to['lang'];
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $content;
	}

/**
 * from_rsv 再編集　reserve から送信用データを作成
 * @todo 	　パラメータにより、初期値があったら設定する
 * @param    int	$rid reserve_id$this->request->params
 * @return    array 	$content hash
 */

	function from_rsv($rid = array()){
		try{
$this->log('### from_rsv start['.$rid.']',LOG_DEBUG);
			$this->loadModel('Reserve');
			$rdata = $this->Reserve->findById($rid);
//$this->log($rdata,LOG_DEBUG);
			if(Hash::check($rdata,'Reserve.rsv_data.Content')){
				// reserve id を返す
				$rdata['Reserve']['rsv_data']['Content']['reserve_id'] = $rid;

				$content = $rdata['Reserve']['rsv_data'];
				$user = $rdata['User'];

				$this->loadModel('Role');
				$flg_select = $this->Role->chkRole($user['group_id'],
						array(	'controller' => 'contents',
								'action' => 'address_select'));
				$add_list = array();
				$free_add = array();
				if($flg_select){ // アドレス帳を使用できるユーザ
					// アドレス帳の登録があればリスト
					$this->loadModel('Addressbooks.Addressbook',true);
					$this->Addressbook->recursive = -1;
					foreach($content['Content']['Status'] as $k => $stt){
						if(Hash::check($stt,'Status.addressbook_id')){
							$abdata = $this->Addressbook->findById($stt['Status']['addressbook_id']);
							if($abdata){
								if($abdata['Addressbook']['email'] == $stt['Status']['email']){
									$add_list[] = $abdata;
									continue;
								}
							}
						}
						$abdata = $this->Addressbook->findEmailToAddressBookID($stt['Status']['email'],$user);
						if($abdata){
							// 見つかったアドレス帳の最初のものをセット
							$add_list[] = $abdata[0];
						} else {
							// アドレス帳の登録がなければフリー宛先
							$free_add[] = $stt['Status']['email'];
						}

					}
				} else {		// アドレス帳を持たないユーザ（ゲスト）
					// ゲストのときは常に free_add
					if(Hash::check($content,'Content.Status.0.Status.email')){
						$free_add[]	= Hash::get($content,'Content.Status.0.Status.email');
					}
				}

				$content[$this->name]['add_list'] = $add_list;
				$content[$this->name]['free_add'] = $free_add;
				$content[$this->name]['Status'] = array();
				// 前にアップロードしたファイルが残っていたら使いまわす

				$this->loadModel('Uploadfile');
				$uploadary = array();
//$this->log($content,LOG_DEBUG);
				if(Hash::check($content,'Content.Uploadfile')){
					foreach($content['Content']['Uploadfile'] as $uploadfile){
						// ファイルが存在すること
						if($this->Uploadfile->isFileExists($uploadfile['Uploadfile']['path'])){
							// 削除されていないもの （内部で zip したかどうかはまた後日）
							if(!$uploadfile['Uploadfile']['is_deleted']){
								$uploadfile['Uploadfile']['id'] = '';
								$uploadfile['Uploadfile']['content_id'] = '';
								$uploadfile['Uploadfile']['dl_cnt'] = 0;
								$uploadary[] = $uploadfile;
							}
						}
					}
				}
				$content[$this->name]['Uploadfile'] = $uploadary;
				return($content);
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}

/**
 * copy content_id から 返信用データを作成
 * @todo 	　パラメータにより、初期値があったら設定する
 * @param    array	$this->request->params
 * @return    array 	$content hash
 */
	function copy($pass = array()){
		try{
			$content = $this->newData();

			$cid = $pass[0];	// コピー元はひとつだけ
            // forward のとき困るので
//			if(!$this->is_valid($cid)){
//$this->log('--- copy アクセス権限なし['.$cid.']');
//				return array();
//			}
			$cdata = $this->findById($cid);
			// 基本情報をコピー
			$me = CakeSession::read('auth');									//	ログインユーザ情報を取得
			$this->loadModel('Role');
			$flg_select = $this->Role->chkRole($me['group_id'],
					array(	'controller' => 'contents',
							'action' => 'address_select'));
            if(!$flg_select){
                // ゲストのときだけ設定
                $content[$this->name]['user_id'] = $me['id'];
    			$content[$this->name]['owner_id'] = $cdata['Content']['owner_id'];
            }
			$content[$this->name]['title'] = $cdata[$this->name]['title'];
			$content[$this->name]['lang'] = $cdata[$this->name]['lang'];
			$content[$this->name]['message'] = $cdata[$this->name]['message'];

			$this->loadModel('Status');
			// add_list , free_add 宛先編集
			$addresses = $this->Status->makeAddListFromStatuses($cdata['Status']);// reserve id を返す
			$content[$this->name]['add_list'] = $addresses['add_list'];
			$content[$this->name]['free_add'] = $addresses['free_add'];

			// 前にアップロードしたファイルが残っていたら使いまわす
			$this->loadModel('Uploadfile');
			$uploadary = array();
			if(Hash::check($cdata,'Uploadfile')){
				foreach($cdata['Uploadfile'] as $uploadfile){
					// ファイルが存在すること
					if($this->Uploadfile->isFileExists($uploadfile['path'])){
						// 内部でzip したものではなく、削除されていないこと
						if($uploadfile['zpass'] == null && !$uploadfile['is_deleted']){
							$uploadfile['id'] = '';
							$uploadfile['content_id'] = '';
							$uploadfile['dl_cnt'] = 0;
							$uploadary[] = array('Uploadfile' => $uploadfile);
						}
					}
				}
			}
			$content[$this->name]['Uploadfile'] = $uploadary;
			return($content);
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}
/**
 * foward content_id から 返信用データを作成
 * @todo 	　パラメータにより、初期値があったら設定する
 * @param    array	$this->request->params
 * @return    array 	$content hash
 */
	function forward($pass = array()){
		try{
			$content = $this->copy($pass);
			if(empty($content)){
$this->log('--- forward アクセス権限なし');
				return array();
			}
			$content[$this->name]['title'] = 'Fw:'.$content[$this->name]['title'];
			$content[$this->name]['add_list'] = array();
			$content[$this->name]['free_add'] = array();
			return $content;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}

/**
 * adcopy content_id から 返信用データを作成
 * @todo 	　パラメータにより、初期値があったら設定する
 * @param    array	$this->request->params
 * @return    array 	$content hash
 */
	function adcopy($pass = array()){
		try{
$this->log('adcopy start',LOG_DEBUG);
			$content = $this->newData();
			$this->loadModel('Status');
			$add_list = array();
			$free_add = array();
			$this->loadModel('Role');
			$me = CakeSession::read('auth');									//	ログインユーザ情報を取得
            $flg_select = $this->Role->chkRole($me['group_id'],
					array(	'controller' => 'contents',
							'action' => 'address_select'));
			foreach($pass as $cid){
				if($this->is_valid($cid)){
					$cdata = $this->findById($cid);
					// add_list , free_add 宛先編集
					$addresses = $this->Status->makeAddListFromStatuses($cdata['Status']);// reserve id を返す
					$add_list = array_merge($add_list,$addresses['add_list']);
					$free_add = array_merge($free_add,$addresses['free_add']);
                    // ゲストなら１回しか回らないはずで、
                    // コピー元の基本情報を入れておく
                    if(!$flg_select){
            			$content[$this->name]['user_id'] = $cdata['Content']['user_id'];;
            			$content[$this->name]['owner_id'] = $cdata['Content']['owner_id'];
                    }
				}
			}
			$content[$this->name]['add_list'] = $add_list;
			$content[$this->name]['free_add'] = $free_add;
			return $content;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}

/**
 * from_ab アドレス帳メンバ id から返信用データを作成
 * @todo 	　パラメータにより、初期値があったら設定する
 * @param    array	$this->request->params
 * @return    array 	$content hash
 */
	function from_ab($pass = array()){
		try{
			$addresslist = array();
			$this->recursive = 0;
			$this->loadModel('Addressbooks.Addressbook');
			$add_list = $this->Addressbook->find('all',array(
				'conditions' => array('id' => $pass),
				'recursive' => -1));
			$content = $this->newData();
			$content[$this->name]['add_list'] = $add_list;

			return $content;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}

/**
 * from_ag アドレスグループ id から返信用データを作成
 * @todo 	　パラメータにより、初期値があったら設定する
 * @param   array	$pass : $this->request->params
 * @param	bool	$recursive : true = 再帰あり / false = 再帰なし
 * @return    array 	$content hash
 */
	function from_ag($pass = array(),$recursive = true){
		try{
			$_pass = $pass;
			$addresslist = array();
			if($recursive){
				// 再帰的にメンバリストを求めるとき
// ログインユーザ情報が必要な時はこうやって↓
//				$user = CakeSession::read('auth');									//	ログインユーザ情報を取得
//debug($user);
				$this->loadModel('Addressbooks.Addressgroup');
				$pass2 = $this->Addressgroup->generateTreeListFromParentId($pass[0]);
				$_pass = array_keys($pass2);

			}
			// 対象グループに含まれるメンバーのIDを求める
			$this->loadModel('Addressbooks.AddressbooksAddressgroup');
			$ab_list = $this->AddressbooksAddressgroup->find(
				'list',
				array(
					'fields' => array('id','addressbook_id'),
					'conditions' => array('addressgroup_id' => $_pass),
					'group' => 'addressbook_id',
				)
			);

			// メンバーの詳細を求める
			$this->loadModel('Addressbooks.Addressbook');
			$add_list = $this->Addressbook->find('all',array(
				'conditions' => array('id' => $ab_list),
				'group' => 'email',
				'recursive' => -1));

			// 送信の宛先初期設定
			$content = $this->newData();
			$content[$this->name]['add_list'] = $add_list;
			return $content;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}

/**
 * from_user ユーザ一覧　から送信用データを作成
 * @todo 	　パラメータにより、初期値があったら設定する
 * @param    array	$this->request->params
 * @return    array 	$content hash
 */
	function from_user($pass = array()){
		try{
			$addresslist = array();
			$this->recursive = 0;
			$this->loadModel('User');
			$add_list = $this->User->find('list',array(
				'conditions' => array('id' => $pass),
				'fields' => array('id','email'),
				'recursive' => -1));
			$content = $this->newData();
			$content[$this->name]['free_add'] = $add_list;

			return $content;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}

/**
 * reply content_id から 返信用データを作成
 * @todo 	　パラメータにより、初期値があったら設定する
 * @param    array	$this->request->params
 * @return    array 	$content hash
 */
/*
	function again($content = array(), $pass = array()){
		try{
			$addresslist = array();
			$this->recursive = 1;
			foreach($pass as $ck => $cv){
//				$cdata = $this->findById($cv, array('contain' => array('Status.email')));
				$cdata = $this->find('first', array(
					'conditions' => array('Content.id' => $cv),
					'contain' => array('Status.email')));
				debug($cdata);
				if($cdata){
					foreach($cdata['Status'] as $sk => $sv){
					}
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
	}
*/
/**
 * sttm Send To These Members
 * @todo 	　パラメータにより、初期値があったら設定する
 * @param    array	$this->request->params
 * @return    array 	$content hash
 */
	function sttm($query = array()){
		try{
			$cid = $query['id'];
			$list = array_filter($query['status_id']);
			$keys = array_flip($list);

			$content = $this->newData();
			$cdata = $this->findById($cid);
			// 基本情報をコピー
			$content[$this->name]['title'] = $cdata[$this->name]['title'];
			$content[$this->name]['lang'] = $cdata[$this->name]['lang'];
			$content[$this->name]['message'] = $cdata[$this->name]['message'];
			$content[$this->name]['user_id'] = $cdata['Content']['user_id'];
			$content[$this->name]['owner_id'] = $cdata['Content']['owner_id'];

			$status = array();
			foreach ($cdata['Status'] as $k => $v){
				if(array_key_exists($v['id'],$keys)){
					$status[] = $v;
				} else {
				}
			}


			$this->loadModel('Status');
			// add_list , free_add 宛先編集
			$addresses = $this->Status->makeAddListFromStatuses($status);// reserve id を返す
			$content[$this->name]['add_list'] = $addresses['add_list'];
			$content[$this->name]['free_add'] = $addresses['free_add'];

			// 前にアップロードしたファイルが残っていたら使いまわす
			$this->loadModel('Uploadfile');
			$uploadary = array();
			if(Hash::check($cdata,'Uploadfile')){
				foreach($cdata['Uploadfile'] as $uploadfile){
					// ファイルが存在すること
					if($this->Uploadfile->isFileExists($uploadfile['path'])){
						// 内部でzip したものではなく、削除されていないこと
						if($uploadfile['zpass'] == null && !$uploadfile['is_deleted']){
							$uploadfile['id'] = '';
							$uploadfile['content_id'] = '';
							$uploadfile['dl_cnt'] = 0;
							$uploadary[] = array('Uploadfile' => $uploadfile);
						}
					}
				}
			}
			$content[$this->name]['Uploadfile'] = $uploadary;
			return $content;

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
	}

/**
 * rev_check リザーブに入っているデータがこのまま送信可能か調べる
 * @todo 	　validation を使おうと思ったがうまくいかなかったので・・・
 * @param    array	$this->request->params
 * @return    array 	$content hash
 */
	function rev_check($rid,$rsv_data = array()){
//debug($rsv_data);
$this->log('rev_check rid['.$rid.']');
		try{
			// バリデーションのチェック
			if (!Validation::notBlank(@$rsv_data['title'])) {
$this->log(__FILE__ .':'. __LINE__ .': invalid title');
				return false;
			}
			if (!Validation::numeric(@$rsv_data['user_id'])){
$this->log(__FILE__ .':'. __LINE__ .': invalid user_id');
				return false;
			}
			if (!Validation::numeric(@$rsv_data['owner_id'])){
$this->log(__FILE__ .':'. __LINE__ .': invalid owner_id');
				return false;
			}
			if (!is_array(@$rsv_data['add_list'])){
				if(!is_array(@$rsv_data['free_add'])){
$this->log(__FILE__ .':'. __LINE__ .': invalid add_list and free_add');
					return false;
				}
			}
			// 有効な宛先がない
			if(count($rsv_data['Status']) < 1){
$this->log(__FILE__ .':'. __LINE__ .': no To address');
					return false;
			}

			// このIDから既に送信されているか調べる
			$content = $this->getFrmRsvIds($rid);
			// まだだったらとりあえずOK

			if(empty($content)) return true;

			// 既に送った実績があったらその結果が正常終了以外だったら再送不可
			switch($content[0][$this->name]['status_code']){
				case VALUE_Status_Error :
				// ステータス : エラー
				case VALUE_Status_Conv_Error :
				// ステータス : 変換エラー
				return false;

				case VALUE_Status_Aprv_Waiting :
				// ステータス : 承認待ち
				case VALUE_Status_Aprv_Rjct :
				// ステータス : 却下
				case VALUE_Status_Conv_Waiting :
				// ステータス : 変換待ち
				case VALUE_Status_Conv_Doing :
				// ステータス : 変換中
				case VALUE_Status_Waiting :
				// ステータス : 待ち
				case VALUE_Status_Doing :
				// ステータス : 処理中
				case VALUE_Status_Done :
				// ステータス : 終了
				default:
				break;
			}
$this->log('rev_check end true');

			return true;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
$this->log('rev_check end FALSE');
		return false;
	}

/**
 * saveAll パラメータにより、初期値があったら設定する
 * @todo 	　パラメータにより、初期値があったら設定する
 * @param    array	$this->request->params
 * @return    array 	$content hash
 */
	function saveAll($data = null){
		$ret = null;
		try{
$this->log('Content SaveAll',LOG_DEBUG);
$this->log('Content SaveAll ['.print_r($data,true).']',LOG_DEBUG);
			// アップロードサイズ　登録
			// 戻り値が　true のとき、オーバーしてる
			$this->loadModel('Uploadresult');
			$uploadresult_id = $this->Uploadresult->saveResult($data);
$this->log('Content Uploadresult->saveResult ['.$uploadresult_id.']',LOG_DEBUG);

			// 送信人数カウント
			$address_count = count($data['Status']);
			$data[$this->name]['address_count'] = $address_count;

			$tfg_encoded_count = 0;	// エンコードされているファイル数
			// ↑　これが　１以上のときは強制的に tfg モード
			$_do_zip_now = false;
			// パスワード付きZIP指定があった時

			// 送信ファイルカウント(いまはzip　とか考えない）
			$uploadfile_count = 0;
			$uploadfile_totalsize = 0;
//debug($data);
			if(Hash::check($data,$this->name.'.Uploadfile')){

				foreach($data[$this->name]['Uploadfile'] as $k => $v){
					if($v['Uploadfile']['size'] > 0){
						$uploadfile_count++;
						$uploadfile_totalsize += $v['Uploadfile']['size'];
					}
					if($v['Uploadfile']['enc_type'] == VALUE_Enctype_Enc){
	$this->log('TFG 暗号化ファイルがみつかりました',LOG_DEBUG);
						$tfg_encoded_count++;
					}
				}
			} elseif(Hash::check($data,'Uploadfile')) {
$this->log(__FILE__ .':'. __LINE__ .': ここにきますか？',LOG_DEBUG);

/*
debug($data);
				foreach($data['Uploadfile'] as $k => $v){
					if($v['size'] > 0){
						$uploadfile_count++;
						$uploadfile_totalsize += $v['size'];
					}
					if($v['enc_type'] == VALUE_Enctype_Enc){
	$this->log('TFG 暗号化ファイルがみつかりました',LOG_DEBUG);
						$tfg_encoded_count++;
					}
				}
*/
			}
			// 有効期限
			$expdate = '';
			if(isset($data[$this->name]['time_limit'])){
				$expdate = $this->getexpday($data[$this->name]['time_limit']);
			} else {
$this->log('default time limit');
				$expdate = $this->getexpday(VALUE_content_limit_default);
			}

			$data[$this->name]['address_count'] = $address_count;
			$data[$this->name]['uploadfile_count'] = $uploadfile_count;
			$data[$this->name]['uploadfile_totalsize'] = $uploadfile_totalsize;
			$data[$this->name]['expdate'] = $expdate;

			if($uploadfile_totalsize == 0){
	$this->log('ファイルなし',LOG_DEBUG);
				// ファイルがないときはファイル関連のオプションは何があっても無視
					$data[$this->name]['opt_tfg'] = 'off';
					$data[$this->name]['opt_encryption'] = 'off';
					$data[$this->name]['opt_pscan'] = 'off';
					$data[$this->name]['conv_option'] = '';
					$data[$this->name]['status_code'] = VALUE_Status_Done;	// 変換待ち
			} else {
				if($tfg_encoded_count > 0){
					// TFG 暗号化ファイルがあったらかならず　TFG　オプションをつける
					$data[$this->name]['opt_tfg'] = 'on';
					$data[$this->name]['status_code'] = VALUE_Status_Conv_Waiting;	// 変換待ち
				} elseif($data[$this->name]['opt_pscan'] == 'on'){
	$this->log('PSCAN オプションがみつかりました。',LOG_DEBUG);
					// TFG と Pscan が共存することはない
					// pscan があったら tfg と パスワード付きzip はやらない
					$data[$this->name]['opt_tfg'] = 'off';
					$data[$this->name]['opt_encryption'] = 'off';
					$data[$this->name]['conv_option'] = $data['pdf'];
					$data[$this->name]['status_code'] = VALUE_Status_Conv_Waiting;	// 変換待ち
				}
				if($data[$this->name]['opt_tfg'] == 'on'){
					$data[$this->name]['status_code'] = VALUE_Status_Conv_Waiting;	// 変換待ち
				}
	$this->log('status ['.@$data[$this->name]['status_code'].']',LOG_DEBUG);


				if($data[$this->name]['opt_encryption'] == 'on'){
					// tfg があったら後回し
					if($data[$this->name]['opt_tfg'] == 'off'){
						// tfg なしなら、入ってきたデータをZIP化する
						$_do_zip_now = true;
					}
				}
				if(Hash::check($data,'Approval')){
	$this->log('承認を求める',LOG_DEBUG);
					// 承認を求めるときは　zip もあとで
					$_do_zip_now = false;
				}
			}
			if($data == null) return false;
$this->log('saveAll',LOG_DEBUG);
//$this->log($data,LOG_DEBUG);
			$ret = parent::saveAll($data);
			if($ret){
				if($_do_zip_now){
					// プラグインがあれば実施
					if(CakePlugin::loaded('Encrypt')){
						$lastid = $this->getLastInsertID();
						$this->loadModel('Encrypt.Encrypt');
					// デバッグ中はパスワードきめ打ち
						$_zpwd = $this->mkrandamstring(VALUE_Encryption_Pwd_Length,false);
						// ↓　第３パラメータは承認の有無（ここでは承認なししか来ないはず）
						$rtn = $this->Encrypt->mkzip($lastid,$_zpwd);
//$this->log($rtn,LOG_DEBUG);
						if($rtn < 0){
							$ret = false;
						}
					}
				}
			}
//$this->log($ret,LOG_DEBUG);
			if($ret && $uploadresult_id > 0){
				// 今回アップロードしたファイルサイズが契約をオーバーしたとき
				// 既定の人にアラートメールを送る
				$lastid = $this->getLastInsertID();
				// super は除外
				$_auth = CakeSession::read('auth');
				$this->loadModel('Group');
				$is_super = $this->Group->is_super($_auth['group_id']);
				if(!$is_super){
$this->log('############## 容量超過(super は除外)',LOG_DEBUG);
//$this->log($data,LOG_DEBUG);
                    if($data['Content']['user_id'] != $data['Content']['owner_id']){
                        $this->loadModel('User');
                        $owner = $this->User->findById($data['Content']['owner_id']);
//$this->log($owner,LOG_DEBUG);
                        if(!$this->Group->is_super($owner['User']['group_id'])){
$this->log('############## 容量超過(ゲストの場合、super　への返信は除外)',LOG_DEBUG);
                            $this->sendOverInfo($lastid,$uploadresult_id);
                        }
                    } else {
                        $this->sendOverInfo($lastid,$uploadresult_id);
                    }
                }
$this->log('lastid['.$lastid.']',LOG_DEBUG);
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $ret;
	}

	/**
	 * setSendStatus
	 * @todo 	メール送信処理後のステータス
	 * @param	string $eid  : もとになるEventlogId
	 * @return  bool
	 */
	function sendOverInfo($cid = null,$ulid = null){
		try{
$this->log('sendOverInfo cid['.$cid.'] ulid['.$ulid.']',LOG_DEBUG);

			$this->bindModel(array('belongsTo' => array(
					'Owner' => array(
						'className' => 'User',
						'foreignKey' => 'owner_id',
						'conditions' => '',
						'fields' => '',
						'order' => ''
					),
				)),true);
			$content = $this->find('first',array(
				'conditions' => array('Content.id' => $cid),
				'contain' => array('User','Owner'),
				'recursive' => false,
				));
			$this->loadModel('Uploadresult');
			$uploadresult = $this->Uploadresult->findById($ulid);
//$this->log($content,LOG_DEBUG);
//$this->log($uploadresult,LOG_DEBUG);
				/** 新規送信時エラーになる箇所 2016/06/17
				 *  →　CommonBehavior に関数を追加したので解決したはず　2016/6/20
				 */
$this->log('############ 超過サイズ #####################');
$this->log($content);
				$logid = $this->writeLog(
				array(
					'type' => 'UploadOver',
					'login_id' => $content['User']['email'],
					'contract_id' => $content['Owner']['contract_id'],
					'event_action' => 'アップロード',
					'remark' => '契約サイズ',
					'result' => '超過',
					'event_data' => $uploadresult,
				));

//$this->log($logid,LOG_DEBUG);
				// アラートメール送信(ここら辺はフラグで制御かな)
				$this->loadModel('Mailqueue');
				$this->Mailqueue->putQueue('upload_over',$cid,$logid);

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
	}

	/**
	 * setEventStatus
	 * @todo 	メール送信処理後のステータス
	 * @param	string $eid  : もとになるEventlogId
	 * @return  bool
	 */
	function setEventStatus($eid){
$this->log('call content setstatus ==== C');
$this->log('setEventStatus ['.$eid .']');
		if($eid == null) return false;
		$rtn = true;
		try{
			$this->loadModel('Eventlog');
			$this->Eventlog->recursive = -1;
			$eventlog = $this->Eventlog->findById($eid);
			$content_id = $eventlog['Eventlog']['content_id'];
			if(is_null($content_id)) return false;

			$this->loadModel('Mailqueue');
			$this->Mailqueue->recursive = -1;
			$wait = $this->Mailqueue->find('count',array(
								'conditions' => array(
									'Mailqueue.eventlog_id' => $eid,
									'Mailqueue.status_code' => VALUE_Status_Waiting,
									)
								));
$this->log('メール送信待ち['.$wait.']');
			if($wait > 0){
$this->log('メール送信待ち');
				$this->setStatus($content_id,VALUE_Status_Waiting);
//				return true;
			}

			$all = $this->Mailqueue->find('count',array(
								'conditions' => array(
									'Mailqueue.eventlog_id' => $eid,
									)
								));
			$done = $this->Mailqueue->find('count',array(
								'conditions' => array(
									'Mailqueue.eventlog_id' => $eid,
									'Mailqueue.status_code' => VALUE_Status_Done,
									)
								));
			$doing = $all - $done;
$this->log('処理中all['.$all.']- done['.$done.']=['.$doing.']');
			if($doing > 0){
$this->log('処理中');
				$this->setStatus($content_id,VALUE_Status_Doing);
//				return true;
			}
$this->log('check1 wait['.$wait.'] all['.$all.']- done['.$done.']=['.$doing.']');

			$error = $this->Mailqueue->find('count',array(
								'conditions' => array(
									'Mailqueue.eventlog_id' => $eid,
									'Mailqueue.status_code' => VALUE_Status_Error,
									)
								));
// 一つでも smtp エラーになったら送信全体が見えなくなる不具合の解消
$this->log('エラーあり['.$error.']');
//			if($error === $all){
//$this->log('全件エラーあり');
//				$this->setStatus($content_id,VALUE_Status_Error);
//				return false;
//			} else {
$this->log('check wait['.$wait.'] all['.$all.']- done['.$done.']=['.$doing.']');
			if($wait === 0 ){
$this->log('待ちがなくなったら終了');
				$this->setStatus($content_id,VALUE_Status_Done);
			}
			return true;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. $e->getMessage());
			return false;
		}
	}

	/**
	 * setStatus
	 * @todo 	status を設定する
	 * @param   int  $id
	 * @param   int  $code : status
	 * @return  bool $rtn : 結果
	 */
	function setStatus($id = null, $code = 0){
		try{
$this->log('content setStatus start cid['.$id.'] code['.$code.']');
			if($id == null) return false;
			parent::save(array(
						'id' => $id,
						'status_code' => $code,
						'modified' => null,
						));

//			if($code === VALUE_Status_Done){
//$this->log('有効期限調整 cid['.$id.'] code['.$code.']');
//                $this->setExpdate($id);
//			}
			return true;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 * addressCountUp
	 * @todo 	address_conf_count をカウントアップする
	 * @param   int  $id
	 * @return  bool $rtn : 結果
	 */
	function addressCountUp($id = null){
		try{
			if($id == null) return false;
			$this->recursive = -1;
			$data = $this->findById($id);
			if(isset($data[$this->name]['id'])){
				$address_count = $data[$this->name]['address_count'];
				$address_conf_count = $data[$this->name]['address_conf_count'];
				$address_conf_rate  = $data[$this->name]['address_conf_rate'];
				if($address_conf_rate >= 1.00) return false;
				$address_conf_count++;
				// 宛先総数を越えない
				if($address_conf_count > $address_count) {
					$address_counf_count = $address_count;
				}
				$address_conf_rate = $address_conf_count / $address_count;
				// 1.00　を超えない
				if($address_conf_rate > 1.00){
					$address_conf_rate = 1.00;
				}

				parent::save(array(
					'id' => $id,
					'address_conf_count' => $address_conf_count,
					'address_conf_rate' => $address_conf_rate,
				));
				return true;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 * newData
	 * @todo 	初期設定
	 * @return  array $rtn : 初期データ
	 */
	function newData(){
		$newdata = $this->create();
		$newdata[$this->name]['title'] = '';
		$newdata[$this->name]['add_list'] = array();
		$newdata[$this->name]['free_add'] = array();
		$newdata[$this->name]['cc'] = array();
		$newdata[$this->name]['bcc'] = array();
		$newdata[$this->name]['Uploadfile'] = array();
		$newdata[$this->name]['opt'] = array();
		$newdata[$this->name]['time_limit'] = 0;
		$newdata[$this->name]['message'] = '';

		return $newdata;
	}

/**
 * saveData method Congroller から移動中
 * reserve してからくるときもおそらく get
 * @return void
 */
	public function saveData($rid = null) {
		try{
			$this->loadModel('Reserve');
			$rdata0 = $this->Reserve->findById($rid);
$this->log('データ取り出し');
//$this->log($rdata0);
			if(!is_array($rdata0)){
$this->log('データ取り出し失敗');
				return false;
			}

			/**
			 * ここは仮に addressbooks/add の権限で振り分けますが、
			 *　将来変更するかもしれません。2015.10.06
			 **/
			$this->loadModel('Role');
			$flg_browse = $this->Role->chkRole($rdata0['User']['group_id'],
					array(	'controller' => 'addressbooks',
							'action' => 'add'));
//$this->log($rdata0);
$this->log('権限をチェック['.$flg_browse.']');
			if($flg_browse){
$this->log('アドレス帳登録する');
				// 未登録のアドレスがあったら登録（アドレス帳番号を取得）
				$this->loadModel('Addressbooks.Addressbook');
				$rdata1 = $this->Addressbook->send($rdata0);
//$this->log($rdata1);
				if(!is_array($rdata1)){
$this->log('アドレス帳登録エラー');
					return false;
				}
			} else {
$this->log('アドレス帳登録しない');
				$rdata1 = $rdata0;
			}
			// 未登録のユーザがあったら登録（ユーザIDを取得）
			$this->loadModel('User');
			$rdata2 = $this->User->send($rdata1);
			if(!is_array($rdata2)){
$this->log('ユーザ登録エラー');
				return false;
			}

			if(Hash::check($rdata2, 'Reserve.rsv_data.Content.cc')){
				$cc = $this->formatAry(Hash::get($rdata2,'Reserve.rsv_data.Content.cc'));
				$rdata2['Reserve']['rsv_data']['Content']['cc'] = $cc;
			}
			if(Hash::check($rdata2, 'Reserve.rsv_data.Content.bcc')){
				$bcc = $this->formatAry(Hash::get($rdata2,'Reserve.rsv_data.Content.bcc'));
				$rdata2['Reserve']['rsv_data']['Content']['bcc'] = $bcc;
			}

			// ハッシュを整形
			$rdata2['Reserve']['rsv_data']['Content']['reserve_id'] = $rid;
			$rdata2['Reserve']['rsv_data']['Status'] = $rdata2['Reserve']['rsv_data']['Content']['Status'];
			$rdata2['Reserve']['rsv_data']['Uploadfile'] = $rdata2['Reserve']['rsv_data']['Content']['Uploadfile'];
$this->log('save start');
//$this->log($rdata2['Reserve']['rsv_data'],LOG_DEBUG);
			if($this->saveAll($rdata2['Reserve']['rsv_data'])){
//				$this->Reserve->delete($rid);
				$this->Reserve->softdelete($rid);
				$lastid = $this->getLastInsertID();
$this->log('登録ＯＫ['.$lastid.']['.$this->name.']');
				return $lastid;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

/**
 * formatAry
 * cc , bcc の最適化（email の形式でないものははじく）
 * @return array
 * 使われていないかも
 */
	public function formatAry($ary = array()){
		try{
			$ary1 = array();
			if(is_array($ary)){

				foreach($ary as $v0){
//debug($v0);
					$words = preg_split("/[ ;,]+/", $v0);
//debug($words);
					foreach($words as $v1){
						$ary1[] = $v1;
					}
				}
//debug($ary1);
				$ary2 = array();
				foreach($ary1 as $v){
					if( Validation::email($v)){
						$ary2[] = $v;
					}
				}
//debug($ary2);
				return $ary2;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $ary1;
	}

/* Validation が単体で使えるかテスト　使えるようです。 */
	public function Valid($text = ''){
		try{
			return Validation::email($text);
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}


/**
 * findAllData
 * view のときに使用するfind(softdelete されているファイルも出すため）
 * @return array
 * 使われていないかも
 */
	public function findAllData($options = array()){
		try{
			$this->recursive = 1;
			$options['conditions']['Content.is_deleted'] = array(0,1);
//debug($options);
			$data = $this->find('first',$options);
//debug($data);
			if($data[$this->name]['uploadfile_count'] > 0){
				// ファイルがある
				if(count($data['Uploadfile']) < 1){
debug('ファイルは削除されている');
					$this->loadModel('Uploadfile');
					$files = $this->Uploadfile->find('all',
						array('conditions' =>
							array('is_deleted' => array(true,false),
								  'content_id' => $data[$this->name]['id']
					)));
//debug($files);
				}
			}
			return $data;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}

/**
 * findForView
 * view のときに使用する
 * @return array
 */
	public function findForView($id = null){
		try{
			// 正当性チェック
			if(!$this->is_valid($id)){
				return array();
			}

			$contain = array(
				'User' => array(
					'UserExtension'
				),
				'Status' => array('User'),
				'Uploadfile',
			);
			if(defined('ERRMAILS')){
				$contain[] = 'Errmail';
				$this->bindModel(array(
					'hasMany' => array(
						'Errmail' => array(
							'className' => 'Errmails.Errmail',
							'foreignKey' => 'content_id',
							'dependent' => false,
							'conditions' => '',
							'fields' => array(
								'id','mail_type','content_id','status_id'
								),
							'order' => '',
							'limit' => '',
							'offset' => '',
							'exclusive' => '',
							'finderQuery' => '',
							'counterQuery' => ''
						)
					)
				));
			}

			$cond = array('Content.id' => $id);
			$data = $this->find('first', array(
				'conditions' => $cond ,
				'contain' => $contain,
				'recursive' => false
			));
			if(empty($data)) {
				// ここで見つからないときは権限がないので戻る
				return array();
			}
			// エラーメールの返ってきている　status を調べる
			$errmail = isset($data['Errmail']) ? $data['Errmail'] : array();
			$result = Hash::combine($errmail,'{n}.status_id');
			$data['Errmail'] = $result;

			// ファイルの存在チェック
			$this->loadModel('Uploadfile');
			foreach ($data['Uploadfile'] as $k => $v){
				if(!$v['is_deleted']){
					if($this->Uploadfile->isFileExists($v['path'])){
$this->log('##ありました['.$v['path'].']');
					} else {
$this->log('##なかった['.$v['path'].']');
						$data['Uploadfile'][$k]['is_deleted'] = true;
					}
				}
			}

			// 承認依頼をしているかどうか
			// approval_limit　> 0 のときは依頼がある
			if($data[$this->name]['approval_limit'] > 0){
				$this->loadModel('Approval');
				$aprv = $this->Approval->find('first',array(
					'conditions' => array('Approval.content_id' => $data[$this->name]['id']),
					'fields' => array('id'),
					'recursive' => -1
					));
				if(Hash::check($aprv,'Approval.id')){
					$aprv = $this->Approval->findForView($aprv['Approval']['id'],true);
					$data['Approval'] = $aprv;
				}
			}

			return $data;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();

	}

/**
 * findForView
 * view のときに使用する
 * @return array
 */
	public function setAprvLimit($id = null,$limit = 0){
		try{
			$this->recursive = -1;
			if($this->exists($id)){
				return $this->save(array($this->name =>
								array( 'id' => $id, 'approval_limit' => $limit)));
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

/**
 * getFrmRsvIds
 * reserve_id を元にデータを求める
 * @return array
 */
	public function getFrmRsvIds($id = null){
		$rtnary = array();
		try{
			if(!is_array($id)){
				return $this->find('all',array('conditions' => array('reserve_id' => $id)));
			}
			$cond = array('reserve_id' => $id);
			$data1 = $this->find('all',array(
					'conditions' => $cond
					));
			// まれにひとつの reserve_id に対して複数の Content がヒット
			// する場合があるが、そのときは最後の１つが活きる
			foreach($data1 as $k => $v){
				$rtnary[$v['Content']['reserve_id']] = $v;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $rtnary;
	}

	/**
	 * findContentIdsByKeyword
	 * @todo 検索条件パラメータkeywordによる検索条件を作成
	 * サニタイズ対象項目は、サニタイズしたkeywordで検索を行う
	 * @param    $data : str または array
	 * @return   $conditions :array()
	 */
	// たぶん使ってない？
	public function findContentIdsByKeyword($data = array()) {
		$list = array();
		try{
			$cond = $this->searchKeyword($data);
//debug($cond);
			$list = $this->find('list',array(
			'conditions' => $cond,
			'recursive' => 0));
		} catch (Exception $e){
debug(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $list;
	}

/**
 * countSendErr
 * @todo	当該ユーザに関連する情報を求める（送信失敗件数）
 * @param	int $uid : ユーザID
 * @var array 	:	'new' => 未読・期限内
 *					'old'   => 未読・期限切れ
 */
	public function countSendErr($uid = null){
		$data = array('new' => 0, 'old' =>0);
		try{
			$this->loadModel('User');
			if($this->User->exists($uid)){
				// smtp エラーになったもの
/*
				$errlist = $this->find('list',
						array(	'conditions' => array(	'Content.user_id' => $uid,
														'Content.status_code' => VALUE_Status_Error),
								'recursive' => -1
								));
*/
$errlist = array();
debug($errlist);
				$errmail = array();
				if(CakePlugin::loaded('Errmails')){
					// 不達があるもの
					$this->loadModel('Errmails.Errmail');
					$errmail = $this->Errmail->find('list',
							array(	'conditions' => array(	'Errmail.user_id' => $uid , 'Errmail.is_checked' => 'N'),
									'recursive' => -1,
									// content_id でグループ化したい
									'fields' => array('content_id','id'),
									));
debug($errmail);
				}
				// マージ
				$tmp = $errlist + $errmail;
debug($tmp);
				$data['new'] = count($tmp);
				$data['old'] = 0;
			}
//debug($data);
			// エラーがあるかどうかを設定する（未）
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $data;
	}


/*************************************************************
 * cleanup で使用する関数群
 *
*************************************************************/
/**
 * getExpirationList
 * @todo DBから期限が切れるコンテンツを取得
 * @param date $date : 日付（null のときは現在）
 * @var array : ファイル名リスト
 */
	public function getExpirationList($date = null){
		try{
$this->log('getExpirationList  start date['.$date.']');
			$expdate = $date;
			// パラメータの指定がないときは現在の日付
			if($expdate == null){
				$expdate = $this->today();
			}
			// 新たに期限が切れたもの(処理中のものは除く)
			// 送信完了、承認依頼中
			$list1 = $this->find('all',array(
					'fields' => array(
						'id','title','is_expdate','expdate','is_deleted','status_code'
					),
					'conditions' => array(
						'is_expdate' => 'N',
						'expdate <' => $expdate,
						'status_code' => array(
							VALUE_Status_Done ,
							VALUE_Status_Aprv_Waiting
						)
					),
					'recursive' => -1
				));
//debug($list1);

			// 期限前だけど削除されたもの
			// 送信エラー、変換エラー、却下
			// 削除済も拾うため、一時的にビヘイビアをオフ
			$this->Behaviors->unload('SoftDelete');
            $cond_status = array(
  										VALUE_Status_Error,
										VALUE_Status_Conv_Error,
										VALUE_Status_Aprv_Rjct,
										VALUE_Status_Aprv_RjctAuto
                           );
			$list2 = $this->find('all',array(
					'fields' => array(
						'id','title','is_expdate','expdate','is_deleted','status_code'
					),
					'conditions' => array(
							'OR' => array(
                                      array(
           //                                 'expdate <' => $expdate,
											'is_expdate' => 'N',
											'is_deleted' => true
										),
                                       array(
											'is_expdate' => 'N',
                                            'status_code' => $cond_status,
                                       ),

//									'status_code' => array(
//										VALUE_Status_Error,
//										VALUE_Status_Conv_Error,
//										VALUE_Status_Aprv_Rjct,
//										VALUE_Status_Aprv_RjctAuto
//										)
								)

						),
					'recursive' => -1
				));
			$list = array_merge($list1,$list2);
$this->log($list1);
$this->log($list2);
			return $list;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}

/**
 * setExpFlg
 * @todo コンテンツリストから期限切れフラグを立てる
 * @param array $list : 期限切れにするコンテンツリスト
 * @var array : 期限切れにしたコンテンツIDをコンソールに出力
 */
	public function setExpFlg($contents = array()){
		try{
//debug($contents);
			foreach($contents as $content){
//debug($content);
				$id =  $content[$this->name]['id'];
				if(!$this->exists($id)){
					continue;
				}
				$rtn = parent::save(array(
						'id' => $id,
						'is_expdate' => 'Y'
						));
				if($rtn){
					$this->out('content expend --- ['.$id.']');
				}
			}
			return true;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
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
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $cond;
	}


 /**
 * validEmails
 * @todo 	　アドレスの有効性チェック
 * @param    array	$field
 * @return   bool	ひとつでも有効なアドレスがあれば true
 */
   function validEmails($field,$name,$is_serialized = false){
		try{
$this->log('validEmails');
			$ok_num = 0;
			$err_num = 0;
			$workary = array();
$this->log($field);
$this->log($name);
			if($is_serialized){
$this->log('name['.$name.'] unserialize');
				$workary = unserialize($field[$name]);
			} else {
$this->log('name['.$name.'] NOT unserialize');
				$workary = $field[$name];
			}
			if(is_array($workary)){
				foreach($workary as $emails){
					$email = preg_split("/[ ;,]+/", $emails);
					foreach($email as $v){
						if(strlen(trim($v)) > 0){
							if( Validation::email($v)){
								$ok_num++;
							} else {
								$err_num++;
							}
						}
					}
				}
			}

			if($ok_num > 0){
				return true;
			}
			if($err_num == 0){
				return true;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
    }

 /**
 * validEmails
 * @todo 	　アドレスの有効性チェック
 * @param    array	$field
 * @return   bool	ひとつでも有効なアドレスがあれば true
 */
   function validAprv($field,$name){
		try{
//debug($field);
			if($field['approval_add'] > 0){
				return true;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
    }

 /**
 * count_enc_type
 * @todo 	指定　enc_type 暗号ファイルカウント
 * @param   array	$field
 * @return  int	当該ファイルカウント
 */
   function count_enc_type($content=array(),$enc_type = VALUE_Enctype_Enc){
		$count = 0;
		try{
			if(CakePlugin::loaded('Tfg')){
				$ary = Hash::combine($content,'Content.Uploadfile.{n}.Uploadfile.path','Content.Uploadfile.{n}.Uploadfile.enc_type');
$this->log($ary);
				foreach($ary as $name => $type){
					if($type == $enc_type){
						$count++;
					}
				}
			} else {
				// Tfg プラグインがないときは関係ない
				return 0;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $count;
    }

 /**
 * chk_ext 拡張子の正当性チェック
 * @todo 	$opt = 'tfg' のときは tfg チェック
　*			$opt = 'pscan' のときは pscan チェック
 * @param   array	$field
 * @return  bool	true : 全部OK / false ： NG　あり（ひとつでもあればfalse）
 */
   function chk_ext($content=array(),$opt = 'tfg'){
		$rc = true;
		try{
			if($opt == 'tfg'){
				// TFG のときは TFG 不可能な拡張子があれば　NG
				if(CakePlugin::loaded('Tfg')){
					$ary = Hash::combine($content,'Content.Uploadfile.{n}.Uploadfile.path','Content.Uploadfile.{n}.Uploadfile.name');
					$this->loadModel('Tfg.Tfg');
					foreach($ary as $path => $name){
						if($this->Tfg->chk_ext($name)){
						} else {
							$rc = false;
						}
					}
				}
			} elseif ($opt == 'pscan'){
				// PSCAN　のときは　PSCAN　可能でなければ　NG
				if(CakePlugin::loaded('Pscan')){
					$ary = Hash::combine($content,'Content.Uploadfile.{n}.Uploadfile.path','Content.Uploadfile.{n}.Uploadfile.enc_type');
					$this->loadModel('Pscan.Pscan');
					foreach($ary as $path => $name){
						if($this->Pscan->chk_ext($name)){
$this->log('---['.$name.'] OK');
						} else {
$this->log('---['.$name.'] NG');
							$rc = false;
						}
					}
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $rc;
    }

	/**
	 * addZip 更新
	 * @param $uploads
	 */
	function addZip($cid,$decary,$encary = array()){
		try{

$this->log(__METHOD__."[".__LINE__."] start [$cid]",LOG_DEBUG);

//			if(is_null($this->Content)){
//$this->log(__METHOD__."[".__LINE__."] ClassRegistry::init Content",LOG_DEBUG);
//				$this->Content = ClassRegistry::init('Content');
//			}
			$this->recursive = 1;

			// 改めて読む
			$_content = $this->findById($cid);
//debug($_content);
//debug($ary);
//$this->log($_content,LOG_DEBUG);
			if(is_null($this->Uploadfile)){
$this->log(__METHOD__."[".__LINE__."] loadModel Uploadfile",LOG_DEBUG);
				$this->Uploadfile = $this->loadModel('Uploadfile');
			}

			foreach ($_content['Uploadfile'] as $k => $v){
				// ダウンロード不可
$this->log('Uploadfile dl_mod ['.$k.']',LOG_DEBUG);
				$_dl_mod = VALUE_dl_mod_NG;
				$this->Uploadfile->set_dl_mod($v['id'],$_dl_mod);
			}
			// 追加用のデータ
			$_add = $this->Uploadfile->Create();
			$tmpname = $this->mkrandamstring(6);
			$_add['Uploadfile']['content_id'] = $cid;
			$_add['Uploadfile']['name'] = 'Arc'.$tmpname.'.zip';		// 今はきめうち
			$_add['Uploadfile']['path'] = $decary['path'];
			$_add['Uploadfile']['mime_type'] = 'application/x-zip-compressed';
//			$_add['Uploadfile']['size'] = filesize(Configure::read('Upfile.dir') .'/'.$ary['name']);
			$_add['Uploadfile']['size'] =  $decary['size'];
			$_add['Uploadfile']['created'] = null;
			$_add['Uploadfile']['modified'] = null;
			$_add['Uploadfile']['deleted'] = null;
			$_add['Uploadfile']['is_deleted'] = false;
			$_add['Uploadfile']['dl_mod'] = 0;
			$_add['Uploadfile']['dl_cnt'] = 0;
			$_add['Uploadfile']['avs_result'] = defined('VALUE_AntiVirus_NotScan') ? VALUE_AntiVirus_NotScan : -1 ;
			$_add['Uploadfile']['error'] = 0;
			$_add['Uploadfile']['zpass'] = $decary['pwd'];
			$_add['Uploadfile']['enc_type'] = 'dec';
			$_add['Uploadfile']['dec_path'] = $decary['path'];
			$_add['Uploadfile']['dec_size'] = $decary['size'];

			if(!empty($encary)){
				$_add['Uploadfile']['enc_path'] = $encary['path'];
				$_add['Uploadfile']['enc_size'] = $encary['size'];
			}

			$_content['Uploadfile'][] = $_add['Uploadfile'];
//			$result = $this->saveAll($_content);
			$result = $this->Uploadfile->save($_add);
$this->log(__METHOD__."[".__LINE__."] saveAll",LOG_DEBUG);
			return $result;

		} catch (Exception $e){
$this->log(__METHOD__."[".__LINE__."] try err[".$e."]",LOG_DEBUG);
		}
		return false;
	}

	/**
	 * sendFromQueue
	 * @param $uploads
	 */
	function sendFromQueue($qid){
		try{
$this->log('sendFromQueue start qid['.$qid.']');
			$this->loadModel('Queue');
			$qdata = $this->Queue->findById($qid);
//$this->log($qdata);
			$q_stat = Hash::get($qdata,'Queue.status_code');
$this->log('q_stat['.$q_stat.']');

			$cid = Hash::get($qdata,'Queue.content_id');
			$rid = Hash::get($qdata,'Content.reserve_id');
			$this->loadModel('Reserve');
			$rdata = $this->Reserve->findById($rid);
//debug($rdata);
			$_rsv_data = Hash::get($rdata,'Reserve.rsv_data');

			// 承認が必要なら承認を求める
			if(Hash::check($_rsv_data,'Approval')){
				// 承認あり
				$aprv = Hash::get($rdata,'Reserve.rsv_data.Approval');
				$aprv['id'] = '';
				$aprv['content_id'] = $cid;
				$this->loadModel('Approval');
				$aprv['sno'] = $this->Approval->makeSno($rdata,$cid);
				$aprv_lastid = $this->Approval->save($aprv);
				if($aprv_lastid){
$this->log('--- 承認依頼成功 (ContentModel)',LOG_DEBUG);
					$logid = $this->writeLog(
						array(
							'type' => 'Content',
							'content_id' => $cid,
							'user_id' => Hash::get($qdata,'Content.user_id'),
							'event_action' => '送信',
							'remark' => '承認依頼',
							'result' => '成功',
						));
					// ステータス：承認待ち
					$this->setStatus($cid,VALUE_Status_Aprv_Waiting);
					// 依頼メール送信
					$this->loadModel('Mailqueue');
					$rc = $this->Mailqueue->putQueue('aprv_apply',$cid, $logid);
				} else {
$this->log('--- 承認依頼失敗',LOG_DEBUG);
					$logid = $this->writeLog(
						array(
							'type' => 'Content',
							'content_id' => $cid,
							'event_action' => '送信',
							'remark' => '承認依頼',
							'result' => '失敗',
						));
						$this->setStatus($cid,VALUE_Status_Error);
						return false;
				}
			} else {
				// 承認なし
$this->log('NOT have Approval['.$qid.']');
				// zip をするか調べる（たぶんここに来るときはTFG のときだけ
				if(Hash::get($_rsv_data,'Content.opt_encryption') == 'on'){
					// プラグインがあれば実施
					if(CakePlugin::loaded('Encrypt')){
						$this->loadModel('Encrypt.Encrypt');
					// デバッグ中はパスワードきめ打ち
						$_zpwd = $this->mkrandamstring(VALUE_Encryption_Pwd_Length,false);
						// ↓　第３パラメータは承認の有無（ここでは承認なししか来ないはず）
						$rtn = $this->Encrypt->mkzip2($cid,$_zpwd);
//$this->log($rtn,LOG_DEBUG);
						if(!$rtn){
							$this->setStatus($cid,VALUE_Status_Error);
							return false;
						}
					}
				}
				//　------------------ zip 終わり
				// 通常送信（承認なし）
				$this->setAprvLimit($cid,0);
				$logid = $this->writeLog(
					array(
						'type' => 'Content',
						'content_id' => $cid,
						'event_action' => '送信',
						'remark' => 'Shell',
						'result' => '成功',
					));
				// ステータス：送信待ち
				$this->setStatus($cid,VALUE_Status_Waiting);
				// メール送信リクエスト
				// 予約したときは予約ができた時点の結果
				// 即時送信のときはメール送信の結果
				// 一度に複数のメールを送るため個々のメール送信エラーは取れない
$this->log('ログID['.$logid.']　メール送信リクエスト',LOG_DEBUG);
				$this->loadModel('Mailqueue');
				$rc = $this->Mailqueue->putQueue('upload',$cid, $logid);
$this->log('メール送信['.$rc.']',LOG_DEBUG);
				if($rc){
$this->log('メール送信成功['.$rc.']',LOG_DEBUG);
//					$this->Session->setFlash(__('The content was sent.'),'Flash/success');
				} else {
				// SMTP エラーのとき
$this->log('メール送信失敗['.$rc.']',LOG_DEBUG);
					$logid = $this->writeLog(
						array(
							'id' => $logid,
							'type' => 'Content',
							'content_id' => $cid,
							'event_action' => '送信',
							'remark' => 'Shell',
							'result' => '失敗',
							'event_data' => 'メール送信に失敗しました（SMTP エラー）',
						));
//						$this->Session->setFlash(__('There are addresses which could not be sent.'),'Flash/error');
				}
			}

			return true;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

/**
 * is_valid
 * 当該データにアクセス権限があるかチェック
 */
	public function is_valid($id = null){
		try{
$this->log('content is_valid['.$id.']');
			// データの整合性チェックのため
			$auth = CakeSession::read('auth');
//debug($auth);
			$_my_id = $auth['id'];
			$this->loadModel('Role');
			$_is_super = $this->Role->isSuper($auth['group_id']);

			// 基本は送信者のみ
			// データの整合性チェックを追加　（スーパーは除外）
			$cond = array($this->name.'.id' => $id, 'Content.user_id' => $_my_id);

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
	 * setExpdate
	 * @todo 有効期限を設定
	 * 承認から　ｎ　日後の指定に従って設定し直す
	 * @param    $id : int : content_id
	 * @return   bool
	 */
	public function setExpdate($id = null) {
		try{
			if($this->exists($id)){
                $cdata = $this->find('first',array(
                        'conditions' => array('Content.id' => $id),
                        'recursive' => -1
                        ));
                $limit = $cdata['Content']['time_limit'];
                $cdata['Content']['expdate'] = date('Y-m-d', strtotime('+'.$limit.' day'));
//$this->log($cdata);
                return $this->save($cdata);
            }
		} catch (Exception $e){
debug(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 * deleteMyAddress
	 * @todo 送信宛先から自分のアドレスを抜く
	 * @param    array $content 処理前データ
	 * @return   array　処理後データ
	 */
	public function deleteMyAddress($content = array()) {
		try{
 			$auth = CakeSession::read('auth');
            $myaddr = strtolower($auth['email']);
            if(is_array($content)){
                if(Hash::check($content,'Content.add_list')){
                    $_new_add_list = array();
                    foreach($content['Content']['add_list'] as $k => $v){
                        $_addr = strtolower($v['Addressbook']['email']);
                        if($myaddr == $_addr) continue;
                        $_new_add_list[] = $v;
                    }
                    $content['Content']['add_list'] = $_new_add_list;
                 }
            }
            // free_address はとりあえず除外（将来必要ならここで削除すればいいかな）
		} catch (Exception $e){
debug(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $content;
	}

	/**
	 * deleteInvalidAddress
	 * @todo 送信宛先から無効なアドレスを抜く
	 * @param    array $content 処理前データ
	 * @return   array　処理後データ
	 */
	public function deleteInvalidAddress($content = array()) {
		try{
            if(!is_array($content)){
                return $content;
            }
            if(Hash::check($content,'Content.add_list')){
                $this->loadModel('User');
//debug($content);
                // 削除されたユーザ一覧
                $delusers = $this->User->find('list',array(
                        'conditions' => array('is_deleted' => true),
                        'fields' => array('id','email'),
                    ));
                $delusers[0] = 'dmy'; // id = 0 も除外
                // 情報があれば
                    // メールアドレスは全部小文字に
                $delusers = array_change_key_case($delusers,CASE_LOWER);

                $_new_add_list = array();
                foreach($content['Content']['add_list'] as $k => $v){
                    $_uid = strtolower($v['Addressbook']['user_id']);
                    $_addr = strtolower($v['Addressbook']['email']);
                    $_flg = true;
                        foreach($delusers as $id => $email){

                            if($id == $_uid){
                                // id がマッチ
                                $_flg = false;  break;
                            }
                            if($email == $_addr){
                                // アドレスがマッチ
                                $_flg = false;  break;
                            }
                        }
                     if($_flg){
                        // マッチしないものだけ戻す
                        $_new_add_list[] = $v;
                    }
                }
                $content['Content']['add_list'] = $_new_add_list;
            }
		} catch (Exception $e){
debug(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $content;
	}


/**
 * codeToMessage description
 * @param  integer $error [アップロードエラーコード]
 * @return text         [エラーメッセージ]
 */
	public function codeToMessage($error = 0){
        $txt = '';
        try{
            switch ($error){
                case UPLOAD_ERR_OK:
                case UPLOAD_ERR_NO_FILE:
                // OK
                break;

                case UPLOAD_ERR_INI_SIZE:
                // 値: 1; アップロードされたファイルは、php.ini の upload_max_filesize ディレクティブの値を超えています。
                $txt = __('UPLOAD_ERR_INI_SIZE');
                break;

                case UPLOAD_ERR_FORM_SIZE:
                // 値: 2; アップロードされたファイルは、HTML フォームで指定された MAX_FILE_SIZE を超えています。
                $txt = __('UPLOAD_ERR_FORM_SIZE');
                break;

                case UPLOAD_ERR_PARTIAL:
                // 値: 3; アップロードされたファイルは一部のみしかアップロードされていません。
                $txt = __('UPLOAD_ERR_PARTIAL');
                break;

                case UPLOAD_ERR_NO_TMP_DIR:
                // 値: 6; テンポラリフォルダがありません。PHP 5.0.3 で導入されました。
                $txt = __('UPLOAD_ERR_NO_TMP_DIR');
                break;

                case UPLOAD_ERR_CANT_WRITE:
                // 値: 7; ディスクへの書き込みに失敗しました。PHP 5.1.0 で導入されました。
                $txt = __('UPLOAD_ERR_CANT_WRITE');
                break;

                case UPLOAD_ERR_EXTENSION:
                // 値: 8; PHP の拡張モジュールがファイルのアップロードを中止しました。
                $txt = __('UPLOAD_ERR_EXTENSION');
                break;

                default:
                // 上記以外; 知られていない何らかのエラー。
                $txt = __('UPLOAD_ERR_UNKNOWN');
                break;
            }
            return $txt;
        } catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true),LOG_DEBUG);
            if(empty($txt)){
                $txt = __('UPLOAD_ERR_UNKNOWN');
            }
        }
        return $txt;
    }
//
//// アップロード完了時の時刻と速度を取得しログへ書き出す
//
	function WriteUpload ($val,$com){
						$TSize = 0;
						$upload_time = microtime(TRUE) - $_SERVER['REQUEST_TIME_FLOAT'];
						if(Hash::check($val,'Content.Uploadfile')){
							foreach ($val['Content']['Uploadfile'] as $val1){
								$TSize += $val1['size'];
							}
						}
						// 単位が byte なので、size / time の値を
						// MByte に直す
						$_b = round($TSize / $upload_time,2);
						$MB = round($TSize / $upload_time / 1024 / 1024, 2);
//$this->log('計算['.$_b.'] MB['.$MB.']');
						// 小数点以下の数値は、リテラルでくくらないと
						// DB　に入れたとき数値フォーマットが崩れる
						$event_data = array(
							'画面名' => $com,
							'時間（秒）'   => "'".round($upload_time,2)."'",
							'サイズ(Byte)'	=> $TSize,
							'速度（MB/s）'	=> "'$MB'",
							'速度（B/s）'	=> "'$_b'",
							);
						$logid = $this->writeLog(
										array('type' => 'Time',
												'event_action' => '計測',
												'result' => '成功',
                                                'remark' => "'".round($upload_time,2)."秒'",
												'event_data' => $event_data
//												'event_data' => $com.'時間:'.round($upload_time,2).'秒'.'サイズ:'.$TSize.'byte'.'速度:'.$MB.'MB/s'
										));
	}

}
