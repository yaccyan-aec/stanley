<?php
App::uses('AppModel', 'Model');

/**
 * Uploadfile Model
 *
 * @property Content $Content
 * @property Syslog $Syslog
 */
class Uploadfile extends AppModel {

	var $_targetDir;
	var $_seed = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	var $_seedlen;
/**
 * __construct
 * @todo 	コンストラクタ　（初期値設定）
 * @return   void
 */
	function __construct($id = false, $table = null, $ds = null){
		parent::__construct($id,$table,$ds);
		$this->_targetDir =  Configure::read('Upfile.dir');
		$this->_seed =  Configure::read('Upfile.seed');
		$this->_seedlen =  strlen($this->_seed) -1;
		if(CakePlugin::loaded('Vscan')){
			$this->Behaviors->load('Vscan.Vscan');
		}
	}


/**
 * vscan
 * Vscan プラグインがロードされていたらコマンドを発行してウイルススキャンを行う
 * ないときはすべて「検査せず」で通す
 */
	public function vscan($file,$flg = 'off'){
		try{
			if(CakePlugin::loaded('Vscan')){
				$rc = $this->do_scan($file,$flg);
				return $rc;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return -1;
	}
/**
 * Use behavior
 *
 * 削除フラグ（tinyint) => 削除日(datetime) のフィールド名カスタマイズ
 * デフォルトは 'deleted' => 'deleted_date'
 */
//	var $actsAs = array('SoftDelete' => array(
//			'is_deleted' => 'deleted',
//		));
// 削除するときだけ使用する
//	var $actsAsConfig = array('SoftDelete' => array(
//			'is_deleted' => 'deleted',
//		));

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
	var $sanitizeItems = array(	'name' => true,
								'fname' => true,
								'fext' => true,
								'path' => false,
								);

/**
 * Use database config
 *
 * @var string
 */
//	public $useDbConfig = 'default';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
	);
/**
 * validationSets set
 * @todo Multivalidatable ビヘイビアを使用する
 * @var array
 */
	var $validationSets = array(
		'admin' => array(
		),
		'precheck' => array(
			'name' => array(
				'notBlank' => array(
						'rule' => array('notBlank'),
						'last' => false,
						'message' => 'The blank is not goodness.'
				),
			),
			'size' => array(
				'range' => array(
					'rule' => array('range', 0 , VALUE_file_size_limit),
					'last' => false,
					'message' => 'File size is too large.',
				),
			),
		),
		'register' => array(
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
			'name' => array(
				'notBlank' => array(
						'rule' => array('notBlank'),
						'last' => false,
						'message' => 'The blank is not goodness.'
				),
			),
			'size' => array(
				'range' => array(
					'rule' => array('range', 0 , VALUE_file_size_limit),
					'last' => false,
					'message' => 'File size is too large.',
				),
			),
		),
 		'update' => array(
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
			'name' => array(
				'notBlank' => array(
						'rule' => array('notBlank'),
						'last' => false,
						'message' => 'The blank is not goodness.'
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
		'Content' => array(
			'className' => 'Content',
			'foreignKey' => 'content_id',
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
	);

/**
* function beforeValidate
* @brief ファイル名と拡張子を分割して保存（for PSCAN）
*
*/
	function beforeValidate($options = array()) {
		try{
$this->log('upload 	beforeValidate start');
//$this->log($this->data);
			if(isset($this->data[$this->name]['id'])){
$this->log('============== 1 ID があったらupdate');
			} else {
$this->log('============== 2　新規登録');
				$_name = new SplFileInfo($this->data[$this->name]['name']);
		//$this->log(__FILE__.':'.__LINE__.' beforeValidate start['.$_name.']');
		//$this->log($this->data[$this->name]	);
				if( isset($this->data[$this->name]['fext']) ) {
				} else {
					$this->data[$this->name]['fext'] = $_name->getExtension();
				}
				if( isset($this->data[$this->name]['fname']) ) {
				} else {
					// getBasename はファイル名の頭が欠けるため使用しない
					$this->data[$this->name]['fname'] =
						preg_replace("/\.".$this->data[$this->name]['fext']."$/m", "" , $this->data[$this->name]['name']);
				}
			}

			// 存在チェックは after find に移動　2015/02/12

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		parent::beforeValidate($options);
	}

/**
 * afterFind
 * @todo find後の処理（空白を作らないために補完）
 * @param    array $results
 * @return   array
 */
	function afterFind($results) {
        $rs = parent::afterFind($results);
		foreach ((array)$rs as $idx => $rec) {
			if(is_numeric($idx)){
				foreach((array)$rec[$this->name] as $colName => $val){
						switch( $colName ) {
							case 'path':
//$this->log('file 存在チェック start['.$val.']');
								if($this->isFileExists($val)){
//$this->log('file ありました ['.$val.']');
								} else {
$this->log('file なかった['.$val.']');
									$rs[$idx][$this->name]['is_deleted'] = 1;
								}
								break;
							default:
								break;
						}
				}
			} else {
			}
		}
		return $rs;
	}

	// 存在チェックnew password が現在のものと等しくないことをチェック
//    function new_pwd_not_same(){
//		if($this->data[$this->name]['pwd'] === $this->data[$this->name]['new_password']){
//$this->log('verify NG');
//			return false;
//		}
//$this->log('verify OK');
//       return true;
//	}

/**
 * savefile
 * @todo アップロードしたファイルを別名でセーブ
 * @param array $data : リクエストデータ
 * @var array
 */
	public function savefile($data){
$this->log('savefile');
//$this->log($data);
		$num = 0;
		$newary = array();

		/**
		* 以前にアップロードして持ちまわってきたもの (file_upload_default.ctp 使用)
		*/
		if(isset($data[$this->name])){
			foreach($data[$this->name] as $updata){
				$newary[$num] = $updata;
				$num++;
			}
		}

		/**
		* 通常のアップロードで入ってきたもの (file_upload.ctp　,file_upload_jq 使用)
		*/
		$has_vchk = Hash::get($data, 'Content.opt_avs');
$this->log('has_vchk['.$has_vchk.']');
		if(isset($data['Content'][$this->name])){
			foreach($data['Content'][$this->name] as $key => $val){
				$nval = $this->_saveFile($val, $num, $has_vchk);
				if(is_null($nval)){
					continue;
				}
				$newval = $this->makeMyData($nval);
				$newary[$num++] = $newval;
			}
		}
		/**
		* ドラッグ＆ドロップなどでアップロードしたもの (file_upload_jq2 休止中)
		* (ハッシュ構造が若干異なるため別に処理)
		*/
		if(isset($data['Upload'])){
			foreach($data['Upload']['files'] as $key => $val){
				$nval = $this->_saveFile($val, $num, $has_vchk);
				if(is_null($nval)){
					continue;
				}
				$newval = $this->makeMyData($nval);
				$newary[$num++] = $newval;
			}
			unset($data['Upload']);
		}
		/**
		* SWF アップロードで入ってきたもの　(file_upload_jq3)
		* (ハッシュ構造が若干異なるため別に処理)
		*/
		if(isset($data['Content']['Upload'])){
			foreach($data['Content']['Upload'] as $key => $val){
				// もうセーブは済んでいる
				$newval = $this->makeMyData($val,$has_vchk);
				$newary[$num++] = $newval;
			}
			unset($data['Content']['Upload']);
		}
		if(isset($newary)){
			$data['Content'][$this->name] = $newary;
		}
		return $data;
	}
/**
 * _saveFile
 * @todo 登録用に整形する（細かいオプションはあとで）
 * @param    array $ary : 1件分の情報
 * @param	 int 	$num : 枝番
 * @param	 int	$scan : ウイルススキャン（VSCANプラグインがあるときはチェック）
 * @return   array #newdata : 整形後のデータ
 */
	public function _saveFile($file,$num = 0,$vchk = 'off'){
//debug('_saveFile');
//debug($file);
		$newdata = $file;
		try{
			if($newdata['size'] > 0){
				/**
				* 格納先フォルダが存在しなかったら作る
				*/
				if (!is_dir($this->_targetDir)) {
					mkdir($this->_targetDir, 0777, true);
				}

				/**
				* ユニークな名前を作成して移動
				*/
				$name = $this->mkName($num);
				$tmppath = $newdata['tmp_name'];
				$newpath = $this->_targetDir . DS .  $name;
				$rc = move_uploaded_file($tmppath , $newpath);
				if($rc){
					$newdata['path'] = $name;
					// 一応ウイルスチェックを通しておく
//					if($vchk == 'on'){
						$newdata['avs_result'] = $this->vscan($name,$vchk);
//					}
					// 保存成功
				} else {
					// 保存失敗
$this->log(__FILE__ .':'. __LINE__ .': upload 保存失敗：'. print_r($file,true));
					$newdata['error'] = UPLOAD_ERR_CANT_WRITE;
				}
			} else {
				// size = 0
				if(empty($newdata['name']) && $newdata['error'] != UPLOAD_ERR_NO_FILE){
                    // ファイルなし
                    return null;
                } else {
                    // 何らかのエラーがある
                    $this->log(__FILE__ .':'. __LINE__ .': upload 失敗：'. print_r($file,true));
                }
			}
			return $newdata;
		} catch (Exception $e){
			$newdata['error'] = UPLOAD_ERR_CANT_WRITE;
			return $newdata;
		}
	}


/**
 * makeMyData
 * @todo 登録用に整形する（細かいオプションはあとで）
 * @param    array $ary : 1件分の情報
 * @return   array #newdata : 整形後のデータ
 */
	public function makeMyData($ary = array(),$vchk = 'off'){
$this->log('makeMyData['.print_r($ary,true).']');
		if(empty($ary)) { return array(); }
		$newdata = $this->create($ary);
$this->log('------------------1');
		$_has_opt_tfg = CakePlugin::loaded('Tfg');
		if($_has_opt_tfg){
$this->log('TFG プラグインがある');
			$this->loadModel('Tfg.Tfg');
		}
		$mypath = Configure::read('Upfile.dir');
$this->log('mypath['.$mypath.']');
		$this->setValidation('precheck');
		if($ary['error'] == UPLOAD_ERR_OK){
			if($_has_opt_tfg){
$this->log('------------------2');
				// TFG プラグインあり
				$chk_tfg = $this->Tfg->get_enctype($mypath,$newdata[$this->name]['path']);
$this->log('------------------3 chk_tfg['.$chk_tfg.']');
				switch($chk_tfg){
					case 0: // 平文
				$newdata[$this->name]['enc_type'] = VALUE_Enctype_Dec;
				$newdata[$this->name]['dec_path'] = $newdata[$this->name]['path'];
				$newdata[$this->name]['dec_size'] = $newdata[$this->name]['size'];
						break;
					case 1: // TFG暗号化されている
				$newdata[$this->name]['enc_type'] = VALUE_Enctype_Enc;
				$newdata[$this->name]['enc_path'] = $newdata[$this->name]['path'];
				$newdata[$this->name]['enc_size'] = $newdata[$this->name]['size'];
						break;
					default: // エラー
				$newdata[$this->name]['enc_type'] = VALUE_Enctype_ERR;
				$newdata[$this->name]['dec_path'] = $newdata[$this->name]['path'];
				$newdata[$this->name]['dec_size'] = $newdata[$this->name]['size'];
						break;

				}
			} else {
				// TFG プラグインなし
				$newdata[$this->name]['enc_type'] = VALUE_Enctype_Dec;
				$newdata[$this->name]['dec_path'] = $newdata[$this->name]['path'];
				$newdata[$this->name]['dec_size'] = $newdata[$this->name]['size'];
			}
			$newdata[$this->name]['mime_type'] = $newdata[$this->name]['type'];
			/**
			* 基本的なバリデーションをチェックしておく
			*/
			$rc = $this->set($newdata);
			if($this->validates()){
				if($vchk == 'on'){
					$newdata[$this->name]['avs_result'] = $this->vscan($newdata[$this->name]['path']);
				}
			} else {
				$errors = $this->validationErrors;
				debug($errors);
				$newdata['error'] = -1;
				$newdata['valildationErrors'] = $errors;
			}

		} else {
		}
		return $newdata;
	}

/**
 * FileExists
 * @todo ファイル存在チェック（ないときは情報を削除）
 * @param    array $ary : ファイル情報
 * @return   array $rtnary : 整形後のデータ
 */
	public function FileExists($ary = array()){
//debug('isFileExists');
		if(empty($ary)) { return array();}
		$rtnary = array();
		foreach($ary as $k => $v){
			if(isset($v[$this->name]['path'])){
				if($this->isFileExists($v[$this->name]['path'])){
//debug('まだある['.$fpath.']');
					$rtnary[] = $v;
				} else {
//debug('もうない['.$fpath.']');
				}
			}
		}
//debug($rtnary);
		return $rtnary;
	}

/**
 * FileExistsSetFlg
 * @todo ファイル存在チェック（ないときはフラグをセット）
 * @param    array $ary : ファイル情報
 * @return   array $rtnary : 整形後のデータ
 */
	public function FileExistsSetFlg($ary = array()){
		try{
			if(empty($ary)) { return $ary;}
			$rtnary = array();
			foreach($ary as $k => $v){
				if(isset($v[$this->name]['path'])){
					if($this->isFileExists($v[$this->name]['path'])){
						$rtnary[] = $v;
					} else {
						$v[$this->name]['is_deleted'] = 1;
						$v[$this->name]['deleted'] = $this->now();

						$rtnary[] = $v;
					}
				}
			}
	//debug($rtnary);
			return $rtnary;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $ary;
	}

/**
 * isFileExists
 * @todo ファイル存在チェック
 * @param    string $path : ファイル名
 * @return   boolean
 */
	public function isFileExists($path = null){
		try{
			$fpath = $this->_targetDir .'/'. $path;
			$file = new File($fpath);
			if($file->exists()){
				return true;
			}
//debug($rtnary);
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}


/**
 * countUp
 * @todo ダウンロードカウンタのカウントアップ
 * @param  int : id
 * @return int : count
 */
	public function countUp( $id = 0 ){
		try{
/* いま見ているDB dbconfig を調べる(for debug)
	$db = ConnectionManager::getDataSource($this->useDbConfig);
	debug($db->config['database']);
*/
			if($id == 0) return -1;
			$this->recursive = -1;
			$data = $this->findById($id);
//$this->log('countup data start ====id['.$id.']');
//$this->log($data);
			$count = $data[$this->name]['dl_cnt'];
			$count++;


			$this->save(array(
						'id' => $id,
						'dl_cnt' => $count
						));
			$data = $this->findById($id);
//$this->log('countup data end ====id['.$id.']');
//$this->log($data);
			return $count;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. $e->getMessage());
			return -1;
		}
	}

/**
 * mkName
 * @todo アップロードしたファイルの別名を作成
 * @param int $num : 連番
 * @param string $prefix : 接頭語
 * @var string :　別名
 */
	public function mkName( $num = 0 ,$prefix = 'F_'){
		// ランダムな名前
		$random = $this->mktmpname(16);

		// 念には念を入れてユニークに
		$mtm = (double) microtime () * 1000000;
		$name = sprintf("%s%08d%s_%d", $prefix,$mtm, $random, $num);

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
 * getZPwd
 * zip パスワードを求める
 * @return array
 */
	public function getZPwd($cid = null){
		$zpwd = null;
		try{
			if(CakePlugin::loaded('Encrypt')){
				$_upload = $this->find('all',array(
					'conditions' => array('content_id' => $cid,
											'path LIKE' => "Z_%"),
					'recursive' => -1,
					));
				if(!empty($_upload)){
					$zpwd = $_upload[0][$this->name]['zpass'];
				}
			}
		} catch (Exception $e){
			$zpwd = null;
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $zpwd;
	}

/**
 * set_dl_mod
 * dl_mod セット
 * @return array
 */
	public function set_dl_mod($id = null,$dl_mod = VALUE_dl_mod_OK){
		try{
			if($this->exists($id)){
$this->log('Uploadfile.set_dl_mod id['.$id.'] mod['.$dl_mod.']',LOG_DEBUG);
				$this->recursive = -1;
				$data = $this->findById($id);

				$this->setValidation('default');

				$update = array();
				$update['id'] = $id;
				$update['dl_mod'] = $dl_mod;
				$update['modified'] = null;
				return(parent::save($update));
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}


/*************************************************************
 * cleanup で使用する関数群
 *
*************************************************************/
/**
 * getFileListFromDb
 * @todo DBから現在有効になっているファイル名を取得
 * クリーンアップのときは、事前に以下の処理を行っておくこと
 *	$list = $this->Content->getExpirationList($one);
 *	$this->Content->setExpFlg($list);
 *
 * @var array : ファイル名リスト
 */
	function getDeleteFileListFromDb($list = array()){
		try{
			if(count($list) > 0){
			$ids = Hash::combine($list,'{n}.Content.id','{n}.Content.id');
			$conditions = array(
				'Content.id' => $ids ,
				'Uploadfile.is_deleted' =>  false,
			);
			$found = $this->find('list',array(
						'fields' => array(
							'path', 'Content.id'
						),
						'conditions' => $conditions ,
						'recursive' => 0
					));
			return $found;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}

/**
 * getFileListFromDb
 * @todo DBから現在有効になっているファイル名を取得
 * クリーンアップのときは、事前に以下の処理を行っておくこと
 *	$list = $this->Content->getExpirationList($one);
 *	$this->Content->setExpFlg($list);
 *
 * @var array : ファイル名リスト
 */
	function getFileListFromDb(){
		try{
			$conditions = array(
				'Content.is_expdate' => 'N',
				'Content.is_deleted' =>  false,
			);
			$list = $this->find('list',array(
						'fields' => array(
							'path', 'Content.id'
						),
						'conditions' => $conditions ,
						'recursive' => 0
					));
			return $list;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}
/**
 * getFileListFromDir
 * @todo 実ディレクトリから現在有効になっているファイル名を取得
 * @param date $date : 日付（null のときは現在）
 * @var array : ファイル名リスト
 */
	function getFileListFromDir(){
		$dirpass = Configure::read('Upfile.dir');
		try{
			$dirhandle = opendir( $dirpass );
			if ( ! ereg("/$", $dirpass) ){
				$dirpass = $dirpass.'/';
			}
			$list = array();

			while( $filename = readdir( $dirhandle ) ){
				if( is_dir( $dirpass.$filename ) ){
					// ﾃﾞｨﾚｸﾄﾘはｽｷｯﾌﾟ
					continue;
				}
				$fn = $dirpass.$filename;
				if(file_exists($fn)){
					$updateDate = date('Y-m-d',filemtime($fn));
					$list[$filename] = $updateDate;
				}
			}
			@closedir( $dirpass );
			return $list;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		@closedir( $dirpass );
		return null;
	}

/**
 * getUunlinkFiles
 * @todo DB と 実ディレクトリからのリストを元に本当に消すファイルのリストを作る
 * @param array $dellist : 期限切れになって消すリスト
 * @param array $alivelist : のこすリスト
 * @param array $dirlist :   現在あるファイル
 * @param date  $date : 期限（デフォルトは前日）
 * @var array : $消してよいファイル名リスト
 */
	public function getUunlinkFiles(
		$dellist = array(),
		$alivelist = array(),
		$dirlist = array(),
		$date = null)
	{
		try{
			// ファイルの日付チェック用
			$today = ($date == null) ? $this->today() : $date;
			$yesterday = date('Y-m-d', strtotime($today . " yesterday"));
			$list = array();

			$dirpass = Configure::read('Upfile.dir');
			$msg = "[filename]\t[content_id/uploaddate]\t[remaining/deleting]\t[result]";
			$this->out($msg,1,1);
			foreach($dirlist as $fn => $dt){
				$name = $fn;
				$p = strpos($fn , '.');
				if($p){
					// 拡張子があったら取る
					$name = strstr($fn, '.' , true);
				}

				// 有効リストに入っているときは残す
				if(isset($alivelist[$name])){
					$msg = $fn . "\t" . $alivelist[$name] ."\tremaining\t***";
					$this->out($msg,1,1);
					continue;
				}

				// 今回期限切れ扱いにしたのは新しくても消す
				if(isset($dellist[$name])){
					$msg = $fn . "\t" . $dellist[$name] ."\tdeleting\t";
					if($this->unlinkFile($dirpass.'/'.$fn , $name)){
						$msg .= "success.";
						$list[$fn] = 'success';
					} else {
						$msg .= "failed.";
						$list[$fn] = 'failed';
					}
					$this->out($msg,1,1);
					continue;
				}

				// 有効リストにはないが新しいので取っておく
				if($dt >= $yesterday){
					$msg = $fn . "\t" . $dt ."\tremaining\t[new]";
					$this->out($msg,1,1);
					continue;
				}

				// ここまできたら削除実行
				if($this->unlinkFile($dirpass.'/'.$fn , $name)){
					$msg = $fn . "\t" . $dt ."\tdeleting\tsuccess.";
					$list[$fn] = 'success';
				} else {
					$msg = $fn . "\t" . $dt ."\tdeleting\tfailed.";
					$list[$fn] = 'failed';
				}
				$this->out($msg,1,1);
			}
			return $list;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}

/**
 * unlinkFile
 * @todo ファイルを削除し、DB にもフラグを立てる
 * デバッグモードのときは消さない
 * @param string $fullpath : 削除するファイルパス
 * @param string $path   : 拡張子のないファイル名
 * @param bool $honban : false のときは消さない
 * @var bool : 結果
 */
	public function unlinkFile($fullpath = null, $path = null, $honban = true){
		try{

			$result = true;
			if($honban){
				$result = unlink( $fullpath );
			}
			if($result){
				$find = $this->find('first',array(
						'conditions' => array('path' => $path),
						'recursive' => -1
					));
				if(isset($find[$this->name])){
					$id = $find[$this->name]['id'];
					if(!empty($find)){
						$this->softdelete($id);
					}
				}
			}
			return $result;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

/**
 * getTargetPath
 * @todo アップロードパスを返す
 * @return string :  アップロードパス
 */
	public function getTargetPath() {
		return $this->_targetDir;
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
$this->log('uploadfile setStatus start qid['.$id.'] code['.$code.']');
			if($id == null) return false;
			parent::save(array(
						'id' => $id,
						'enc_result' => $code,
						'modified' => null,
						));
			return true;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

/**
 * delete
 * @todo 削除するときだけ SoftDelete ビヘイビアを使用する
 * デバッグモードのときは消さない
 * @param string $id : 削除するID
 * @return bool : 結果
 */
	public function softdelete($id = null, $cascade = true) {
		try{
			if ($this->exists($id)) {
				parent::save(array(
					'id' => $id,
					'is_deleted' => true,
					'deleted' => $this->now(),
				));
				return true;
			}
			return false;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

/**
 * findFromCid
 * content_id から当該ファイルを求める
 */
	public function findFromCid($cid = null){
		try{
			if($cid == null) return array();
			$this->recursive = -1;
			$_cond = array( 'conditions' =>
					array(	'Uploadfile.content_id' => $cid )
							);
			$udata = $this->find('all',$_cond);
			return ($udata);

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}

}
