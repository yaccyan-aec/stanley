<?php
App::uses('AppModel', 'Model');
App::uses('MySecurity', 'Model');
App::uses('Role', 'Model');
/**
 * User Model
 *
 * @property UserExtension $UserExtension
 * @property Group $Group
 * @property Contract $Contract
 */
class User extends AppModel {
/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';

	public $order = array("User.modified" => "desc");

	// 削除フラグ（tinyint) => 削除日(datetime) のフィールド名カスタマイズ
	// デフォルトは 'deleted' => 'deleted_date'
// ここを有効にするとなぜか unitTest が通らない↓
	var $actsAs = array(
		'SoftDelete' => array('is_deleted' => 'deleted'),
		'Containable',
		'Search.Searchable');

/**
 * Searchプラグイン設定
 */
	var $filterArgs = array(
		'keyword' => array('type' => 'query', 'method' => 'searchKeyword'),
	);

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
				$searchFields = array(	'User.email',			// email アドレス（ログインID）
										'User.name',			// 名前
										'User.name_yomi',		// 名前ふりがな
										'User.division',		// 所属
										'User.div_yomi',		// 所属ふりがな
										'UserExtension.name_jpn',	// 名前日本語
										'UserExtension.name_eng',	// 名前英語
										'UserExtension.div_jpn',	// 所属日本語
										'UserExtension.div_eng',	// 所属英語
										'UserExtension.custom_01',	// 追加項目１
										'UserExtension.custom_02',	// 追加項目２
										'UserExtension.text_01'		// 追加テキスト１
									);
				$conditions['OR'] = $this->mkCondition($searchFields,$keyword);
			}
				//	サニタイズが必要なフィールドには、サニタイズしたキーワードをセット
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
//debug($conditions);
		return $conditions;
	}

	/**
	 * findUserListByKeyword
	 * @todo 検索条件パラメータkeywordによる検索条件を作成
	 * サニタイズ対象項目は、サニタイズしたkeywordで検索を行う
	 * @param    $data : str または array
	 * @return   $conditions :array()
	 */
	public function findUserListByKeyword($data = array()) {
		$list = array();
		try{
			$cond = $this->searchKeyword($data);
//debug($cond);
			$list = $this->find('list',array('conditions' => $cond,'recursive' => 0));
		} catch (Exception $e){
debug(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $list;
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
								'name' => array('html' => true, ),
								'name_yomi' => array('html' => true, ),
								'division' => array('html' => true, ),
								'div_yomi' => array('html' => true, ),
								'remark' => array('html' => false, ),
								'pwd_work' => array('html' => false ,'serialize' => true),
								'etc' => array('html' => false ,'serialize' => true),
								);

/**
 * __construct
 * @todo 画像認証のチェックのため、関連テーブルを追加
 * @param    $id    : テストケースでDB切り替えに使用するため
 * @param    $table :　これらのパラメータは
 * @param    $ds    :　親クラスに渡しておく
 * @return   void
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id , $table , $ds );
		$this->MySecurity = ClassRegistry::init('MySecurity');
		$this->Role = ClassRegistry::init('Role');
	}


/**
 * Validation rules
 *
 * @var array
 */


	public $validate = array(

	);
	var $validationSets = array(
		'default' => array(
			'name' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'required' => true,
					'last' => false,
					'message' => 'The blank is not goodness.'
				),
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					'last' => true,
					'message' => array('%s characters too long.','Name')
				),
			),
			'name_yomi' => array(
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					'message' => array('%s characters too long.','Name_yomi')
				),
			),
			'division' => array(
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					'message' => array('%s characters too long.','Division')
				),
			),
			'div_yomi' => array(
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					'message' => array('%s characters too long.','Div_yomi')
				),
			),
			'email' => array(
				'email' => array(
					'rule' => array('email'),
					'required' => true,
					'last' => true,
					'message' => array('Must be a valid %s .','Email'),
				),
				'isUnique' => array(
					'rule' => 'isUnique',
					'last' => true,
					'message' => array('This %s is duplicated.','Email'),
				),
			),
			'contract_id' => array(
				'numeric' => array(
					'allowEmpty' => true,
					'rule' => array('numeric'),
					'last' => false,
					'message' => 'This item should be a number.',
				),
				// 値が正当であるか
				'isValidContractId' => array(
					'rule' => 'isValidContractId',
					'last' => true,
					'message' => "Please choose contract.",
				),
				// 人数に余裕があるか
				'hasSpace' => array(
					'rule' => 'hasSpace',
					'last' => true,
					'message' => "It can't be registered any more.",
				),
			),
			'expdate' => array(
				'date' => array(
					'rule' => array('date'),
					'allowEmpty' => true,
					'last' => true,
                    'on' => 'create',
					'message' => 'Please enter in the valid date format.',
				),
//  とりあえず形式だけチェック
//				'isValidExp' => array(
//					'rule' => array('isValidExp'),
//					'required' => true,
//					'last' => true,
//					'message' => 'Can\'t be set beyond the period of a contract.',
//				),
			),
		),
		'admin' => array(
		),
		// 個人によるパスワード変更は、現パスワードを確認する
		'MyPassword' => array(
			'pwd' => array(
				// 現在のパスワードがあっているかどうか
				'match' => array('rule' => array('match'),
					'last' => false,
					'message' => 'Current password do not match, please try again.'
				),
				// 文字列の長さが短すぎないか
//				'minLength' => 	array('rule' => array('minLength', MANUAL_PWD_MIN),
//					'last' => false,
//					'message' => array('Password must be at least %d characters long, please try again.',MANUAL_PWD_MIN),
//				),
			),
			'new_password' => array(
				// 文字列の長さが短すぎないか
				'minLength' => 	array('rule' => array('minLength', MANUAL_PWD_MIN),
					'last' => true,
					'message' => array('Password must be at least %d characters long, please try again.',MANUAL_PWD_MIN),
				),
				// 文字列の長さが長すぎないか
				'maxLength' => Array(
					'last' => true,
					'rule' => Array('maxLength', MANUAL_PWD_MAX),
					'message' => array('Password must %d character or less, please try again.',MANUAL_PWD_MAX),
				),
				// ルールに合っているか
				'new_pwd_on_rule' => array('rule' => array('new_pwd_on_rule'),
					'last' => true,
					'message' => "New password is doesn't match a rule."
				),
				// 現在のパスワードと一緒ではないか
				'new_pwd_not_same' => array('rule' => array('new_pwd_not_same'),
					'last' => true,
					'message' => 'The same password as the present cannot be specified.'
				),
				// 履歴に入っていないか
				'new_pwd_history_chk' => array('rule' => array('new_pwd_history_chk'),
					'last' => true,
					'message' => 'New password is in a password history.'
				),
			),
			'new_password_confirm' => array(
				// 文字列の長さが短すぎないか
				//'minLength' => 	array('rule' => array('minLength', MANUAL_PWD_MIN),
				//	'last' => true,
				//	'message' => array('Password must be at least %d characters long, please try again.',MANUAL_PWD_MIN),
				//),
				// 新しいパスワードが２回とも一致するか
				'new_pwd_comfirm' => array('rule' => array('new_pwd_verify'),
					'last' => true,
					'message' => 'New Passwords do not match, please try again.'
				),
			),
		),
		// 管理者によるパスワード変更は、現パスワードを確認しない
		'ChangePwd' => array(
			'new_password' => array(
				// 文字列の長さが短すぎないか
				'minLength' => 	array('rule' => array('minLength', MANUAL_PWD_MIN),
					'last' => true,
					'message' => array('Password must be at least %d characters long, please try again.',MANUAL_PWD_MIN),
				),
				// 文字列の長さが長すぎないか
				'maxLength' => Array(
					'last' => true,
					'rule' => Array('maxLength', MANUAL_PWD_MAX),
					'message' => array('Password must %d character or less, please try again.',MANUAL_PWD_MAX),
				),
				// ルールに合っているか
				'new_pwd_on_rule' => array('rule' => array('new_pwd_on_rule'),
					'last' => true,
					'message' => "New password is doesn't match a rule."
				),
				// 履歴に入っていないか
				'new_pwd_history_chk' => array('rule' => array('new_pwd_history_chk'),
					'last' => true,
					'message' => 'New password is in a password history.'
				),
			),
			'new_password_confirm' => array(
				// 文字列の長さが短すぎないか
				//'minLength' => 	array('rule' => array('minLength', MANUAL_PWD_MIN),
				//	'last' => true,
				//	'message' => array('Password must be at least %d characters long, please try again.',MANUAL_PWD_MIN),
				//),
				// 新しいパスワードが２回とも一致するか
				'new_pwd_comfirm' => array('rule' => array('new_pwd_verify'),
					'last' => true,
					'message' => 'New Passwords do not match, please try again.'
				),
			),
		),



		'update_data' => array(
			'name' => array('rule' => 'notBlank',
							'required' => true,
							'message' => 'The blank is not goodness.'),
			'lang' => array('rule' => array('inList',array('jpn','eng')),
							'message' => 'Please specify language.',
							),
		),

		'set_lang' => array(
			// ここに書くときは、フォームのくずれに注意
//			'lang' => array('rule' => array( 'lang_check'),
////							'last' => true,
////							'message' => false,
////							'label' => false,
//							),
//			'lang' => array('rule' => array('inList',array('jpn'))
//							),
		),

		// パスワード再発行（仮パスワード発行）
		'ResetPwd' => array(
			'email' => array(
				'notBlank' => array(
					'rule' => 'notBlank',
					'required' => true,
					'last' => true,
					'message' => 'The blank is not goodness.',
				),
				'email' => array(
					'rule' => array('email'),
					'required' => true,
					'last' => true,
					'message' => array('Must be a valid %s .','Email'),
				),
				// 登録されているか
				'email_exists' => array(
					'rule' => array('email_exists'),
					'last' => true,
					'message' => 'Valid ID.',
				),
			),
		),

		// ロックアウト中かのチェック
		'CheckLock' => array(
			'email' => array(
				// ロックアウトされていないか
				'lockout_stat' => array(
					// 最後の　null は省略したらダメ
					'rule' => 'isValidLockoutNow',
					'last' => true,
					'message' => 'Log in ID is locked.',
				),
			),
		),


		// パスワード再発行（本パスワード発行）
		'MakePass' => array(

			'email' => array(
				'notBlank' => array(
					'rule' => 'notBlank',
//					'required' => true,
//					'last' => true,
					'message' => 'The blank is not goodness.',
				),
				'email' => array(
					'rule' => array('email'),
					'required' => true,
//					'last' => true,
					'message' => array('Must be a valid %s .','Email'),
				),
			),
			'pwd' => array(
				// 現在のパスワードがあっているかどうか
				'match_tmppwd' => array(
					'rule' => array('match_tmppwd'),
					'last' => true,
					'message' => 'Valid ID or temp password.',
				),
			),
		),

		// 期限延長申請
		'ExpRenew' => array(

			'email' => array(
				'notBlank' => array(
					'rule' => 'notBlank',
					'required' => true,
					'last' => true,
					'message' => 'The blank is not goodness.',
				),
				'email' => array(
					'rule' => array('email'),
					'required' => true,
					'last' => true,
					'message' => array('Must be a valid %s .','Email'),
				),
				'isMailExist' => array(
					'rule' => array('isMailExist'),
					'required' => true,
					'last' => true,
					'message' => 'You can not apply.'
				),
				'isDoneExpApply' => array(
					'rule' => array('isDoneExpApply'),
					'required' => true,
					'last' => true,
					'message' => 'Already done.',
				),
				'canExpApply' => array(
					'rule' => array('canExpApply'),
					'required' => true,
					'last' => true,
					'message' => 'Can not apply.',
				),
			),
		),
		// 期限延長
		'ExpUpdate' => array(
			'expdate' => array(
				'date' => array(
					'rule' => array('date'),
					'allowEmpty' => true,
					'last' => true,
					'message' => 'Please enter in the valid date format.',
				),
				'isValidExp' => array(
					'rule' => array('isValidExp'),
					'required' => true,
					'last' => true,
					'message' => 'Can\'t be set beyond the period of a contract.',
				),
			),
		),
		// パスワード期限延長
		'PwdExpUpdate' => array(
			'pwd_expdate' => array(
				'date' => array(
					'rule' => array('date'),
					'allowEmpty' => true,
					'last' => true,
					'message' => 'Please enter in the valid date format.',
				),
/*	管理者によるパスワード有効期限の変更はある程度自由に
				'isValidPwdExp' => array(
					'rule' => array('isValidPwdExp'),
					'required' => true,
					'last' => true,
					'message' => 'Valid end date.',
				),
*/
			),
		),
		// パスワード期限延長
		'setPwd' => array(
			'name' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'required' => false,
					'allowEmpty' => true,
					'last' => true,
					'message' => 'The blank is not goodness.',
				),
			),
			'email' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'required' => false,
					'allowEmpty' => true,
					'last' => true,
					'message' => 'The blank is not goodness.',
				),
			),
			'id' => array(
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
			'pwd' => array(
				'notBlank' => array(
					'rule' => 'notBlank',
					'required' => true,
					'last' => true,
					'message' => 'The blank is not goodness.',
				),
			)
		),
		//ユーザー情報のインポート
		'import' => array(
			'name' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'required' => true,
					'message' => 'The blank is not goodness.'
				),
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					'message' => array('Data length exceeds %s .',MAX_STRLEN)
				),
			),
			'name_yomi' => array(
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					'message' => array('Data length exceeds %s .',MAX_STRLEN)
				),
			),
			'division' => array(
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					'message' => array('Data length exceeds %s .',MAX_STRLEN)
				),
			),
			'div_yomi' => array(
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					'message' => array('Data length exceeds %s .',MAX_STRLEN)
				),
			),
			'email' => array(
				'email' => array(
					'rule' => array('email'),
					'required' => true,
					'message' => array('EmailAddress Format is invalid.'),
				),
				'isUnique' => array(
					'rule' => 'isUnique',
					'message' => array('This Email is duplicated.'),
				),
			),
			'pwd' => array(
				// 文字列の長さが短すぎないか
				'minLength' => 	array(
					'rule' => array('minLength', MANUAL_PWD_MIN),
					'message' => array('Password length violates regulation.'),
				),
				// 文字列の長さが長すぎないか
				'maxLength' => Array(
					'rule' => Array('maxLength', MANUAL_PWD_MAX),
					'message' => array('Password length violates regulation.'),
				),
				// ルールに合っているか
				'import_pwd_on_rule' => array('rule' => array('import_pwd_on_rule'),
					'last' => false,
					'message' => array('Password is not alphanumeric.'),
				),
			),
			'expdate' => array(
				'isValidExp2' => array(
					'rule' => array('isValidExp2'),
					'required' => true,
					'last' => true,
					'message' => 'Can\'t be set beyond the period of a contract.',
				),
				'date' => array(
					'rule' => array('date'),
					'allowEmpty' => true,
					'last' => false,
					'message' => 'Date format is invalid.',
				),

			),
		)
	);


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasOne associations
 *
 * @var array
 */
	public $hasOne = array(
		'UserExtension' => array(
			'className' => 'UserExtension',
			'foreignKey' => 'id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Group' => array(
			'className' => 'Group',
			'foreignKey' => 'group_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Contract' => array(
			'className' => 'Contract',
			'foreignKey' => 'contract_id',
			'conditions' => '',
			'fields' => 'Contract.*',
			'order' => '',
			'counterCache' => true,
// softDeleteBehavior 使用に伴いおそらく↓のカスタマイズは不要になったと思われる
//			'counterCache' => array('user_count' =>
//								array('User.is_deleted' => false
//								)),

		),
	);

/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
 /* ここに書くとプラグインがないときエラーになるので動的にアソシエーション
 とりあえず beforeSave と afterFind に入れてみましたが
 問題があれば調整してください。2016.4.18

	public $hasAndBelongsToMany = array(
		'Section' => array(
			'className' => 'Sections.Section',
			'joinTable' => 'sections_users',
			'foreignKey' => 'user_id',
			'associationForeignKey' => 'section_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
		)
	);
*/
/*	public $hasAndBelongsToMany = array(
		'Section' => array(
			'className' => 'Sections.Section',
			'joinTable' => 'sections_users',
			'foreignKey' => 'section_id',
			'associationForeignKey' => 'user_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
		)
	);
*/
/*************************************************************
 * 独自バリデーション
 */

	// マッチするかどうかのチェック
    function email_exists($field){
		$found = null;
$this->log($field);
		foreach( $field as $key => $value ){
    		$this->recursive = -1;
    		$found = $this->find('first',
    						array( 'conditions' =>
    							array( 	"{$this->name}.email" => $value,
    						)));
    	}
$this->log($found);
        return $found;
    }

	// マッチするかどうかのチェック
    function match($field){
		$found = null;
		foreach( $field as $key => $value ){
    		$this->recursive = -1;
    		$hash = md5($value);
    		$found = $this->find('first',
    						array( 'conditions' =>
    							array( 	"{$this->name}.id" => $this->data[$this->name]['id'],
    									"{$this->name}.pwd" => $hash,
//    									"{$this->name}.is_deleted" => 'N'
    						)));
    	}
        return $found;
    }

	// new password が現在のものと等しくないことをチェック
    function new_pwd_not_same(){
		if(Hash::check($this->data,$this->name.'.pwd') && Hash::check($this->data,$this->name.'.new_password')){
			if($this->data[$this->name]['pwd'] === $this->data[$this->name]['new_password']){
	$this->log('verify NG');
				return false;
			}
	$this->log('verify OK');
			return true;
			}
		return false;
	}

    function new_pwd_history_chk(){
$this->log(__FILE__ .':'. __LINE__ .': new_pwd_history_chk start!!');
		$this->recursive = -1;
		$udata = null;
		// password のフィールドがないときはユーザ管理からなので id だけで読む
		if(isset($this->data[$this->name]['pwd'])){
			$udata = $this->findByIdAndPwd($this->data[$this->name]['id'],
											md5($this->data[$this->name]['pwd']));
		} else {
			$udata = $this->findById($this->data[$this->name]['id']);
		}
		if(!empty($udata)){
			$newpwd = $this->data[$this->name]['new_password'];
//$this->log($newpwd);
			$h_limit = $this->MySecurity->get_password_item('history_limit');
			$new_pwdwk = null;
			if($h_limit > 0){
				// 過去の履歴をとっているときは、重ならないチェックも入れる
				$pwd_history = $udata[$this->name]['pwd_work'];
//$this->log($pwd_history);
				// 条件が整うまでループ
				$_rc = $this->cmp_pwdwk(md5($newpwd),$pwd_history);

				if($_rc == 0){
$this->log('History ok');
					return true;
				}
			} else {
$this->log('History No check');
				return true;
			}
		}
$this->log('History NG');
        return false;
	}


	// new password が等しいかチェック
    function new_pwd_verify(){
		if($this->data[$this->name]['new_password'] === $this->data[$this->name]['new_password_confirm']){
			return true;
		}
        return false;
    }

	// ルールに合っているかチェック
    function new_pwd_on_rule($data){
		if($this->security_chk($data['new_password'])){
			return true;
		}
        return false;
    }
	// ルールに合っているかチェック
    function import_pwd_on_rule($data){
		if($this->security_chk($data['pwd'])){
			return true;
		}
        return false;
    }
	// lang_check が現在のものと等しくないことをチェック
	// validate に入れるとエラーのときに表示が崩れるので
    function lang_check($data){
		try{
$this->log('lang_check START');
//$this->log($data);
			if(isset($data[$this->name]['lang'])){
				switch($data[$this->name]['lang']){
					// 対応言語がふえたらここを追加
					case 'jpn':
					case 'eng':
$this->log('lang_check OK');
						return true;
					default:
						break;
				}
			}

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
$this->log('lang_check NG');
		return false;
	}

	// temp password が等しいかチェック
    function match_tmppwd(){
		try{
			$this->loadModel('Tmppassword');
			$found = $this->Tmppassword->find('first',array(
					'conditions' => array(
						'email' => $this->data[$this->name]['email'],
						'pwd' => md5($this->data[$this->name]['pwd']),
						'expdate >=' => $this->now(),
						)));
			if($found){
				return true;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
        return false;
    }

	// 期限延長申請ができるかどうかのチェック
	// 有効なユーザが存在し、当該フラグが0でないこと
    function canExpApply($field){
		try{
			// 存在チェック
			$found = $this->find('first',array(
				'contain' => array('UserExtension'),
				'conditions' => array( $field )));
			if($found){
				// 0　でないこと

				$this->loadModel('Role');
				$_aprv_request = $this->Role->chkRole($found['User']['group_id'], array('controller' => 'users', 'action' => 'exprenew_apply'));
				// 期限延長申請が許可された権限であること
				if($_aprv_request){
					$remaindays = $this->get_remain_days($found['User']);
					// 継続のご案内が出ていること
					if($remaindays >= 0){
						if($found['UserExtension']['expdate_apply_flg'] != VALUE_Flg_Apply){
							return true;
						}
					}
				}
			}

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
    }
	// 登録されているアドレスかどうかのチェック
	// true: 登録されている　/　false: 登録されていない
    function isMailExist($field){
		try{
$this->log('isMailExist start');

			$found = $this->find('first',array(
				'recursive' => 0,
				'conditions' => array( $field )));

			if(!empty($found)){
				// 登録ずみであること
                return true;
            }
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
    }

	// 期限延長申請が終わっているかどうかのチェック
	// true: まだ　/　false: 終わっている
    function isDoneExpApply($field){
		try{
$this->log('isDoneExpApply start');

			$found = $this->find('first',array(
				'recursive' => 0,
				'conditions' => array( $field )));

			if($found){
				// 1 （期限切れ＆未申請）　であること
				if($found['UserExtension']['expdate_apply_flg'] != VALUE_Flg_Apply){
                return true;


				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
    }

	// 現在ロックされているIDかどうかのチェック
	// 当該ユーザが見つからないときは、とりあえず通す
	// true: まだ　/　false: 終わっている
    function isValidLockoutNow($field){
		try{
$this->log('isValidLockoutNow start',LOG_DEBUG);
//$this->log($field ,LOG_DEBUG);
			$found = $this->find('first',array(
				'conditions' => array('email' => $field['email']),
				'recursive' => -1
				));
			if(!empty($found)){
				$this->loadModel('MySecurity');
				$my_sec = $this->MySecurity->setLockoutInit();
				$rtn = $this->isLockoutUser($found['User'],$my_sec);
				if($rtn){
					// ロック中
					return false;
				} else {
					// ロック中ではない
					return true;
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return true;
    }

	// 有効期限の値の正当性チェック
	// true: 正しい　/　false: ダメ
    function isValidExp($field){
		try{
			$user_exp = Hash::get($this->data,'User.expdate');
			$contract_exp = Hash::get($this->data,'Contract.expdate');

			$rc = $this->cmpdate($contract_exp,$user_exp);
			if($rc >= 0){
//debug('ok');
				return true;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
    }

	// 有効期限の値の正当性チェック2(契約をDBから読み込んでくる)
	// true: 正しい　/　false: ダメ
    function isValidExp2($field){
		try{
			$user_exp = Hash::get($this->data,'User.expdate');
			$user_contract = Hash::get($this->data,'User.contract');
			$contract = $this->Contract->find('first', array('conditions' => array('Contract.name' => $user_contract) ));
			$contract_exp = $contract['Contract']['expdate'];

			$rc = $this->cmpdate($contract_exp,$user_exp);
			if($rc >= 0){
//debug('ok');
				return true;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
    }

	// パスワード有効期限の値の正当性チェック
	// true: 正しい　/　false: ダメ
    function isValidPwdExp($field){
		try{
			$this->loadModel('Role');
			$flg_expchk = $this->Role->chkRole(Hash::get($this->data,'User.group_id'),
					array(	'controller' => 'users',
							'action' => 'pwdExpdate'));
			if(!$flg_expchk){
$this->log('Super! ok');
				return true;
			}
			$user_exp = Hash::get($this->data,'User.pwd_expdate');
			// 本当はbootstrap からパスワード期限の値をとる。とりあえず決め打ち
			$this->loadModel('MySecurity');
			$limit = $this->MySecurity->get_password_item('time_limit');
			$maxdate = $this->getexpday($limit);
			$rc = $this->cmpdate($maxdate,$user_exp);
			if($rc >= 0){
				return true;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
    }

	// Contract　契約が必要な権限に、契約が指定されているか
	// true: 正しい　/　false: ダメ
    function isValidContractId($field){
		try{
$this->log("isValidContractId start",LOG_DEBUG);
$this->log($field,LOG_DEBUG);
			if(empty($field['contract_id']) ||
				$field['contract_id'] < 1){
				// 契約の記載がないユーザ登録
$this->log($this->data,LOG_DEBUG);
				$this->loadModel('Role');
				$flg_new = $this->Role->chkRole(Hash::get($this->data,'User.group_id'),
						array(	'controller' => 'navi',
								'action' => 'new'));
$this->log('flg_new['.$flg_new.']',LOG_DEBUG);
				if(!$flg_new){
$this->log('isValidContractId --- start　ゲストなのでOK',LOG_DEBUG);
					return true;
				}
				$flg_super = $this->Role->chkRole(Hash::get($this->data,'User.group_id'),
						array(	'controller' => 'users',
								'action' => 'notDelete'));

				if($flg_super){
$this->log('isValidContractId --- start　スーパーなのでOK',LOG_DEBUG);
					return true;
				}
$this->log('isValidContractId --- start　スーパーでもゲストでもないのでNG',LOG_DEBUG);
				return false;
			} else {
                return true;
            }
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
    }

	// Contract　人数に空きがあるかどうかのチェック
	// true: 正しい　/　false: ダメ
    function hasSpace($field){
		try{
			if(empty($field['contract_id']) ||
				$field['contract_id'] < 1){
				// 契約の記載がないユーザ登録
//$this->log($this->data,LOG_DEBUG);
				$this->loadModel('Role');
				$flg_new = $this->Role->chkRole(Hash::get($this->data,'User.group_id'),
						array(	'controller' => 'navi',
								'action' => 'new'));
				if(!$flg_new){
$this->log('hasSpace --- start　ゲストなのでOK',LOG_DEBUG);
					return true;
				}
				$flg_super = $this->Role->chkRole(Hash::get($this->data,'User.group_id'),
						array(	'controller' => 'users',
								'action' => 'notDelete'));

				if($flg_super){
$this->log('hasSpace --- start　スーパーなのでOK',LOG_DEBUG);
					return true;
				}
$this->log('hasSpace --- start　スーパーでもゲストでもないのでNG',LOG_DEBUG);
				return false;
			}
$this->log('hasSpace --- start ここに来るのはゲストじゃない',LOG_DEBUG);
//$this->log($field,LOG_DEBUG);
//$this->log($this->data,LOG_DEBUG);
			$_my_uid = Hash::get($this->data,'User.id');
			$this->loadModel('Contract');
			$this->Contract->recursive = -1;
			$contract = $this->Contract->findById($field['contract_id']);
//$this->log($contract,LOG_DEBUG);
			$_max = $contract['Contract']['usernum'];
			$_now = $contract['Contract']['user_count'];
$this->log('max['.$_max.'] now['.$_now.']',LOG_DEBUG);
			if($_max > $_now){
$this->log('hasSpace --- OK! 余裕があります',LOG_DEBUG);
				return true;
			}elseif($_max < $_now){
$this->log('hasSpace --- NG! ダメです',LOG_DEBUG);
				return false;
			}else{
				// $_max == $_now のとき
$this->log('hasSpace --- 微妙なので調べる',LOG_DEBUG);
				if(empty($_my_uid)){
$this->log('hasSpace --- もういっぱいなのでダメです',LOG_DEBUG);
					return false;
				}
				$userlist = $this->find('list',array(
					'conditions' => array('contract_id' => $field['contract_id'])));
//$this->log($userlist,LOG_DEBUG);
				if(array_key_exists($_my_uid,$userlist)){
$this->log('hasSpace --- 自分は既に入っているからOKです',LOG_DEBUG);
					return true;
				} else {
$this->log('hasSpace --- もういっぱいなのでダメです2',LOG_DEBUG);
					return false;
				}

			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
    }


/*************************************************************
 * 独自メソッド
 */
	public function beforeFind($queryData = array()) {
		   	/** 部門 **/
		if(defined('SECTIONS')){
			$this->bindModel( array(
				'hasAndBelongsToMany' => array(
					'Section' => array(
						'className' => 'Sections.Section',
						'joinTable' => 'sections_users',
						'foreignKey' => 'user_id',
						'associationForeignKey' => 'section_id',
						'unique' => 'keepExisting',
						'conditions' => '',
						'fields' => '',
						'order' => '',
						'limit' => '',
						'offset' => '',
						'finderQuery' => '',
					))));
		}

		parent::beforeFind($queryData);
	}
	public function beforeSave($options = array()) {
		if(defined('SECTIONS')){
			$this->bindModel( array(
				'hasAndBelongsToMany' => array(
					'Section' => array(
						'className' => 'Sections.Section',
						'joinTable' => 'sections_users',
						'foreignKey' => 'user_id',
						'associationForeignKey' => 'section_id',
						'unique' => 'keepExisting',
						'conditions' => '',
						'fields' => '',
						'order' => '',
						'limit' => '',
						'offset' => '',
						'finderQuery' => '',
					))));
		}
		$rc = parent::beforeSave($options);
		return $rc;
	}

	/**
	 * save_expdate 一時ゲストの有効期限を変更
	 */
	function save_expdate($id,$date){
		if($id == null) return false;
   		$this->recursive = -1;
		$found = $this->find('first',
    						array( 'conditions' =>
    							array( 	"{$this->name}.id" => $id,
 //   									"{$this->name}.is_deleted" => 'N'
    						)));
		if(!empty($found)){
			$_date1 = $found['User']['expdate'];
			$rc = $this->cmpdate($_date1,$date);
			if($rc < 0){
				// 期限が延びるときだけ変更　2011.09.06
				parent::save(array(
							'id' => $id,
							'expdate' => $date,
							'modified' => null
						));
			}
		}
		return true;
	}


	/**
	 * getActiveUser 有効なユーザ
	 *
	 * @var int $id:
	 * @var string $pw:
	 * @return array: user array
	 */

	function getActiveUser($id,$pw){
		if($id == null) return(array());
		$this->recursive = 0;
		$myCond = array();
		if(filter_var($id, FILTER_VALIDATE_EMAIL)){
			$myCond = array("{$this->name}.email" => $id,
    						"{$this->name}.pwd" => $pw);
		} else {
			$myCond = array("{$this->name}.id" => $id,
    						"{$this->name}.pwd" => $pw);
		}
//$this->log($myCond);
		$found = $this->find('first',
    						array( 'conditions' => $myCond
    					));
//$this->log($found);

		if(!empty($found)){
			// 拡張項目も入れ込む
			$user = array_merge($found['UserExtension'],$found['User']);
			return $user;
//			return $found['User'];
		}
		return $found;
	}

/**
* function getRetry
* @brief セッションリトライカウンタ
* 			数えていなければ　0を返す。
*			テーブルがあれば当該タイプに該当するものを返す
* @retval mixed	 有効であれば設定値、無効であれば 0
*/
	public function getRetry($id){
	$this->log($id);
		$rc = $this->MySecurity->get_lockout_item();
		if(is_null($rc)){
			return 0;
		}
		$this->id = $id;
		$data = $this->field('pwd_fail_count');
		if($data == false){
			return 0;
		}
		return $data;
	}

/**
* function addRetry
* @brief セッションリトライカウンタカウントアップ
* 			現在はConfigure の内容を返す。
*			テーブルがあれば当該タイプに該当するものを返す
* @retval mixed	 有効であれば設定値、無効であれば null
*/
	public function addRetry($email){
		try{
			$rc = $this->MySecurity->get_lockout_item();
			// セキュリティ指定がなければスルー
			if(is_null($rc)){
				return 0;
			}
			// パラメータが空ならスルー
			if(empty($email)){
				return 0;
			}
			$user = $this->findByEmail($email);
			if(Hash::check($user, 'User.id')){
$this->log('### ユーザがいたのでカウントアップ');
				$count = $this->getRetry($user['User']['id']);
				$count++;
				$this->id = $user['User']['id'];
				$data = $this->saveField('pwd_fail_count',$count);
				if($data == false){
					return 0;
				}
				return $data;
			} else {
$this->log('### ユーザがいないのでスルー');
				// 当該ユーザが存在しないのでスルー
				return 0;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return null;
	}

	public function login_ok($user,$request){

		// セッションに、通常処理までの画面リストを登録
		$jmp_list = array();

		/**
		* パスワードの期限をチェック（期限が切れていたらパスワード変更画面に遷移）
		*/
		$_changepwd_force = $this->Role->chkRole($user['group_id'],
										array (	'controller' => 'users',
												'action' => 'changepwd_force'));
//		$_event_action = HUMAN_TEST ? '（画像認証）' : '';
		$_event_action = '';	// 画像認証ありかどうかは記載しない
		if($_changepwd_force){
			if($this->is_Pwd_expend($user)){
$this->log('############ pwd 期限切れ');
				$jmp_list[] = array('controller' => 'users',
							'action' => 'changepwd_force',
							$user['id']);
			} else {
$this->log('############ pwd 期限 ok');
			}

			if($user['is_chgpwd_demand'] == 'Y'){
$this->log('############ pwd 強制変更フラグON');
				$jmp_list[] = array('controller' => 'users',
							'action' => 'changepwd_force',
							$user['id']);
			}
		} else {
			if($this->is_Pwd_expend($user)){
$this->log('############ 一時ゲスト　pwd 期限切れ (ログインできない)');
//				$_event_action = HUMAN_TEST ? '（画像認証）' : '';
				$logid = $this->writeLog(
				array(
					'type' => 'login',
					'login_id' => $user['email'],
					'event_action' => 'ログイン'.$_event_action,
					'remark' => 'パスワード期限切れ',
					'result' => '失敗',
				));
				// ここで言語を決めないとメッセージと言語が合わなくなる
				$_lang = $this->getLang();
				$_my_lang = $user['lang'] == 'auto' ? VALUE_System_Default_Lang : $user['lang'];
$this->log('############ 今の言語['.$_lang.']['.$_my_lang.']');
				if($_lang != $_my_lang){
					$this->setLang($_my_lang);
$this->log('############ とりあえず言語設定['.$_my_lang.']');
				}

				$message = __('Sorry, Your period expired.');
				$element = 'Flash/error';
				$params = '';
				CakeSession::write('Message.flash', compact('message','element','params'));
$this->log('############ 一時ゲスト　juplist clear (ログインできない)');
				return array(
						'controller' => 'users',
						'plugin' => null,
						'action'=>'logout');
$this->log('############ 一時ゲスト　juplist clear return');
				return;
			}
		}
		/**
		 * 有効期限チェック	 	1 -- 期限前			$one > $two
		 * 					0 -- 等しい			$one = $two
		 * 					-1 -- 期限切れ		$one < $two
		 */
$this->log("#################### 有効期限チェック　start");
		$user_exp = $this->cmpdate($user['expdate']);
$this->log("user_exp end[".$user_exp."]");
		//$this->log("chkDate end[".$user_exp."]");
		$cont_exp = $this->Contract->isExpdate($user['contract_id']);
$this->log("cont_exp end[".$cont_exp."]");
		// 全てのナビを開放しているのは super
		$_is_super = $this->Role->chkRole($user['group_id'],
					array('controller' => 'navi',
							'action' => 'all'));
		// パスワード変更画面が出ないのは tmp ゲスト
		$_changepwd_force = $this->Role->chkRole($user['group_id'],
								array (	'controller' => 'users',
										'action' => 'changepwd_force'));

		if(($cont_exp < 0) && $_is_super){
$this->log("スーパーなので契約切れは見ない");
		} elseif(($user_exp < 0) || ($cont_exp < 0)){
$this->log("期限切れ[".$_changepwd_force."]");
//$this->log($user);
			// まず言語を決めておく
			$_lang = $this->getLang();
			$_my_lang = $user['lang'] == 'auto' ? VALUE_System_Default_Lang : $user['lang'];
$this->log('############ 今の言語['.$_lang.']['.$_my_lang.']');
			if($_lang != $_my_lang){
				$this->setLang($_my_lang);
$this->log('############ とりあえず言語設定['.$_my_lang.']');
			}

			// 期限切れ
			$_event_data = '';
			$_result = '失敗';
			$_remark = '期限切れ';
			if($user_exp < 0){
				// ユーザの期限が切れた
				$_remark .= '[ユーザ]';
			}
			if($cont_exp < 0){
				// 契約の期限が切れた
				$_remark .= '[契約]';
			}
			$this->writeLog(
				array(
					'event_action' =>'ログイン' . $_event_action,
					'remark' => $_remark,
					'result' => $_result,
					'event_data' => $_event_data,
				),$user);

			if(!$_changepwd_force){
$this->log("一時ゲスト？　（ユーザ期限切れ）[".$this->getLang().']');
				// 一時ゲストはパスワード期限切れと同じメッセージを出してログイン画面に戻る
				return array(
						'controller' => 'users',
						'plugin' => null,
						'action'=>'logout',
						'message' => 'experr1',
						'element' => 'error',
						$user['id'],
						);
				return;

			}
			$this->loadModel('UserExtension');
			$this->UserExtension->setExpEnd($user['id'],VALUE_Flg_Lock);
			/**
			* 期限切れとロック解除は別物ゆえ、
			* 期限切れのときはロックは解除（どうせ入れないから）
			*/
//			$this->User->lockout_release($user['id']);
			return array(
					'controller' => 'users',
					'plugin' => null,
					'action'=>'expEnd',
					$user['id']);
			return;
		}
$this->log("#################### 有効期限チェック　end");


		/**
		* とりあえずのログイン認証に成功したらlastlogin を入れる
		*/
		$this->setLastLogin($user);
		// ロックアウト関係もクリアする
		$this->lockout_release($user['id']);
		// ログインOKなら、期限切れフラグが立っていたらクリア
		// 期限延長申請だったらクリアしない（何度も申請メールを出さないため）
		// 管理者が期限を変更したら申請フラグもクリア　2016/08
		$userExt = $this->UserExtension->findById($user['id']);
		if(Hash::check($userExt,'UserExtension.expdate_apply_flg')){
			if($userExt['UserExtension']['expdate_apply_flg'] == VALUE_Flg_Lock){
				// フラグのクリア
				$this->UserExtension->save(array('id' => $user['id'], 'expdate_apply_flg' => VALUE_Flg_None));
			}
		}
$this->log('############ login_ok');
		/**
		* ログイン 誓約が必要なら誓約画面に遷移（オプション）
		*/
		$_agreement = $this->Role->chkRole($user['group_id'],
										array (	'controller' => 'users',
												'action' => 'agreement'));
$this->log("ログイン 誓約　チェック　[".$_agreement."]",LOG_DEBUG);
		if($_agreement == true){
$this->log("ログイン 誓約を求める",LOG_DEBUG);
			$jmp_list[] = array('controller' => 'users',
						'action' => 'agreement');
		} else {
$this->log("ログイン 誓約なし",LOG_DEBUG);
		}


	/**
	 *  言語が決まっていなかったら必ず指定させる
	 */
		switch($user['lang']){
			case 'jpn':
			case 'eng':
				break;
			default	:
$this->log('言語選択');
				/**
				* 初めてのログインなら、言語を決める画面に遷移
				*/
				// とりあえずデフォルト言語に決めておく
				Configure::write('Config.language',VALUE_System_Default_Lang);

				$jmp_list[] = array(
						'controller' => 'users',
						'action'=>'langspecify',
						$user['id']
					);
			break;
		}
		/**
		*　2016/10/06
		* 直接受信画面に遷移（ゲストの初回）　仮）
		*/
		$is_direct = $this->Role->chkRole($user['group_id'],
										array (	'controller' => 'users',
												'action' => 'direct'));
		if($is_direct){
			/**
			 *  当該ユーザに宛てた最新のものを検索
			 *  初めての「初回」に限定するなら、User.lastlogin == NULL の人に限定する
			 */
			$this->loadModel('Status');
			$direct_stt = $this->Status->find('first',array(
				'conditions' => array('Status.user_id' => $user['id']),
				'order' => array('Status.id' => 'desc'),
				'recursive' => -1,
				));
			if(!empty($direct_stt)){
				$jmp_list[] = array(
						'controller' => 'statuses',
						'action'=>'view',
						$direct_stt['Status']['id']
					);
			}
		}

		/**
		* 一通り終わったらまず表示する画面（ログイン成功）
		*/
$this->log('@@@@@@@ login ---------- 5 ログイン成功');
//		$_event_action = HUMAN_TEST ? '（画像認証）' : '';
		$this->writeLog(
			array(
				'event_action' =>'ログイン' . $_event_action,
				'remark' => HUMAN_TEST ? '画像認証' : '',
				'result' => '成功',
				'event_data' => '',
			),$user);
		// ここで、通常の画面を決めておく
		$next_uri = $this->getNextUriAfterLogin($user,$request);

		$jmp_list[] = $next_uri;

		/**
		* 上から順に実行
		*/
$this->log($jmp_list);
		$uri = array_shift($jmp_list);
		CakeSession::write('jmp_list',$jmp_list);
		if(empty($uri)){
			// 取得できなかったらとりあえず受信履歴
			return array(	'controller' => 'statuses',
							'action' => 'index');
		}
		return $uri;
	}
/**
* function isLockoutUser
* @brief ロックアウト中かどうかしらべる
*
* @param  array user : getActiveUser で得たデータ または　email
* @param  array my_sec : 適用するセキュリティオプション
* @return bool  true : ロックアウト中
*				false: ロックアウト中ではない（時間による解除も）
*
*/

	public function isLockoutUser($user = array(),$my_sec = array()){
		try{
//$this->log('isLockoutUser',LOG_DEBUG);
//$this->log($my_sec,LOG_DEBUG);
//$this->log($user,LOG_DEBUG);
			if(empty($my_sec)){
				// セキュリティオプションの設定がないときはロックアウトなし
				$this->loadModel('MySecurity');
				$_my_sec = $this->MySecurity->setLockoutInit();
				if(empty($_my_sec)){
$this->log('ロックなし:セキュリティオプションなし',LOG_DEBUG);
					return false;
				}
			}

            // 入力パラメータがユーザデータだったらそのまま判定
            // email だけだったらテーブルから読んで判定
            $_my_user = array();
            if(is_array($user)){
$this->log('isLockoutUser user = array');
                $_my_user = $user;
            } else {
$this->log('isLockoutUser user = email');
                $udata = $this->findByEmail($user);
                if(empty($udata)){
                    return false;
                } else {
                    $_my_user = $udata['User'];
                }
            }

			$flg = $this->Role->chkRole($_my_user['group_id'],
										array(	'controller' => 'users',
												'action' => 'lockout'));

			if($flg){
				switch($_my_user['lockout_stat']){
					case VALUE_Flg_Manual:	// 手動ロックアウト中はロックアウト
						// 管理者によるロック中
$this->log('管理者によるロック中',LOG_DEBUG);
						return true;
						break;

					case VALUE_Flg_Lock:
					case VALUE_Flg_Apply:
						// ロック中＆解除依頼中
//						if($my_sec['lockout_date'] != 0){
							// 一定時間で解除
							if(!empty($_my_user['lockout_expdate'])){
								// 有効期限が書いてあったら
								$today = date('Y-m-d H:i:s');
								// 今と比較
								if($today >= $_my_user['lockout_expdate']){
									// 過ぎていたらロックではないとみなす
$this->log('ロックなし:ロック期限切れ →　自動解除',LOG_DEBUG);
									$this->lockout_release($_my_user['id']);
									return false;
								}
							}
//						}
$this->log('ロック中 or 解除依頼中',LOG_DEBUG);
						return true;
						break;

					case VALUE_Flg_None:
					// ロックされていない
					default:
$this->log('isLockoutUser ロックなし:ロックされていない',LOG_DEBUG);
						return false;
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}
/**
* function doLockoutIdNow
* @brief 条件を満たしていれば　ID　をロックアウトする
* @retval	bool 	true : ロックアウト
*					false: ロックしない
*/
	public function doLockoutIdNow($email = null){
		try{
			// ロックアウト関連のオプションを見る
$this->log('doLockoutIdNow start ['.$email.']',LOG_DEBUG);
			if(empty($email)){
				// パラメータが空ならここでのチェックはしない
				return false;
			}

			$my_limit = $this->MySecurity->get_lockout_item();
//$this->log($my_limit,LOG_DEBUG);
			if(is_null($my_limit)){
				// オプションがなければロックアウトは無視して良い
				return false;
			}
			// id, pass のエラーと、画像認証エラーは別にとる
			$_fail_idpass = (empty($email)) ? 0 : $this->MySecurity->getCount($email);

			$user = $this->find('first',array(
				'conditions' => array('email' => $email),
				'recursive' => -1));
			if(!empty($user)){

				$flg = $this->Role->chkRole($user['User']['group_id'],
											array(	'controller' => 'users',
													'action' => 'lockout'));
				// ロックアウト対象ユーザなら
				if($flg){
					// DBの情報はとりあえず無視
	$this->log('sess 内 pass 失敗['.$_fail_idpass.']',LOG_DEBUG);
					if($this->MySecurity->chkLimit($_fail_idpass,'retry_limit_id')){
	$this->log('ユーザロックアウト　1　今回のセッションで',LOG_DEBUG);
						$this->lockout($user['User']['id'],true);
						return true;
					}

					if($my_limit['is_fail_count_keep_db']){
						$pwd_fail_count = $this->getRetry($user['User']['id']);
	$this->log('db ['.$pwd_fail_count.']',LOG_DEBUG);
						if($this->MySecurity->chkLimit($pwd_fail_count,'retry_limit_id')){
	$this->log('ユーザロックアウト 2 db 内容累積で',LOG_DEBUG);
							$this->lockout($user['User']['id'],true);
							return true;
						}
					}
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}
/**
* function doLockoutSessionNow
* @brief 条件を満たしていれば　セッションロックアウトする
* @retval	bool 	true : ロックアウト
*					false: ロックしない
*/
	public function doLockoutSessionNow($email = null){
		try{
			// ロックアウト関連のオプションを見る
$this->log('doLockoutSessionNow start ['.$email.']',LOG_DEBUG);

			$my_limit = $this->MySecurity->get_lockout_item();
//$this->log($my_limit,LOG_DEBUG);
			if(is_null($my_limit)){
				// オプションがなければロックアウトは無視して良い
				return false;
			}
			$is_debug = $this->MySecurity->is_debug();
			// id, pass のエラーと、画像認証エラーは別にとる
			$_fail_session = $this->MySecurity->getCount('session');;
			$_fail_human = $this->MySecurity->getCount('human');
$this->log('human['.$_fail_human.'] sess['.$_fail_session.']',LOG_DEBUG);
if($is_debug) print_r('User.php:1492 human['.$_fail_human.'] sess['.$_fail_session.']<br>');
			// 画像認証もカウントする場合はセッションに足す
			if($my_limit['is_human_test_retry_count']){
if($is_debug) print_r('User.php:1495 human['.$_fail_human.'] + sess['.$_fail_session.']<br>');
				$_fail_session += $_fail_human;
			}
			if($this->MySecurity->chkLimit($_fail_session,'retry_limit_session')){
				// セッションがでオーバーしたのでロック画面に遷移
				if($my_limit['is_lockout_session_limit']){
					// セッションがでオーバーしたときもIDロックする場合
$this->log(' セッションがでオーバーしたときもIDロックするモード['.$email.']',LOG_DEBUG);
					$user = $this->find('first',array(
						'conditions' => array('email' => $email),
						'recursive' => -1));
//$this->log($user,LOG_DEBUG);
					if(!empty($user)){
						// 今ログインしようとしたユーザがいた
						$flg = $this->Role->chkRole($user['User']['group_id'],
													array(	'controller' => 'users',
															'action' => 'lockout'));
						// ロックアウト対象ユーザなら
						if($flg){
$this->log('セッションロックアウト1-1 ユーザもロックする',LOG_DEBUG);
							$rc = $this->lockout($user['User']['id'],true);
							return true;
						} else {
$this->log('セッションロックアウト1-1-1 ロック対象ユーザじゃなかったので画面遷移だけ',LOG_DEBUG);
							// 入力アドレスに対応するユーザがいなかった
							return array(
									'controller' => 'users',
									'action'=>'lockout',
									);
						}
					} else {
$this->log('セッションロックアウト1-2 ユーザいないので画面遷移だけ',LOG_DEBUG);
						// 入力アドレスに対応するユーザがいなかった
						return array(
								'controller' => 'users',
								'action'=>'lockout',
								);
					}
				} else {
					// 画面遷移するだけ
$this->log('セッションロックアウト2 オプションにより画面遷移だけ',LOG_DEBUG);
					return array(
							'controller' => 'users',
							'action'=>'lockout',
							);
				}
			}
			return false;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 *  ロックアウト
	 *  ここに来たときは、ロックするかどうかのチェックは終わっているものとする
	 *  @param $flg :	true  :セキュリティにより自動的にロック(時間制限があったらそれも入れる)
	 *  				false :手動でロックする（時間制限なし）
	 *  			　　
	 */
	public function lockout($id,$flg = false){
		$rtn = null;
		try{
$this->log('lockout start id['.$id.'] flg['.$flg.']',LOG_DEBUG);
			if(!$this->exists($id)) return false;

			// ユーザが存在したら、ロックしていい権限かどうかチェック
			$this->recursive = -1;
			$expdate = null;
			$chgpwd_demand = null;
			$data = array('User'=>array());
			if($flg){
				// 有効期限のオプションがあったら今を基準に有効期限を計算
				$lockdate = $this->MySecurity->get_lockout_item('lockout_date');
				if(is_numeric($lockdate)){
					if($lockdate > 0){
						$expdate = date('Y-m-d H:i:s', strtotime('+ '.$lockdate . ' day'));
					} else {
						// 0 だったら無期限
						$expdate = null;
					}
				} else {
					$expdate = date('Y-m-d H:i:s', strtotime('+ '.$lockdate));
				}
				$data['User']['id'] = $id;
				$data['User']['lockout_stat'] = VALUE_Flg_Lock;
				$data['User']['lockout_expdate'] = $expdate;
				$data['User']['pwd_fail_count'] = 0; 	// 失敗カウントもクリアしとく

				$user = $this->find('first',array('conditions' => array('id' => $id),'recursive' => -1));
				$logid = $this->writeLog(
				array(
					'type' => 'lockout',
					'target_user_id' => $id,
					'event_action' => 'ロックアウト',
					'remark' => 'セキュリティ',
					'result' => '成功',
				));

/* 	管理者による手動解除のときだけ行うのでここでは設定しない
    ここで指定すると、受信専用ユーザがロックされたときにも適用されてしまう。
				$is_chgpwd_demand = $this->MySecurity->get_lockout_item('is_chgpwd_demand');
				if($is_chgpwd_demand){
					$data['User']['is_chgpwd_demand'] = 'Y';
				}
*/
//$this->log($data,LOG_DEBUG);
				$rtn = $this->save($data,false);
			} else {
				// 管理者による手動ロックアウト
				$rtn = $this->save(array('id' => $id, 'lockout_stat' => VALUE_Flg_Manual, 'lockout_expdate' => null),false);
				$logid = $this->writeLog(
				array(
					'type' => 'lockout',
					'login_id' => $this->me['email'],
					'target_user_id' => $id,
					'event_action' => 'ロックアウト',
					'remark' => '管理者による手動ロックアウト',
					'result' => '成功',
				));
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $rtn;
	}

	public function lockout_apply($id){
		$data = array();
		try{
			if($this->exists($id)){
				// 存在すればとりあえずロック関係クリア
				$this->id = $id;
				$data = $this->saveField('lockout_stat',VALUE_Flg_Apply);
			} else {
				return false;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $data;
	}

	/**
	 *  lockout_release
	 *  ロックアウト解除(パスワード変更はしない)
	 */
	public function lockout_release($id){
		$data = array();
		try{
			if($this->exists($id)){
				// 存在すればとりあえずロック関係クリア
				$this->id = $id;
				$data = $this->saveField('pwd_fail_count',0);
				$data = $this->saveField('lockout_stat',VALUE_Flg_None);
				$data = $this->saveField('lockout_expdate',null);
			} else {
				return false;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $data;
	}

/**
* lock
* @brief 管理画面からの手動ロックの実行
* @retval boolean $ret :
*/
    function lock($id = null){
		try{
			$this->recursive = -1;
			if($id === null){
				$this->setFlash(__('Invalid %s .','ID'),'Flash/error');
				return false;
			}
			$data = $this->findById($id);

			$this->setValidation('default');

			$update = array();
			$update['id'] = $id;
			$update['lockout_stat'] = VALUE_Flg_Manual;
			$update['modified'] = null;
			return(parent::save($update,false));

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
        return false;
	}

/**
* unlock
* @brief 管理画面からの手動ロック解除の実行
* @retval boolean $ret :
*/
    function unlock($id = null){
		return ($this->lockout_release($id));
	}

	/**
	 * send
	 * @todo 送信処理に伴うアドレス帳変更
	 * @param    array : $data
	 * @return   array
	 */
	function send($data = null){
		try{
			if(is_null($data)) return false;
			$this->recursive = -1;
			$role = $this->Role->getGroupList(array('controller' => 'users',
													'action' => 'auto'));
			$gid_default = key($role);
			if(isset($data['Reserve'])){
				foreach($data['Reserve']['rsv_data']['Content']['Status'] as $k => $v){
					$find = $this->find('first',array('conditions' =>
												array(
														$this->name.'.email' => $v['Status']['email']
														)));
					if(empty($find)){
$this->log('not found 新ユーザ自動登録');
//$this->log($data);
						$newData = $this->getNewData();
						$newData[$this->name]['group_id'] = $gid_default;
						$newData[$this->name]['name'] = $v['Status']['name'];
						$newData[$this->name]['email'] = $v['Status']['email'];
                        // 最初に送信したユーザの契約ID　2017.07.03
						$newData[$this->name]['contract_id'] = $data['User']['contract_id'];
						// 追加情報を登録
						if(!empty($v['Status']['addressbook_id'])){
							$this->loadModel('Addressbooks.Addressbook');
							$ad = $this->Addressbook->find('first',array(
								'conditions' => array('id' => $v['Status']['addressbook_id']),
								'recursive' => -1
								));
							// アドレス帳に詳細情報があれば追加
							$newData[$this->name]['name_yomi'] = $ad['Addressbook']['name_yomi'];
							$newData[$this->name]['division'] = $ad['Addressbook']['division'];
							$newData[$this->name]['div_yomi'] = $ad['Addressbook']['div_yomi'];
							$newData[$this->name]['etc'] = $ad['Addressbook']['etc'];
							$newData['UserExtension']['name_jpn'] = $ad['Addressbook']['name_jpn'];
							$newData['UserExtension']['name_eng'] = $ad['Addressbook']['name_eng'];
							$newData['UserExtension']['div_jpn'] = $ad['Addressbook']['div_jpn'];
							$newData['UserExtension']['div_eng'] = $ad['Addressbook']['div_eng'];

						}

						// デフォルト有効期限（３カ月）
						$newData[$this->name]['expdate'] =  date('Y-m-d', strtotime(OPTION_Default_AccountLife));
						// 有効期限やパスワード期限などあれば設定する

						$_result = '成功';
						$newid = null;

						if($this->saveAll($newData)){
							$newid = $this->getInsertID();
							$data['Reserve']['rsv_data']['Content']['Status'][$k]['Status']['user_id'] = $newid;
						} else {
$this->log('登録エラー');
						$_result = '失敗';
						$this->log(__FILE__ .':'. __LINE__ .': 登録できませんでした['.$v['Status']['email'].']');
						}
						/** 新規送信時エラーになる箇所 2016/06/17
						 *  →　CommonBehavior に関数を追加したので解決したはず　2016/6/20
						 */
						$logid = $this->writeLog(
						array(
							'type' => 'user_add',
							'login_id' => $v['Status']['email'],
							'target_user_id' => $newid,
							'event_action' => 'アカウント登録',
							'remark' => '自動',
							'result' => $_result,
						));
						/* */
					} else {
						// ロックアウトされていたら解除とか有効期限の延長などの処理をする
						$data['Reserve']['rsv_data']['Content']['Status'][$k]['Status']['user_id'] = $find['User']['id'];
					}
				}
			}
			return $data;
		} catch (Exception $e) {
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
			return false;
		}

	}

	/**
	 * getNewData
	 * @todo 追加のためのデフォルト値設定
	 * @param    array : $data
	 * @return   array
	 */
	function getNewData(){
		$data = $this->create();
		$data[$this->name]['pwd'] = md5(VALUE_DefaultPWD);
		$data[$this->name]['lang'] = 'auto';
		return $data;
	}

	function test($id,$pw){
print_r('human['.$this->MySecurity->_session->read('human').']');
print_r('idpass['.$this->MySecurity->_session->read('idpass').']');
	}


	/**
	 * getFromNames
	 * @todo 	言語に対応するユーザ名を求める
	 * @param   array  $uid
	 * @param   string $lang : null = 現在の言語
	 * @return  array $rtn : 言語に対応した名前
	 */
	function getFromNames($uid,$lang = null){
		$rtn = array();
		try{
			$_lang = ($lang == null) ? $this->getLang() : $lang;
$this->log('getFromNames ['.$uid.']['.$_lang.']');
			if($_lang == 'auto') $_lang = $this->getLang();

			$this->recursive = 0;
			$data = $this->findById($uid);
			if(empty($data)){
//$this->log('getFromNames rtn 1');
				return($rtn);
			}

			$division = $this->_getDivision($data,$_lang);
			if(strlen(trim($division)) > 0){
				$rtn['division'] = $division;
			}

			$name = $this->_getName($data,$_lang);
			if(strlen(trim($name)) > 0){
				$rtn['name'] = $name;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return($rtn);
	}
	/**
	 * _getName
	 * @todo 	言語に対応するユーザ名を求める
	 * @param   array  $user : flatten array
	 *  ↑　flatten やめました　Hash::get を使用します。　2016.5.24
	 * @param   string $lang : null = 現在の言語
	 * @return  string $name : 言語に対応した名前
	 */
	function _getName($user,$lang = null){
		try{
			$_field = $this->makeFieldName('User.name','UserExtension.name_',null,$lang);
			$_name = trim(@Hash::get($user,$_field));
			if(strlen($_name) == 0){
				return @Hash::get($user,'User.name');
			}
			return $_name;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return '';
	}
	/**
	 * _getDivision
	 * @todo 	言語に対応するDivision名を求める
	 * @param   array  $user : flatten array
	 *  ↑　flatten やめました　Hash::get を使用します。　2016.5.24
	 * @param   string $lang : null = 現在の言語
	 * @return  string $name : 言語に対応したDivision名
	 */
	function _getDivision($user,$lang = null){
		try{
			$_field = $this->makeFieldName('User.division','UserExtension.div_',null,$lang);
			$_div = trim(@Hash::get($user,$_field));
			if(strlen($_div) == 0){
				return @Hash::get($user,'User.division');
			}
			return $_div;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return '';
	}

	/**
	 * has_OneTimePwd
	 * @todo 	ワンタイムパスワードが必要かどうか調べる
	 * @param   int  $id :
	 * @return  mix  / array = 必要 : false = 不要
	 */
	function has_OneTimePwd($id = null){
		try{
$this->log('has_OneTimePwd start ['.$id.']');
			if($id == null) return false;
			$this->recursive = -1;
			$udata = $this->findById($id);
//$this->log($udata);
			$role = $this->Role->chkRole($udata[$this->name]['group_id'],
												array(	'controller' => 'users',
														'action' => 'oneTimePwd'));
			if(!$role) {
				// ワンタイムパスワードは必要ない
$this->log(' ワンタイムパスワードは必要ない');
				return false;
			}


			if($udata[$this->name]['is_chgpwd'] == 'Y'){
				// 自分で変更している
				$has_myPwd = $this->Role->chkRole($udata[$this->name]['group_id'],
												array(	'controller' => 'navi',
														'action' => 'chgMyPwd'));
				// 自分でパスワード変更ができる
				if($has_myPwd){
$this->log(' 自分で変更しているのでワンタイムパスワードは必要ない');
					return false;
				}
			}

$this->log(' ワンタイムパスワードが必要です。');
			return $udata;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .':' . $e->getMessage() );
$this->log(' err :'. $e->getMessage());
			return false;
		}
	}

	/**
	 * can_autoRelease
	 * @todo 	送信で自動解除してよいかどうか
	 *			ロックアウト対象ユーザ　＆　申請できないユーザ
	 *			かつ、管理者ロックでない
	 * @param   array  $user :
	 * @param   array  $my_sec : セキュリティオプション
	 * @return  bool  / true = 解除OK　: false = 解除NG
	 */
	function can_autoRelease($user = array(),$my_sec = array()){
		try{
			if(empty($my_sec)){
				// セキュリティオプションの設定がないときはロックアウトなし
				$this->loadModel('MySecurity');
				$_my_sec = $this->MySecurity->setLockoutInit();
				if(empty($_my_sec)){
$this->log('ロックなし:セキュリティオプションなし',LOG_DEBUG);
					return false;
				}
			}
			// ロック解除申請をする権限
			$flg_apply = $this->Role->chkRole($user['group_id'],
										array(	'controller' => 'users',
												'action' => 'lockout_apply'));

			switch($user['lockout_stat']){
				case VALUE_Flg_Manual:	// 手動ロックアウト中はロックアウト
				case VALUE_Flg_Apply:	// 申請を出している人は自動解除しないはず
					// 管理者によるロック中
$this->log('自動解除できない',LOG_DEBUG);
					return false;
					break;

				case VALUE_Flg_Lock:
					// ロック中
					if(!$flg_apply){
$this->log('自動解除対象',LOG_DEBUG);
						return true;
					}
					break;

				case VALUE_Flg_None:
				// ロックされていない
				default:
$this->log('can_autoRelease ロックなし:ロックされていない',LOG_DEBUG);
					return false;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 * mkOneTimePwd
	 * @todo 	ワンタイムパスワードが必要なときに作成する
	 * @param   int  $id :
	 * @param	bool $flg: true :必要なときだけ　/ false: 強制的に変更
	 * @return  string $newpwd / null : null のときはパスワード変更をしていない
	 */
	function mkOneTimePwd($id = null,$flg = true){
		try{
$this->log('mkOneTimePwd start ['.$id.']');
			$udata = null;
			if($flg){
				$udata = $this->has_OneTimePwd($id);
				if(is_array($udata)){
				} else {
$this->log('ワンタイムパスワードなし');
					// ワンタイムパスワードなし
					return null;
				}
			} else {
$this->log('強制的にパスワード変更');
				$this->recursive = -1;
				$udata = $this->findById($id);
			}
			// とりあえずMANUAL_PWD_MIN文字でシンプルに作成するだけ
			// あとでもう少しセキュアに設定

			// デバッグモードのときは履歴に反映しない
			$newpwd = VALUE_DefaultPWD;
			$new_pwdwk = $udata[$this->name]['pwd_work'];
			// デバッグモードでないときだけ本当にランダムなパスワードにする
			if(VALUE_Mail_Transport !== 'Debug'){
$this->log('メールがデバッグモードでないのでランダムに作成');
				for(;;){
					// 条件にかなうまでループ
					$newpwd = $this->mkrandamstring(AUTOPWD_DEFAULT_LEN);
					if($this->security_chk($newpwd)){
$this->log('['.$newpwd.'] OK!');
						break;
					}
//$this->log('['.$newpwd.'] 条件に合わないのでやりなおし!');
				}
				$h_limit = $this->MySecurity->get_password_item('history_limit');
				$new_pwdwk = null;
				if($h_limit > 0){
					// 過去の履歴をとっているときは、重ならないチェックも入れる
					$pwd_history = $udata[$this->name]['pwd_work'];
					// 条件が整うまでループ
					while(1){
						$_rc = $this->cmp_pwdwk(md5($newpwd),$pwd_history);
						if($_rc == 0) break;
						$newpwd = $this->mkrandamstring(MANUAL_PWD_MIN);
						continue;
					}
					// 過去とダブらないPWD
					$new_pwdwk = $this->push_pwdwk($newpwd,$udata[$this->name]['pwd_work']);
				}
			}
//$this->log($newpwd);
			if($this->_setPwd($id, md5($newpwd), $new_pwdwk)){
				// パスワードを変更したので　is_chgpwd をクリア
				$this->set_IsChgpwd($id);
				return($newpwd);
			} else {
$this->log('パスワード変更失敗');
				//!< @brief パスワード変更失敗
				return null;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. var_dump($e->getMessage()));
			return null;
		}
	}

	/**
	 * _setPwd
	 * @todo 	ワンタイムパスワードをユーザ情報に設定する
	 * @param   int  $id :
	 * @return  string $newpwd / null : null のときはパスワード変更をしていない
	 */
	function _setPwd($id,$md5pwd = null,$pwdwk = null){
$this->log($this->validationErrors);
$this->log('setpwd id['.$id.']md5['.$md5pwd,']');
		try{
			$this->recursive = -1;
			if(is_null($id)) return false;


			$savedata = array();
			$savedata['id'] = $id;
			$savedata['pwd'] = $md5pwd;

			// タイムリミットのオプションがあったらセットしておく
			$time_limit = $this->MySecurity->get_password_item('time_limit');
$this->log('setpwd　timelimit['.$time_limit.']');
			if($time_limit > 0){
				$_new_expdate = $this->getexpday($time_limit);
				$savedata['pwd_expdate'] = $_new_expdate;
			}
			if(is_null($pwdwk)){
			} else {
				$savedata['pwd_work'] = $pwdwk;
			}
			$savedata['modified'] = null;
//			$savedata['is_chgpwd_demand'] = 'N';	// パスワード変更強制フラグはオフにする
			$this->setValidation('setPwd');
			if($this->save($savedata)){
$this->log('_setPwd save OK');
				$user = $this->findById($id);
				// 仮パスワードがあったら削除
				$this->loadModel('Tmppassword');
				$this->Tmppassword->delete($user[$this->name]['email']);
				return true;
			}
$this->log('_setPwd save NG');
$this->log($this->validationErrors);
			return false;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. var_dump($e->getMessage()));
			return false;
		}
	}


	/**
	 * clr_pwdwk
	 * パスワード履歴：履歴クリア
	 *
	 * @param  array    $dat   Users.pwd_workカラムのデータ
	 *
	 * @return array    Users.pwd_workカラムのデータ(array型)を初期化したもの
	 */
	function clr_pwdwk( $dat ) {
//$this->log("common clr_pwdwk start : ");

		$hirabun	= array();
		$ngword		= array();
		if( $dat!=NULL && is_array($dat) ) {
			$ngword = $dat['ngword'];
		}

		$rtn_ary =
			array(	"history"	=>	array(),		//パスワード履歴のクリア
					"hirabun"	=>	array(),
					"ngword"	=>	$ngword,
				);

		return( $rtn_ary );
	}

	/**
	 * cmp_pwdwk
	 * パスワード履歴：パスワード比較
	 *
	 * @param  string    $newpw   新パスワード(md5変換済み)
	 * @param  array     $histry  履歴データ
	 *
	 * @return int   比較結果	0  (同一文字列無し) $newpw <> $id
	 *							1  (同一文字列有り) $newpw == $id
	 *							-1 (引数エラー)     error
	 */
	function cmp_pwdwk( $newpw, $hist = null) {
//$this->log("common cmp_pwdwk start : ");
//$this->log($newpw);
$this->log($hist);
		$rtn = 0;
		//パラメタチェック
		if( !isset($newpw) )	return(0);

		if( isset($hist['history']) ) {
			$result = array_search( $newpw, $hist['history'] );		//検索
			if( $result!==FALSE ){
				$rtn = 1;
			}
		}
//$this->log('common cmp_pwdwk end : ['.$rtn.']');

		return( $rtn );
	}

	/**
	 * push_pwdwk
	 * パスワード履歴：パスワード格納
	 *
	 * @param  string    $newpw   		新パスワード(md5変換前)
	 * @param  array     $dat      		Users.pwd_workカラムのデータ
	 * @param  array     $my_security   セキュリティ情報(ConfigureのPassword_Security)
	 *
	 * @return array     Users.pwd_workカラムのシリアライズ済みのデータ
	 *					 デバッグモードのときは、平文も記載
	 */
	function push_pwdwk( $newpw, $dat ) {
		$pwd_ary = $dat;
		try{
			$pwd_ary = $dat;
			$history_ary = array();
			if( isset($pwd_ary['history']) ){
				//履歴登録済み
				$history_ary = $pwd_ary['history'];
			}

			$h_limit = $this->MySecurity->get_password_item('history_limit');
			$is_debug = $this->MySecurity->is_debug();

			//履歴件数のチェック
			if( count($history_ary) === $h_limit ) {
				//件数が上限値であれば、古い履歴を削除する
				unset( $history_ary[count($history_ary)-1] );
			}
			else if( count($history_ary) > $h_limit) {
				//件数が上限値以上であれば、古い履歴を全て削除する
				for( $i=1,$x = count($history_ary); $x-$i >= $h_limit - 1; $i++ ) {
					unset( $history_ary[$x-$i] );
				}
			}

			//パスワードの格納
			array_unshift($history_ary, md5($newpw) );	//md5
			//履歴登録済み
			$pwd_ary['history'] = $history_ary;

			/**
			 * @todo	変更した日付も記載する　2016.07.25
			 */
			$changed_ary = array();
			if( isset($pwd_ary['changed']) ){
				//履歴登録済み
				$changed_ary = $pwd_ary['changed'];
			}
			//履歴件数のチェック
			if( count($changed_ary) === $h_limit ) {
				//件数が上限値であれば、古い履歴を削除する
				unset( $changed_ary[count($changed_ary)-1] );
			}
			else if( count($changed_ary) > $h_limit ) {
				//件数が上限値以上であれば、古い履歴を全て削除する
				for( $i=1,$x = count($changed_ary); $x-$i >=$h_limit-1; $i++ ) {
					unset( $changed_ary[$x-$i] );
				}
			}
			//パスワードの格納
			array_unshift($changed_ary, $this->now() );	//md5
			$pwd_ary['changed'] = $changed_ary;

			/**
			 * @todo	デバッグモードのときは平文も記載する
			 *			本番モードのときは *** として履歴に入れないと
			 *			途中でモード変更したときにずれてしまう
			 */
			$hirabun_ary = array();
			if( isset($pwd_ary['hirabun']) ){
				//履歴登録済み
				$hirabun_ary = $pwd_ary['hirabun'];
			}
			//履歴件数のチェック
			if( count($hirabun_ary) === $h_limit ) {
				//件数が上限値であれば、古い履歴を削除する
				unset( $hirabun_ary[count($hirabun_ary)-1] );
			}
			else if( count($hirabun_ary) > $h_limit ) {
				//件数が上限値以上であれば、古い履歴を全て削除する
				for( $i=1,$x = count($hirabun_ary); $x-$i >= $h_limit-1; $i++ ) {
					unset( $hirabun_ary[$x-$i] );
				}
			}
			//パスワードの格納
			if($is_debug == true){
//$this->log('### is_debug デバッグ中なので平文をのこすよ');
				array_unshift( $hirabun_ary, $newpw );
			} else {
//$this->log('### is_debug 本番なので平文は残さないよ');
				array_unshift( $hirabun_ary, '***' );
			}
			$pwd_ary['hirabun'] = $hirabun_ary;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return( $pwd_ary );
	}

	/**
	 * change_password
	 * パスワード履歴：パスワードのvalidate チェック格納
	 *
	 * @param  string    $newpw   		新パスワード(md5変換前)
	 * @param  array     $dat      		Users.pwd_workカラムのデータ
	 * @param  array     $my_security   セキュリティ情報(ConfigureのPassword_Security)
	 *
	 * @return array     Users.pwd_workカラムのシリアライズ済みのデータ
	 *					 デバッグモードのときは、平文も記載
	 */
	public function save_password($data){
		try{
$this->log(__FILE__ .':'. __LINE__ .': save_password');
$this->log($data);
			$this->recursive = -1;
			$udata = $this->findById($data[$this->name]['id']);

			$newpwd = $data[$this->name]['new_password'];
			// パスワード履歴の個数分の履歴を保管
			$h_limit = $this->MySecurity->get_password_item('history_limit');
			$new_pwdwk = null;
			if($h_limit > 0){
				$new_pwdwk = $this->push_pwdwk($newpwd,$udata[$this->name]['pwd_work']);
			}
			if($this->_setPwd($data[$this->name]['id'], md5($newpwd), $new_pwdwk)){
//				if(Hash::check($data,'User.is_chgpwd')){
//$this->log(__FILE__ .':'. __LINE__ .':set_IsChgpwd['.$data[$this->name]['is_chgpwd'].']');
//					$this->set_IsChgpwd($data[$this->name]['id'],$data[$this->name]['is_chgpwd']);
//				}
				return true;
			}
			return false;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. $e->getMessage());
		}
		return false;
	}
	/**
	 * saveAll
	 * ユーザ登録、変更
	 *
	 * @param  array    $data   		登録データ
	 *
	 * @return mix      false: 失敗
	 *					 デバッグモードのときは、平文も記載
	 */
	public function saveAll($data = array(),$options = array()){
		try{
			if(!empty($options)){
				return parent::saveAll($data,$options);
			}
$this->log('############# saveAll start');
//$this->log($data);
			$flg_browse = false;
			$this->loadModel('Role');

			if(isset($data[$this->name]['email'])){
$this->log('---- A');
				$_my_ag_id = 0;
				if(isset($data[$this->name]['id']) && empty($data[$this->name]['id'])){
$this->log('############# 新規作成時は root を作る');
					$data[$this->name]['pwd'] = md5(VALUE_DefaultPWD);
// ---------------------- 新アドレス帳対応　▼
					$flg_browse = $this->Role->chkRole($data[$this->name]['group_id'],
							array(	'controller' => 'addressbooks',
									'action' => 'add'));
$this->log('アドレス帳閲覧権限をしらべる１ flg_browse ['.$flg_browse.']');
					if($flg_browse){
$this->log('新アドレス帳プラグインあります1 権限もOK');
							$this->loadModel('Addressbooks.Addressgroup');
							$ag_root = $this->Addressgroup->create(
													array(	'name' => $data[$this->name]['name']. '_ROOT',
															'contract_id' =>$data[$this->name]['contract_id'],
															'parent_id' => null,
															'is_shared' => 'N',
															'is_root' => 'Y',
															));
							$data += $ag_root;
							// saveALL で一緒に登録するための一時的アソシエーション
							$this->bindModel(array('hasOne' => array(
														'Addressgroup' => array(
															'className' => 'Addressgroup',
															'foreignKey' => 'user_id',
															'conditions' => '',
															'fields' => '',
															'order' => ''
														))),false);
					}
// ---------------------- 新アドレス帳対応　▲
				} else {
					// 既存ユーザ
$this->log('---- B');
					$flg_browse = $this->Role->chkRole($data[$this->name]['group_id'],
							array(	'controller' => 'addressbooks',
									'action' => 'add'));
$this->log('アドレス帳閲覧権限をしらべる２ ['.$flg_browse.']');
					if($flg_browse){
$this->log('新アドレス帳プラグインあります2 権限もOK');
// ---------------------- 新アドレス帳対応　▼
						$_my_ag_id =  $this->_getAddressgroupPersonalRootId($data);
$this->log('-------------2 my_ag_id['.$_my_ag_id.']');
						$this->loadModel('Addressbooks.Addressgroup',true);
					// まだ　root ができていない場合
						if($_my_ag_id == null)	{
$this->log('---- D');
								$ag_root = $this->Addressgroup->create(
														array(	'name' => $data[$this->name]['name']. '_ROOT',
																'contract_id' =>$data[$this->name]['contract_id'],
																'parent_id' => null,
																'is_shared' => 'N',
																'is_root' => 'Y',
																));
								$data += $ag_root;
								// saveALl で一緒に登録するための一時的アソシエーション
								$this->bindModel(array('hasOne' => array(
															'Addressgroup' => array(
																'className' => 'Addressgroup',
																'foreignKey' => 'user_id',
																'conditions' => '',
																'fields' => '',
																'order' => ''
															))),false);
						} else {
							if(@$data[$this->name]['addreeegroup_id'] == $_my_ag_id){
							} else {
							// root のID が正しくなかったらなおす
								$data[$this->name]['addreeegroup_id'] = $_my_ag_id;
							}
						}
					}
$this->log('新アドレス帳プラグインあります2 おわり');
// ---------------------- 新アドレス帳対応　▲
				}
				// 日付が変更されていたらフラグをはずす
				if(isset($data[$this->name]['expdate'])){
$this->log('---- E');
					$expdate = $data[$this->name]['expdate'];
					$rc = $this->cmpdate($expdate);
					if($rc >= 0){
						$data['UserExtension']['expdate_apply_flg'] = VALUE_Flg_None;
					}
				}
			} elseif(isset($data[$this->name]['id'])){
$this->log('---- F id['.$data[$this->name]['id'].']');
				// 契約管理からの有効期限一括変更
				if(!$this->exists($data[$this->name]['id'])){
					// 存在しなければエラー
$this->log('---- F not exists');
					return false;
				}
			} else {
$this->log('---- G');
				return false;
			}
$this->log('############# save all start');
$this->log($this->validate);
$this->log($this->validationErrors);
			$this->setValidation('admin');
			$sv_rc  = parent::saveAll($data);
			$_id = $this->getLastInsertID();
			// 一時的アソシエーションを解除
			$this->resetAssociations();
$this->log('############# save all finish['.$flg_browse.']');
$this->log($this->validationErrors);
			if($sv_rc){
				if($flg_browse){	// アドレス帳を持っていたら
					if(!empty($_id)){
						$sv_rc = $this->_setAddressgroupId($_id);
		$this->log('============================ root 登録1');
							$this->Addressgroup->fixUserLinks($_id,$data[$this->name]['contract_id']);
		$this->log('============================ root 登録1-1');
					} else {
						$sv_rc = $this->_setAddressgroupId($data[$this->name]['id']);
		$this->log('============================ root 登録2');
						// contract_id が変わった時の対応
						// 項目そのものがないときは契約変更はないはず
						if(Hash::check($data,'User.contract_id')){
		$this->log('---- fixUserLinks start');
							$this->Addressgroup->fixUserLinks($data[$this->name]['id'],$data[$this->name]['contract_id']);
		$this->log('---- fixUserLinks end');
						}
					}
				}
			}
			return $sv_rc;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
			return false;
		}
	}
	/**
	 * saveAll_import
	 * ユーザ登録、変更(インポート時使用)
	 *
	 * @param  array    $data   		登録データ
	 *
	 * @return mix      false: 失敗
	 *					 デバッグモードのときは、平文も記載
	 */
	public function saveAll_import($data = array(),$options = array()){
		try{
			if(!empty($options)){
				return parent::saveAll($data,$options);
			}
$this->log('############# saveAll_import start');
$this->log($data);
			$flg_browse = false;
			$this->loadModel('Role');

			if(isset($data[$this->name]['email'])){
$this->log('---- A');
				$_my_ag_id = 0;
				if(isset($data[$this->name]['id']) && empty($data[$this->name]['id'])){
$this->log('############# 新規作成時は root を作る');
					//noumoto syusei
					if (empty($data[$this->name]['pwd'])){
						$data[$this->name]['pwd'] = md5(VALUE_DefaultPWD);
					}else{
						$data[$this->name]['pwd'] = md5($data[$this->name]['pwd']);
					}
// ---------------------- 新アドレス帳対応　▼
					$flg_browse = $this->Role->chkRole($data[$this->name]['group_id'],
							array(	'controller' => 'addressbooks',
									'action' => 'add'));
$this->log('アドレス帳閲覧権限をしらべる１ flg_browse ['.$flg_browse.']');
					if($flg_browse){
$this->log('新アドレス帳プラグインあります1 権限もOK');
							$this->loadModel('Addressbooks.Addressgroup');
							$ag_root = $this->Addressgroup->create(
													array(	'name' => $data[$this->name]['name']. '_ROOT',
															'contract_id' =>$data[$this->name]['contract_id'],
															'parent_id' => null,
															'is_shared' => 'N',
															'is_root' => 'Y',
															));
							$data += $ag_root;
							// saveALL で一緒に登録するための一時的アソシエーション
							$this->bindModel(array('hasOne' => array(
														'Addressgroup' => array(
															'className' => 'Addressgroup',
															'foreignKey' => 'user_id',
															'conditions' => '',
															'fields' => '',
															'order' => ''
														))),false);
					}
// ---------------------- 新アドレス帳対応　▲
				} else {
					// 既存ユーザ
$this->log('---- B');
					$flg_browse = $this->Role->chkRole($data[$this->name]['group_id'],
							array(	'controller' => 'addressbooks',
									'action' => 'add'));
$this->log('アドレス帳閲覧権限をしらべる２ ['.$flg_browse.']');
					if($flg_browse){
$this->log('新アドレス帳プラグインあります2 権限もOK');
// ---------------------- 新アドレス帳対応　▼
						$_my_ag_id =  $this->_getAddressgroupPersonalRootId($data);
$this->log('-------------2 my_ag_id['.$_my_ag_id.']');
						$this->loadModel('Addressbooks.Addressgroup',true);
					// まだ　root ができていない場合
						if($_my_ag_id == null)	{
$this->log('---- D');
								$ag_root = $this->Addressgroup->create(
														array(	'name' => $data[$this->name]['name']. '_ROOT',
																'contract_id' =>$data[$this->name]['contract_id'],
																'parent_id' => null,
																'is_shared' => 'N',
																'is_root' => 'Y',
																));
								$data += $ag_root;
								// saveAll_import で一緒に登録するための一時的アソシエーション
								$this->bindModel(array('hasOne' => array(
															'Addressgroup' => array(
																'className' => 'Addressgroup',
																'foreignKey' => 'user_id',
																'conditions' => '',
																'fields' => '',
																'order' => ''
															))),false);
						} else {
							if(@$data[$this->name]['addreeegroup_id'] == $_my_ag_id){
							} else {
							// root のID が正しくなかったらなおす
								$data[$this->name]['addreeegroup_id'] = $_my_ag_id;
							}
						}
					}
$this->log('新アドレス帳プラグインあります2 おわり');
// ---------------------- 新アドレス帳対応　▲
				}
				// 日付が変更されていたらフラグをはずす
				if(isset($data[$this->name]['expdate'])){
$this->log('---- E');
					$expdate = $data[$this->name]['expdate'];
					$rc = $this->cmpdate($expdate);
					if($rc >= 0){
						$data['UserExtension']['expdate_apply_flg'] = VALUE_Flg_None;
					}
				}
			} elseif(isset($data[$this->name]['id'])){
$this->log('---- F id['.$data[$this->name]['id'].']');
				// 契約管理からの有効期限一括変更
				if(!$this->exists($data[$this->name]['id'])){
					// 存在しなければエラー
$this->log('---- F not exists');
					return false;
				}
			} else {
$this->log('---- G');
				return false;
			}
$this->log('############# save all2 start');
$this->log($this->validate);
$this->log($this->validationErrors);
			$this->setValidation('admin');
			$sv_rc  = parent::saveAll($data);
			$_id = $this->getLastInsertID();
			// 一時的アソシエーションを解除
			$this->resetAssociations();
$this->log('############# save all finish['.$flg_browse.']');
$this->log($this->validationErrors);
			if($sv_rc){
				if($flg_browse){	// アドレス帳を持っていたら
					if(!empty($_id)){
						$sv_rc = $this->_setAddressgroupId($_id);
		$this->log('============================ root 登録1');
							$this->Addressgroup->fixUserLinks($_id,$data[$this->name]['contract_id']);
		$this->log('============================ root 登録1-1');
					} else {
						$sv_rc = $this->_setAddressgroupId($data[$this->name]['id']);
		$this->log('============================ root 登録2');
						// contract_id が変わった時の対応
						// 項目そのものがないときは契約変更はないはず
						if(Hash::check($data,'User.contract_id')){
		$this->log('---- fixUserLinks start');
							$this->Addressgroup->fixUserLinks($data[$this->name]['id'],$data[$this->name]['contract_id']);
		$this->log('---- fixUserLinks end');
						}
					}
				}
			}
			return $sv_rc;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
			return false;
		}
	}
	/**
	 * _getAddressgroupPersonalRootId
	 * @todo 	root アドレスグループID　を求める
	 * 			当該ユーザにroot アドレスグループの設定がないとき、root のIDを設定する。
	 *			contract_id はまれに不一致のことがあるのでまずは user_id から求める
	 * @param   array  $data :
	 *
	 * @return  int $assressgroup_id / null : null のときはパスワード変更をしていない
	 */
	function _getAddressgroupPersonalRootId($data = array()){
		try{
			$this->loadModel('Addressbooks.Addressgroup',true);
			$this->Addressgroup->recursive = -1;
			$data = $this->Addressgroup->find('first',array(
					'conditions' => array(
						'Addressgroup.user_id' => $data[$this->name]['id'],
//						'Addressgroup.contract_id' => $data[$this->name]['contract_id'],
						'Addressgroup.is_root' => 'Y',
						'Addressgroup.is_shared' => 'N')));
//$this->log($data);
			if(Hash::check($data,'Addressgroup.id')){
				return $data['Addressgroup']['id'];
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return null;
	}

	/**
	 * _setAddressgroupId
	 * @todo 	root アドレスグループID　のセット
	 * 			当該ユーザにroot アドレスグループの設定がないとき、root のIDを設定する。
	 * @param   int  $id :
	 *
	 * @return  string $newpwd / null : null のときはパスワード変更をしていない
	 */
	function _setAddressgroupId($id = null){
		try{
$this->log('_setAddressgroupId id['.$id.']');
			$user = null;
			$lastid = $id;
			// 直前に処理したユーザIDを求める
			if(!$this->exists($id)){
				// 新規のとき
				$lastid = $this->getLastInsertID();
			}
$this->log('lasnid ['.$lastid.']');
			$this->recursive = -1;
			$user = $this->findById($lastid);
//$this->log($user);
			if(is_null($lastid)) return false;

			$this->id = $lastid;

			// 個人root となるグループの条件
			$root_id = $this->_getAddressgroupPersonalRootId($user);

			$this->loadModel('Addressgroup');
			$this->Addressgroup->recursive = -1;
			$ag = $this->Addressgroup->find('first',array(
						'conditions' => array( 'user_id' => $lastid,
												'id' => $root_id)));
//$this->log($ag);
			if(Hash::check($ag,'Addressgroup.id')){

				$rc = $this->saveField(
					'addressgroup_id',
					$ag['Addressgroup']['id']
				);

				if($rc){
$this->log('save OK');
					return true;
				}
			}
$this->log('save NG');
			return false;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. var_dump($e->getMessage()));
			return false;
		}
	}

	/**
	 * setExpdate
	 * 有効期限変更：　各ユーザの有効期限変更（expdate_apply_flg もクリア）
	 *
	 * @param  mixed    $id   		ユーザID
	 * @param  array  $data   		変更データ　array( key => value,...)
	 * @return bool
	 */
	public function setExpdate($ids = null ,$data = array() ){
		try{
			$this->recursive = 0;
			$idlist = array();
			if(is_array($ids)){
				$idlist = $ids;
			} else {
				$idlist[] = $ids;
			}
			$count = 0;
			if(isset($data['expdate'])){
				foreach($idlist as $id){
					$sv = array('User' => array('id' => $id,
												'expdate' => $data['expdate'],),
								'UserExtension' => array('expdate_apply_flg' => 0));
//debug($sv);
					$rc = $this->saveAll($sv);
//debug($rc);
					if($rc) {
						$count++;
					}
				}
			}
			return $count;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 * set_IsChgpwd
	 * パスワード変更フラグの設定：　
	 *
	 * @param  mixed    $id   		ユーザID
	 * @param  array  $data   		変更データ　array( key => value,...)
	 * @return bool
	 */
	public function set_IsChgpwd($ids = null ,$flg = 'N'){
		try{
			$this->recursive = -1;
			$this->setValidation('admin');
			$idlist = array();
			if(is_array($ids)){
				$idlist = $ids;
			} else {
				$idlist[] = $ids;
			}
			$count = 0;
			if(!empty($flg)){
				foreach($idlist as $id){
					$sv = array('User' => array('id' => $id,
												'is_chgpwd' => $flg));
					$rc = $this->save($sv);
					if($rc) {
						$count++;
					}
				}
			}
			return $count;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 * setGuestExpdate
	 * ゲストのユーザ有効期限変更：　Content送信のタイミングで変更
	 *
	 * @param  mixed    $id   		ユーザID
	 * @param  array  $data   		変更データ　array( key => value,...)
	 * @return bool
	 */
	public function setGuestExpdate($email = null ,$expdate = null ){
		$rc = false;
		try{
$this->log('setGuestExpdate['.$email.'] exp['.$expdate.']');
			$this->recursive = 0;
			if(isset($expdate)){
				$user = $this->findByEmail($email);
//$this->log($user);
				$this->loadModel('Role');
				$is_default = $this->Role->chkRole($user['User']['group_id'],
					array(	'controller' => 'users',
							'action' => 'expdate ',
							'named' => array('from' => 'default')));
				if($is_default){
$this->log('比較 content['.$expdate.'] mine['.$user['User']['expdate'].']');
					$cmp = $this->cmpdate($expdate,$user['User']['expdate']);
					if($cmp > 0){
$this->log('変更します');
						$sv = array('User' => array('id' => $user['User']['id'],
													'expdate' => $expdate,),
									'UserExtension' => array('expdate_apply_flg' => 0));
						$rc = $this->saveAll($sv);
					}
				}
			}
			return $rc;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $rc;
	}


	/**
	 * setPwdExpdate
	 * パスワード変更：　各ユーザのパスワード変更
	 *
	 * @param  mixed    $id   		ユーザID
	 * @param  array  $data   		変更データ　array( key => value,...)
	 * @return bool
	 */
	public function setPwdExpdate($ids = null ,$expdate = null ){
		try{
			$idlist = array();
			$this->recursive = -1;
			if(is_array($ids)){
				$idlist = $ids;
			} else {
				$idlist[] = $ids;
			}
			$count = 0;
			if(!is_null($expdate)){
				foreach($idlist as $id){
					$sv = array('User' => array('id' => $id,
												'pwd_expdate' => $expdate
												));
//debug($sv);
					$rc = $this->save($sv);
//debug($rc);
					if($rc) {
						$count++;
					}
				}
			}
			return $count;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 * setCond
	 * パスワード変更：　有効なものだけにするか、全部見るかパラメータで切り替え
	 *
	 * @param  mixed  $params   	request->params
	 * @param  array  $cond   		もともとの条件
	 * @return array				検索条件
	 */
	public function setCond($params,$cond = array()){
		try{
			$today = $this->today();
			if(isset($params['named']['exp']) && ($params['named']['exp'] == 'all')){
			} else {
				// '0000-00-00' と NULL はちがうみたい　（調整中）
				$cond[] = array(	array('or' =>
										array(	'User.expdate >=' => $today,
												'User.expdate ='   => '0000-00-00',
												'User.expdate'   => NULL
										)),
										array('or' =>
										array(	'Contract.expdate >=' => $today,
												'Contract.expdate =' => '0000-00-00',
												'Contract.expdate'	=> NULL
										)));

			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $cond;
	}

	/**
	 * setSecurityCond
	 * パスワード変更：　有効なものだけにするか、全部見るかパラメータで切り替え
	 *
	 * @param  mixed  $params   	request->params
	 * @param  array  $cond   		もともとの条件
	 * @return array				検索条件
	 */
	public function setSecurityCond($params){
		$cond = array();
		try{
			if(empty($params['status'])){
				return null;
			}
			$today = $this->today();

			$cond = array();
			foreach($params['status'] as $type){
				switch($type){
					case 'lock':
						$cond['User.lockout_stat >'] = 0;
						break;
					case 'exp':
						// ただの日付比較だと、両方無期限のものも選択されてしまうのでこうなります。
						$cond['or'] =
							array('and' =>
								array(	'User.expdate !=' => null,
										'User.expdate != ' => '0000-00-00',
										'User.expdate <' => $today),
								array(	'Contract.expdate !=' => null,
										'Contract.expdate != ' => '0000-00-00',
										'Contract.expdate <' => $today)
									);
						break;
					case 'pwd':
						$cond['or '] =
							array('and' =>
								array(	'User.pwd_expdate !=' => null,
										'User.pwd_expdate != ' => '0000-00-00',
										'User.pwd_expdate <' => $today),
									);
						break;
					default:
					break;
				}
			}
			if(count($cond) > 1){
				return array('or' => $cond);
			} else {
				return $cond;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return null;
	}
	/**
	 * getIDfromEmail
	 * @todo 追加のためのデフォルト値設定
	 * @param    array : $data
	 * @return   array
	 */
	function getIDfromEmail($email = null){
		try{
			$this->recursive = -1;
			$found = $this->findByEmail($email);
//$this->log($found);
			if(isset($found[$this->name]['id'])){
				return $found[$this->name]['id'];
			}
$this->log('--- not found');
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return null;
	}

	/**
	 * getNextUriAfterLogin
	 * @todo 次のURI設定
	 * @param    array : $data
	 * @return   array
	 */
	function getNextUriAfterLogin($user = array(),$request = array()){
		try{
	$this->log('############ getNextUriAfterLogin パスワード期限→同意文→言語　が終わった後');

//	$this->log($user);
//debug($request);
			/**
			* 承認依頼メールのリンクから来た場合にはパラメータが付いているので
			*  承認一覧画面に遷移
			*/
			if(Hash::check($request->params,'named.r')){
	$this->log("承認一覧へのパラメータがあったら",LOG_DEBUG);
				$_aprv_all = $this->Role->chkRole($user['group_id'],
											array (	'controller' => 'approvals',
													'action' => 'index_all'));
				/**
				* 統括上長だったら他の部門の一覧も見える
				*/


				if($_aprv_all){
	$this->log("部門をこえて",LOG_DEBUG);
					return(array(	'controller' => 'approvals',
									'action' => 'index_all'));
				}

				$_aprv = $this->Role->chkRole($user['group_id'],
											array (	'controller' => 'approvals',
													'action' => 'index'));
				/**
				* 部門上長だったら自分の部門のみ
				*/
				if($_aprv){
	$this->log("部門内",LOG_DEBUG);
					return(array(	'controller' => 'approvals',
									'action' => 'index'));
				}
	$this->log("権限が無いので無視",LOG_DEBUG);
			}
$this->log('============ rtn');
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		/**
		* 通常は受信一覧画面
		*/
		return array(	'controller' => 'statuses',
						'action' => 'index');
	}


	/**
	 * getAdmin
	 * @todo 追加のためのデフォルト値設定
	 * @param    array : $data
	 * @return   array
	 */
	function getAprv($me = array(),$seclist = array(),$self = false){
		$list = array();
		try{
//debug($seclist);
			// users/index の権限がある場合はその契約の管理者とみなす
			// users/indexall はスーパーのみなので非表示
			$gids = $this->Role->getGroupList(array('controller' => 'approvals','action' =>'acting'));
			$cond = array(	'contract_id' => $me['contract_id'],
							'group_id' => array_keys($gids)
							);
			if(!empty($seclist)){
				// 部門があれば、関連部門に所属する代行承認可能なメンバーも出す
				$this->loadModel('Section');
				$uids = $this->Section->SectionsUser->find('list',
					array('conditions' => array('section_id' => $seclist),
						'fields' => array('id','user_id')));
				$cond['User.id'] = $uids;
			}

			$data = @$this->find('all',array('conditions' => $cond,
							'contain' => array(	'UserExtension',
												'Section',
												),
							));

			$_sectionParam = array('Section.0.name','Section.0.name_',null);

			$selfdata = array();
			foreach($data as $k => $v){
				$id = @Hash::get($v,'User.id');
				$name = $this->_getName($v);
				$email = @Hash::get($v,'User.email');
				$sname = $this->getFieldData($v,$_sectionParam);
				if($id == $me['id']){
					$selfdata[$id] = __('%s[%s]%s',$name,$sname,$email);
				} else {
					$list[$id] = __('%s[%s]%s',$name,$sname,$email);
				}
			}
			// 自己承認を許していたら自分を最後に追加
			if($self){
				if(empty($selfdata)){
					// 部門に入っていなくても追加
					$mydata = $this->find('first',array('conditions' => array('User.id' => $me['id']),
							'contain' => array(	'UserExtension',
												'Section',
												),
							));
					$name = $this->_getName($mydata);
					$email = @Hash::get($mydata,'User.email');
					$sname = $this->getFieldData($mydata,$_sectionParam);
					$list[$me['id']] = __('%s[%s]%s',$name,$sname,$email);

				} else {
					$list += $selfdata;
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $list;
	}

	/**
	 * is_Pwd_expend
	 * @todo パスワード期限切れかどうかを判定
	 * @param    array : $user ユーザデータ
	 * @return   bool : true　: 期限切れ / false : 期限内
	 */
	function is_Pwd_expend($user = array()){
		try{
			// users/pwdExpdate の権限がある場合のみチェック
			$role = $this->Role->chkRole($user['group_id'],
												array(	'controller' => 'users',
														'action' => 'pwdExpdate'));
			if(!$role){
$this->log('is_Pwd_expend チェックする権限ではないので無視します');
				return false;
			}

			$rc = $this->cmpdate(Hash::get($user,'pwd_expdate'));
			if($rc < 0){
$this->log('is_Pwd_expend : 期限切れです。');
				return true;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/* 残り日数を取得：2011.09.01
	 * 無期限のときは　-1
	 * それ以外のときは残り日数
	 */
	function get_remain_days($_u = array()){
		try{
			// 最新の状態を確認
			$user = $this->findById($_u['id']);
			// たまに拡張データが登録されていないユーザがあって、エラーになるので一応確認
			if(Hash::check($user,'UserExtension.expdate_apply_flg')){
				if($user['UserExtension']['expdate_apply_flg'] == VALUE_Flg_Lock){
$this->log('-- 期限切れなので０日(バリデーション対策)');
					return 0;
				}
			}

			if(!empty($user['User']['expdate'])){
				$today = getdate();
//debug($today);
				$today_i = mktime(0, 0, 0, $today['mon'],$today['mday'],$today['year']);

				$limit = date_parse($user['User']['expdate']);
//debug($limit);
				$limit_i = mktime(0, 0, 0,$limit['month'],$limit['day'],$limit['year']);
				$strings = "";

				if(defined('VALUE_Timing_of_Display_Beginning')) {	// 定義されているとき
					if (is_numeric(VALUE_Timing_of_Display_Beginning)){
						$strings = '+' . VALUE_Timing_of_Display_Beginning . 'days';
					} else {
						$strings = '+' . VALUE_Timing_of_Display_Beginning;
					}

				} else {
					$strings = '+0 days';
				}

				$disp = date_parse(date('Y-m-d',strtotime($strings)));
				$disp_i = mktime(0, 0, 0, $disp['month'],$disp['day'],$disp['year']);

				$diff = $limit_i - $today_i;
				$diffDay = $diff / 86400;//1日は86400秒

				$diff2 = ($limit_i - $disp_i) / 86400;//1日は86400秒
//$this->log($diff2);
				if($diff2 <= 0){	// 表示期間
					return $diffDay;
				} else {			// まだ表示しない
					return -1;
				}

				return $diffDay;
			}
	//$this->log('---3 無期限');
			return -1;		// 計算できない　または　無期限
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return 0; // 何かのエラーは０日とする
	}

	/**
	 * get_expdate_users
	 * @todo 有効期限にかかるユーザ一覧を取り出す
	 * AlertMailShell で使用
	 * @param    array : $glist 取り出し対象のグループID
	 * @param    string : $day  日付（Y-m-d）
	 * @return   array : $day の日が有効期限のユーザリスト
	 */
	function get_expdate_users($glist = null, $day = null){
		$rtn = array();
		try{
			$_bingo = $day;
			if($_bingo == null){
				$_bingo = $this->today();
			}
			// 既に期限が切れた人や、期限延長申請をしている人には出さない
			// expdate_apply_flg == 1 : 期限切れでログインできない
			// expdate_apply_flg == 2 : 延長申請を出している
			$cond = array(	'User.expdate' => $_bingo,
							'UserExtension.expdate_apply_flg' => 0);
			if(!empty($glist)){
				$cond['group_id'] = array_keys($glist);
			}
			$contain = array('UserExtension');
			$list = $this->find('all',array(
				'conditions' => $cond,
				'contain' => $contain,
				'recursive' => false
			));
			return $list;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $rtn;
	}

	/**
	 * setLastLogin
	 * @todo lastlogin　をセット　
	 * user / oneTimePwd のユーザには、有効期限を無効３カ月をセット
	 * @param    int : $user_id
	 * @return   array : $day の日が有効期限のユーザリスト
	 */
	function setLastLogin($user = array()){
		try{
			$this->id = $user['id'];
			$data = $this->saveField('lastlogin',$this->now());

			// ワンタイムパスワードを発行する権限(ゲスト)
			$this->loadModel('Role');
			$chg_expdate = $this->Role->chkRole($user['group_id'],array(
					'controller' => 'users',
					'action' => 'oneTimePwd'
				));
			// 返信できる権限なら　lastlogin ごとに延ばしてあげる
			$show_index_outofdate = $this->Role->chkRole($user['group_id'],
					array(	'controller' => 'contents',
							'action' => 'add',
							'named' => array('from' => 'Reply')));
//					array(	'controller' => 'statuses',
//							'action' => 'index',
//							'named' => array('from' => 'outofdate')));

			if($chg_expdate && $show_index_outofdate){
				// あまりないケースかもしれないが、自動的に有効期限が延長された場合は
				// 延長依頼フラグもリセットする
				$new_expdate = date('Y-m-d', strtotime(OPTION_Default_AccountLife));
				$savedata = array('User' =>
					array('id' => $user['id'],'expdate' => $new_expdate),
								'UserExtension' =>
					array('id' => $user['id'],'expdate_apply_flg' => 0));

				$data = $this->saveAll($savedata,array('validate' => false));
			}

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $data;
	}

	/**
 * Called after every deletion operation.
 *
 * @return void
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#afterdelete
 */
//	public function afterDelete() {
//$this->log('############### afterDelete');
//		$this->updateCounterCache();
//	}
/*--------------------------
*契約ののプルダウンメニュー作成
---------------------------*/
public function contractList($cond = array()){
		try{
			$this->loadModel('Contract');
			$e_act = $this->Contract->find('all',array(
						'conditions' => $cond,
						'recursive' => -1,
						'order' => array('name' => 'desc'),
						//'fields'=>array('DISTINCT name')
                        ));
			$e_act = Hash::combine($e_act, '{n}.Contract.id','{n}.Contract.name');
			return $e_act;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}
/**
 * mailServer
 * @todo 	ユーザIDが$idのメールサーバ設定を読み込む
 * @param 	int $id　ユーザID
 * @return  array 　　メールサーバ情報
 */
	function mailServer($id){

		$options = array('conditions' => array('User.id' => $id));
		$this->recursive = 1;
		$this->Group->unbindModel(array('hasAndBelongsToMany' => array('Role')));
		$data = $this->find('first', $options);


		return $data;
	}
	/**
	 * deleteUser	ユーザの削除後、アドレス帳からも削除する
	 *	$id	削除したいユーザid
	 */
    function deleteUser($id,$auth){
         // 削除するユーザのEメールを取得
        $delEmail = $this->find('first',array(
                            'conditions' => array('User.id' => $id),
                            'fields' => array('email'),
                            ));

        // Userの削除
        $this->delete($id);  // softDelete なので常にfalse
        $userDelRtn = $this->existsAndNotDeleted($id);
        if ($userDelRtn) {
            // まだアクティブならエラー
            $rtn = false;
        } else {
            // アクティブでなければOK
            $rtn = true;
            // 共通アドレス帳からも削除 個人のアドレス帳からは消さない
            $delAddRtn = $this->deleteAddress($delEmail['User']['email']);
        }
        return $rtn;
    }


	/**
	 * deleteAddress	データをアドレス帳から削除する
	 *	$data	削除したいEmailアドレス
	 */
    function deleteAddress($data = null){
        $rst = false;
        try{
            if (!empty($data)){
                // 共通アドレス帳のデータを取得
                $this->loadmodel('Addressbook');
                $this->Addressbook->unbindModel(array('hasAndBelongsToMany'=>array('Addressgroup')));
                $addressId = $this->Addressbook->find('all',array(
                                    'conditions' => array('Addressbook.is_shared' => 'Y'),
                                    'fields' => array('id','email'),
                ));

                foreach ($addressId as $id => $adddata){
                    if(strcasecmp($adddata['Addressbook']['email'] , $data) == 0 ){
                        // 共通アドレス帳に登録の場合削除する
                        $this->loadmodel('Addressbook');
                        $rst = $this->Addressbook->delete($adddata['Addressbook']['id']);
                        // AddressbooksAddressgroupからも削除する
                        $this->loadmodel('AddressbooksAddressgroup');
                        $this->AddressbooksAddressgroup->deleteAll(array('AddressbooksAddressgroup.addressbook_id' => $adddata['Addressbook']['id']), false);
                    }
                }

            }
		} catch (Exception $e){
            $this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
        return $rst;
    }
}
