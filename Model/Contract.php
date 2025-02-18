<?php
App::uses('AppModel', 'Model');
//App::uses('User', 'Model');
/**
 * Contract Model
 *
 * @property Contract $Contract
*/

class Contract extends AppModel {

// 	var $name = 'Contract';

	// 削除フラグ（tinyint) => 削除日(datetime) のフィールド名カスタマイズ
	// デフォルトは 'deleted' => 'deleted_date'
    var $actsAs = array('SoftDelete' => array(
			'is_deleted' => 'deleted',
	));

	/**
	 * ここに書いてもダメみたいなので仕方なくapp 直下のモデルに入れる
	 */
	var $order = array( 'Contract.modified'	=>	'desc' );

//	var $virtualFields = array(
//			'user_count' => 0,
//		);

/**
 * sanitizeItems
 * 		sanitize したい項目を定義すると、appModel で自動的にやってくれる。
 * @var array : フィールド名 => html (true = タグを削除 / false = タグをエスケープ)
 * ここに書いてもダメみたいなので仕方なくapp 直下のモデルに入れる
 *
 */
	var $sanitizeItems = array(
								'name' => array('html' => true, ),
								'name_jpn' => array('html' => true, ),
								'name_eng' => array('html' => true, ),
								'etc' => array('html' => true, ),
								'logo' => array('html' => true, ),
//								'uri' => array('html' => true),	// 有効にするとバリデーションに引っかかるので無効にする
								'theme' => array('html' => true),
								);

	var $_targetDir;
	var $_seed = 'abcdefghijklmnopqrstuvwxyz0123456789';
	var $_seedlen;
/**
 * __construct
 * @todo 	コンストラクタ　（初期値設定）
 * @return   void
 */

	function __construct($id = false, $table = null, $ds = null){
		parent::__construct($id,$table,$ds);
		$this->_targetDir =  WEB_ROOT . DS . Router::url('/') . 'app/webroot/img/logo';
		$this->_seedlen =  strlen($this->_seed) -1;
	}

/**
 * Validation rules
 *
 * @var array
 */
	var $validate = array(
	);

	/*
	 * その他のバリデーションルール
	 * ここに書いてもダメみたいなので仕方なくapp 直下のモデルに入れる
	 */
	var $validationSets = 	array(
			'test' => array(
				'name' => array(
					'test' => array(
						'rule' => array('test'),
						'last' => true,
						'message' => 'The blank is not goodness.'
					),
				),
			),
			'default' => array(
				'id' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						'last' => true,
						'on' => 'update',
						'message' => array('Invalid %s .','ID'),
					),
				),
				'name' => array(
					'notBlank' => array(
						'rule' => array('notBlank'),
						'last' => true,
						'message' => 'The blank is not goodness.'
					),
					'isUnique' => array(
						'rule' => array('isUnique'),
						'last' => true,
						'message' => array('This %s is duplicated.','Name'),
					),
					'maxStringLength' => array(
						'rule' => array('maxStringLength', MAX_STRLEN),
						'last' => true,
						'message' => array('%s characters too long.','Name')
					),
				),
				'name_jpn' => array(
					'maxStringLength' => array(
						'rule' => array('maxStringLength', MAX_STRLEN),
						'allowEmpty' => true,
						'last' => true,
						'message' => array('%s characters too long.','Name_Jpn')
					),
				),
				'name_eng' => array(
					'maxStringLength' => array(
						'rule' => array('maxStringLength', MAX_STRLEN),
						'allowEmpty' => true,
						'last' => true,
						'message' => array('%s characters too long.','Name_Eng')
					),
				),

				'logo' => array(
//					'logo_extension' => array(
//						'rule' => array('logo_extension'),
//						'allowEmpty' => true,
//						'last' => false,
//						'message' => array('拡張子エラー１','Logo'),
//					),
                    // 拡張子のチェック　とりあえず gif,jpeg,png,jpg のいずれかのみ
					'extention' => array(
						// システムデフォルトは array('gif','jpeg','png','jpg')
						'rule' => array('extension'),
						'allowEmpty' => true,
						'last' => true,
						'message' => 'The extension is not image.',
					),
					'logo_size' => array(
                        // ファイルサイズのチェック（constdef.php で指定している）
						'rule' => array('logo_size',VALUE_Contracts_Logo_Size),
						'allowEmpty' => true,
						'last' => true,
						'message' => array('The size of the file has to be less than %s.', VALUE_Contracts_Logo_Size),
					),
				),
				'uri' => array(
					'url' => array(
						'rule' => array('url', true),
						'allowEmpty' => true,
						'last' => true,
						'message' => 'The format is not goodness.'
					),
				),
				'usernum' => array(
					'chkRange' => array(
						'rule' => array('chkRange',VALUE_Contracts_Size_Usernum_Min,VALUE_Contracts_Size_Usernum_Max),
						'last' => true,
						'message' => array('Please enter a number between %d and %d .', VALUE_Contracts_Size_Usernum_Min, VALUE_Contracts_Size_Usernum_Max),
					),
					'naturalNumber' => array(
						'rule' => array('naturalNumber'),
						'last' => true,
						'message' => array('Please supply the number of %s.','User Number'),
					),
					'minNumber' => array(
						'rule' => array('minNumber'),
						'last' => true,
						'message' => 'Less than the number of registration.',
					),
				),
				'size' => array(
					'chkRange' => array(
						'rule' => array('chkRange',VALUE_Contracts_Size_Disk_Min,VALUE_Contracts_Size_Disk_Max),
						'last' => true,
						'message' => array('Please enter a number between %d and %d .', VALUE_Contracts_Size_Disk_Min, VALUE_Contracts_Size_Disk_Max),
					),
					'naturalNumber' => array(
						'rule' => array('naturalNumber'),
						'last' => true,
						'message' => array('Please supply the number of %s.','disk size(GB)'),
					),
					// 'range' => array(
						// 'rule' => array('range',0,101),
						// 'last' => true,
						// 'message' => array('Please enter a number between %d and %d .',1,100),
					// ),
				),
				'time_limit' => array(
					'naturalNumber' => array(
						'rule' => array('naturalNumber'),
						'last' => true,
						'message' => array('Please supply the number of %s.','time limit days'),
					),
					'chkRange' => array(
						'rule' => array('chkRange',VALUE_Contracts_Size_TimeLimit_Min,VALUE_Contracts_Size_TimeLimit_Max),
						'last' => true,
						'message' => array('Please enter a number between %d and %d .', VALUE_Contracts_Size_TimeLimit_Min, VALUE_Contracts_Size_TimeLimit_Max),
					),
					// 'range' => array(
						// 'rule' => array('range',0,101),
						// 'last' => true,
						// 'message' => array('Please enter a number between %d and %d .',1,100),
					// ),
				),
				'approval_limit' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						'last' => true,
						'message' => array('Please supply the number of %s.','approval limit days'),
					),
					'chkRange' => array(
						'rule' => array('chkRange',VALUE_Contracts_Size_ApprovalLimit_Min,VALUE_Contracts_Size_ApprovalLimit_Max),
						'last' => true,
						'message' => array('Please enter a number between %d and %d .', VALUE_Contracts_Size_ApprovalLimit_Min, VALUE_Contracts_Size_ApprovalLimit_Max),
					),
					// 'range' => array(
						// 'rule' => array('range',0,101),
						// 'last' => true,
						// 'message' => array('Please enter a number between %d and %d .',1,100),
					// ),
				),
    			'expdate' => array(
    				'date' => array(
    					'rule' => array('date'),
    					'allowEmpty' => true,
    					'last' => true,
    					'message' => 'Please enter in the valid date format.',
    				),
    			),
			),
		);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
	);


/**
* func minNumber
* @brief empry ユーザ数が現在登録されている人数を下回らないことをチェック
* @param array $data :
* @retval boolean $ret :
*/
	function minNumber($data){
		try{
//$this->log($data);
$this->log('usernum['.$data['usernum'].'] user_count['.$this->data[$this->name]['user_count'].']');
			if($data['usernum'] >= $this->data[$this->name]['user_count']){
				return true;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}


/**
* func logo_extension
* @brief empry 拡張子チェック　（今は使っていないが、拡張子の種類を変えたくなった場合はつかうかも）
* @param array $data :
* @retval boolean $ret :
*/
   function logo_extension($data){
		try{
			if(empty($this->data[$this->name]['logofile'])){
				return true;
			}

			if(Validation::extension($this->data[$this->name]['logofile'][0])){
$this->log('拡張子 OK');
				return true;
			} else {
$this->log('拡張子 NG');
			}
			return false;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
        return false;
	}
/**
* func logo_size
* @brief empry ファイルサイズチェック
* @param array $data :
* @retval boolean $ret :
*/
   function logo_size($data,$size){
		try{
			if(empty($this->data[$this->name]['logo'])){
				return true;
			}
            $path = WEB_ROOT. DS . MY_APP .'/app/webroot/img/logo/'.$this->data[$this->name]['logo'];

			if(Validation::fileSize($path , '<=', $size)){
				return true;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
        return false;
	}



/**
 * hasMany associations
 *　バーチャルフィールドにしたいときは必要みたいだけどやめたのでいらない
 * @var array
 */
	public $hasMany = array(
	);



/**
 * saveFiles
 * @todo テンポラリにあるアップロードファイルをセーブする（セッションが切れるとなくなってしまうので）
 * @param    array $ary : リクエストデータ
 * @return   array #newdata : 整形後のデータ
 */
	function saveFiles($req_data = array()) {
		try{
			$data = $req_data[$this->name]['logofile'];
			if(is_array($data)){

				if($data[0]['error'] === 0){
					$rc = $this->_saveLogo($data);
					if($rc[0]['error'] === 0){
						$req_data[$this->name]['logo'] = $rc[0]['path'];
					}
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $req_data;
	}
/**
 * _saveFile
 * @todo 登録用に整形する（細かいオプションはあとで）
 * @param    array $ary : 1件分の情報
 * @return   array #newdata : 整形後のデータ
 */
	public function _saveLogo($file = array()){
		$newdata = $file;
		try{
			if($newdata[0]['size'] > 0){
				/**
				* 格納先フォルダが存在しなかったら作る
				*/
				if (!is_dir($this->_targetDir)) {
					mkdir($this->_targetDir, 0777, true);
				}

				/**
				* ユニークな名前を作成して移動
				*/
				$ext = '.' . pathinfo($newdata[0]['name'],PATHINFO_EXTENSION);
				$name = $this->mkName(6,'l_',$ext );
				$tmppath = $newdata[0]['tmp_name'];
				$newpath = $this->_targetDir . DS .  $name;
				$rc = move_uploaded_file($tmppath , $newpath);
				if($rc){
					$newdata[0]['path'] = $name;
					// 保存成功
				} else {
					// 保存失敗
					$newdata[0]['error'] = UPLOAD_ERR_CANT_WRITE;
				}
			} else {
				// size = 0
				return null;;
			}
			return $newdata;
		} catch (Exception $e){
			$newdata[0]['error'] = UPLOAD_ERR_CANT_WRITE;
			return $newdata;
		}
	}
/**
 * mkName
 * @todo アップロードしたファイルの別名を作成
 * @param int $num : 連番
 * @param string $prefix : 接頭語
 * @var string :　別名
 */
	public function mkName( $mojisu = 6 ,$prefix = 'logo' , $suffix = ''){
		// ランダムな名前
		$random = $this->mktmpname($mojisu);

		// 念には念を入れてユニークに
		$mtm = (double) microtime () * 1000000;
		$name = sprintf("%s%s%s", $prefix, $random, $suffix);

		return $name;
	}
/**
 * mktmpname
 * @todo ランダム文字列生成
 * @param int $mojisu : 文字数
 * @var string : ランダム文字列
 */

	function mktmpname($mojisu = 16){
		$len = $mojisu;
		srand ( (double) microtime () * 1000000);
		$pass = "";
		while ($len--) {
			$pos = rand(0,$this->_seedlen);
			$pass .= $this->_seed[$pos];
		}
//		$this->log("mktmpname called. [$pass]");
		return $pass;
	}

/**
 * setUser
 * @todo ユーザ反映（細かいオプションはあとで）
 * @param    array $ary : 1件分の情報
 * @return   array #newdata : 整形後のデータ
 */
	public function setUser($cid = null, $data = array()){
		try{
			$this->loadModel('User');
			$this->User->recursive = -1;

			$conditions = array('User.contract_id' => $cid);
			$list = $this->User->find('list',array('conditions' => $conditions));

			$rc = $this->User->setExpdate(array_keys($list),$data);
			return $rc;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

/**
 * getContractList
 * @todo ユーザ編集に使用する契約リスト取得（管理者の場合は自分だけ）
 * @param    array $me : ログインユーザ
 * @return   array #newdata : グループリスト
 */
	function getContractList($cid = null){
		try{
			$lang = $this->getLang();
			$this->recursive = -1;
            $cond = array();
            if($cid > 0){
                $cond = array('id' => $cid);
            }
			$data = $this->find('all',array(
                'conditions' => $cond,
				'fields' => array('id','name','name_jpn','name_eng','sortorder'),
				'order' => array('sortorder' => 'asc',
								'id' => 'asc')
								));
			$list = array();
			foreach($data as $k => $v){
				$id = $v[$this->name]['id'];
				$name_lang = $v[$this->name][$this->makeFieldName('name','name_')];
				$name = empty($name_lang) ? $v[$this->name]['name'] : $name_lang;
				$list[$id] = $name;
			}
			return $list;

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}

/**
 * getContractList
 * @todo ユーザ編集に使用する契約リスト取得（管理者の場合は自分だけ）
 * @param    array $me : ログインユーザ
 * @return   array #newdata : グループリスト
 */
	function save($data){
		try{
			$this->recursive = -1;
			if(isset($data[$this->name]['is_trial'])){
				$is_trial = $data[$this->name]['is_trial'];
				$data[$this->name]['is_trial'] =
					($is_trial === VALUE_Contracts_Type_Regular) ? $is_trial : VALUE_Contracts_Type_Trial;
			}
			$result = parent::save($data);
			$addressgroup_id = $this->setAddressGroup($result);
			if($addressgroup_id > 0){
				if($addressgroup_id == @$result[$this->name]['addressgroup_id']){
//$this->log('--- リンクもOK');
				} else {
					// root だったけど リンクがへんなときは直す
//$this->log('--- リンクを張り直し');
					$result[$this->name]['addressgroup_id'] = $addressgroup_id;
					$result = parent::save($result);
				}
			}
			return $result;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}

/**
 * setUser
 * @todo ユーザ反映（細かいオプションはあとで）
 * @param    array $ary : 1件分の情報
 * @return   array #newdata : 整形後のデータ
 */
	public function setAddressGroup($data = null){
		$myData = array();
		try{
			if(is_array($data)){
				// array ならば save 直後に来ている
				$myData = $data;
			} else {
				// id ならば取り出す
				if($this->exists($data)){
					$this->recursive = -1;
//					削除されたものもほしいとき↓
//					$this->enableSoftDeletable(false);
					$myData = $this->findById($data);
				} else {
					// IDが存在しなければ終了
					return -1;
				}
			}
			$myId = $myData[$this->name]['id'];
			// 整合性をチェック
			$_ag = 'Addressgroup';
			$this->loadModel('Addressbooks.Addressgroup');

			/***************************************************/
			// バッチでインポートしたときに別のDBに登録しないため
			$_mydb = $this->getDataSource()->configKeyName;
			$_agdb = $this->{$_ag}->getDataSource()->configKeyName;
			if($_mydb != $_agdb){
				// 異なるDBを見ていたら合わせる
				$this->{$_ag}->setDataSource($_mydb);
			}
			$_agdb = $this->{$_ag}->getDataSource()->configKeyName;
			/***************************************************/

			$this->{$_ag}->recursive = -1;
			$ag_list = $this->{$_ag}->find('all',
				array( 'conditions' =>
					array( 'contract_id' => $myId,
							'user_id' => null
				)));
//debug($myData);
			// softDelete だと [is_deleted] や [deleted] は項目も出ないみたい
			if(!empty($myData[$this->name]['id'])){
				if(Hash::check($myData,'Contract.is_deleted')){
					if(Hash::get($myData,'Contract.is_deleted')){
						$this->log('--- 削除のときはこちら');
						return 0;
					}
				}
			$root_default_name = $myData[$this->name]['name'] . '_ROOT';
			foreach($ag_list as $k => $v){
				if($v[$_ag]['is_root'] == 'Y'){
					$root_group_id = $v[$_ag]['id'];
//$this->log('--- ROOT だった['.$root_group_id.']');
					return $root_group_id;
				} else {
					if($v[$_ag]['name'] == $root_default_name && $v[$_ag]['parent_id'] = null){
//$this->log('--- 名前がROOTデフォルトなので　root とみなす');
						$root_group_id = $v[$_ag]['id'];
						$v[$_ag]['is_root'] = 'Y';
						$this->{$_ag}->save($v);
//$this->log('--- 名前がROOTデフォルトなので　root とみなす['.$root_group_id.']');
						return $root_group_id;
					}
				}
			}

			// ここに来るときはたぶん登録されていない
//$this->log('--未登録なので登録');
			// 契約のROOT
			$root = $this->{$_ag}->create(
									array(	'name' => $root_default_name,
											'user_id' => null,
											'contract_id' => $myId,
											'is_root' => 'Y',
											'is_shared' => 'Y',
											));
			$result = $this->{$_ag}->save($root);
//$this->log($result);
			$root_id = $this->{$_ag}->getLastInsertID();
//$this->log('--- 登録終了['.$root_id.']');

			return $root_id;
			} else {
$this->log('--- 削除のときはこちら（ここ来ますか？）');
				return 0;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return -1;
	}

 /**
 * getAmount
 * @todo 	　契約単位の、現在の使用量（有効なファイルのみ）
 * @param    int	$contract_id
 * @return   int	有効なファイルの使用量（byte）
 */
	function getAmount($contract_id = 0){
		try{
$this->log("===== getAmount start![".$contract_id."]",LOG_DEBUG);
			if($contract_id == null){
$this->log("===== getAmount contract not found![".$contract_id."]",LOG_DEBUG);
				return 0;
			}
			// -------- 同じ契約のユーザを求める
			$this->loadModel('User');
			$_users = $this->User->find('list',
										array('conditions' =>
											 array('User.contract_id' => $contract_id)));
//$this->log("===== getAmount user->find list ============",LOG_DEBUG);
			$_user_id = array_keys($_users);
//$this->log("===== getAmount key ============",LOG_DEBUG);
//$this->log($_user_id,LOG_DEBUG);
			// -------- 却下したもの
			$this->loadModel('Approval');
			$app_cond = array(	'Approval.contract_id' => $contract_id,
								'Approval.aprv_stat' => VALUE_AprvStat_NG);
			$aprv_ng = $this->Approval->find('list',
					array(	'conditions' => $app_cond,
							'fields' => array('Approval.id','Approval.content_id'),
					));
//$this->log("===== getAmount aprv_ng list ============",LOG_DEBUG);
//$this->log($aprv_ng,LOG_DEBUG);

			// -------- 同じ契約のユーザが関係するコンテンツを求める
			$this->loadModel('Content');
			$this->Content->recursive = -1; //

			// 撤回は数えない　＆ 却下されたものも数えない
			$conditions = array("Content.owner_id" => $_user_id,
								"AND" =>
								array (	"Content.is_expdate" => 'N',
										"Content.is_deleted" => false,
										"NOT" => array('Content.id' => $aprv_ng),
										)
								);

//$this->log("===== getAmount conditions ============",LOG_DEBUG);
//$this->log($conditions,LOG_DEBUG);
			$_contents = $this->Content->find('list',
									array( 'conditions' => $conditions));

$this->log("===== getAmount contents ============",LOG_DEBUG);
$this->log($_contents,LOG_DEBUG);

			// -------- コンテンツに関連するファイルを求める
			$this->loadModel('Uploadfile');
			$this->Uploadfile->recursive = -1; //

			$_files = $this->Uploadfile->find('list',
			array(	'fields' => array('Uploadfile.path', 'Uploadfile.size'),
					'conditions' =>  array("Uploadfile.content_id" => array_keys($_contents))));

$this->log("===== getAmount contents ============",LOG_DEBUG);
$this->log($_files,LOG_DEBUG);
			$_sum = array_sum($_files);

$this->log("===== getRate end! sum[".$_sum."]",LOG_DEBUG);
			return $_sum;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return 0;
	}

	/* 残り日数を取得：2011.09.01
	 * 無期限のときは　-1
	 * それ以外のときは残り日数
	 */
	function get_remain_days($cid = null){
		$remaindays = -1;
		try{
			if(is_null($cid)){
				// IDがなければ無期限（契約縛りはなし）
				return -1;
			}
			if(!$this->exists($cid)){
				// 当該データが存在しなければ無期限（契約縛りはなし）
				return -1;
			}
			$contract = $this->find('first',array(
						'conditions' => array('id' => $cid),
						'recursive' => -1));
			$my_expdate = $contract['Contract']['expdate'];
			if(is_null($my_expdate)){
				// 日付指定がなければ無期限
				return -1;
			}
			$today = getdate();
			$today_i = mktime(0, 0, 0, $today['mon'],$today['mday'],$today['year']);

			$limit = date_parse($my_expdate);
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
			if($diff2 <= 0){	// 表示期間
				return $diffDay;
			} else {			// まだ表示しない
				return -1;
			}

			return $diffDay;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return -1; // 何かのエラーは無期限とする
	}

	/* 有効期限が変化するかどうかをチェック
	 * true ： 変更あり　→　ユーザ反映へ
	 * false: 変更なし　→　一覧へ
	 */
	function is_change_expdate($data = array()){
		try{
			if(Hash::check($data,'Contract.id')){
				$id = Hash::get($data,'Contract.id');
				if($this->exists($id)){
					$olddata = $this->find('first',array(
							'conditions' => array('Contract.id' => $id),
							'recursive' => -1,
						));
					$d_new = $data['Contract']['expdate'];
					$d_old = $olddata['Contract']['expdate'];
$this->log('new['.$d_new.'] old['.$d_old.']');
					if($this->cmpdate($d_new,$d_old) != 0){
						// 有効期限が変更されます
						return true;
					}
					if(empty($d_new)){
						// 空白だったら無期限なので
						if($d_old==''){
							// 変更なし
							return false;
						} else {
							// 日付が入っていたら変更あり
							$obj_old = new DateTime($d_old);
							$f_old = $obj_old->format('Y-m-d');
$this->log('new['.$d_new.'] old['.$f_old.']!!!');
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

}
