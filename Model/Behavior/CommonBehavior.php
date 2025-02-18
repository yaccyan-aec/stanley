<?php
App::uses('ModelBehavior', 'Model');
App::uses('CakeSession', 'Model/Datasource');

class CommonBehavior extends ModelBehavior {
	/**
     * 日付の比較
     *
     * @param Object $model
     * @param String $one : チェックしたい日付（null のときはチェックしない）
     * @param String $two : 基準の日付（null のときは現在日付）
     * 戻り値：1 -- 期限前			$one > $two
     *         0 -- 等しい			$one = $two
     *        -1 -- 期限切れ		$one < $two
     */
	function cmpdate( Model $model, $one , $two = null){
		if($one == null) return 0;
		$_one = $one;
//		$_two = ($two == null) ? $this->now($model) : $two;
		// now() を AppModel　に移したのでこうなる↓
		$_two = ($two == null) ? $model->now() : $two;

		$_one_ary = date_parse($_one);
		$_two_ary = date_parse($_two);

		$_one_fmt = sprintf('%04d%02d%02d',$_one_ary['year'],$_one_ary['month'],$_one_ary['day']);
		$_two_fmt = sprintf('%04d%02d%02d',$_two_ary['year'],$_two_ary['month'],$_two_ary['day']);


		// まれに　expdate が　'0000-00-00' だったときには null のときと同じ扱いに
		if($_one_fmt == '00000000') return(0);
		if($_one_fmt > $_two_fmt) return(1);
		if($_one_fmt == $_two_fmt) return(0);
		return(-1);
	}

	/* =============================================
	 * 日付型の値を YYYY-MM-DD の形にする
	 * =============================================
	 */
	function dateFormatAfterFind(Model $model,$dateString) {
		if(isset($dateString)){
			if($dateString == '1970-01-01 00:00:00'){
				return NULL;
			}
			return date('Y-m-d', strtotime($dateString));
		} else {
			return NULL;
		}
	}
	/*
	 * @author shirosawa
	 * its for 要望 #1965
	 */
	function getexpday(Model $model,$limit = null){
		$_limit = $model->limit;
		// オプションがあったら優先させる
		if($limit != null) $_limit = $limit;
		$exp_day = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + $_limit, date("y")));
		return($exp_day);
	}
	
	function getTerm(Model $model,$one,$two){
		// データ取得期間を表す日付文字列を生成
		$startDate = $one;
		$endDate = $two;
		$dispStartDate = $startDate["year"] . "-" . $startDate["month"] . "-" . $startDate["day"];
		// 2012.10.22:アクセスログの出力期間制限対応
		//$dispEndDate = date("Y-m-d", mktime(0,0,0, $endDate["month"], $endDate["day"]+1, $endDate["year"]));
		$tmpEndManth =  date("Y-m-d", mktime(0,0,0, $startDate["month"]+1, 0, $startDate["year"]));		//月末日を計算
		$endManth = explode('-', $tmpEndManth);
		$endDateDay = $endDate["day"]+1;
		// 画面で指定されたtoの日付が月末日をOverしているかチェック
		if( $endDate["day"] > $endManth['2'] ) {
			$endDateDay =  $endManth['2'] + 1;		// Overしている場合は、月末日+1 をtoの日付にする
		}
		$dispEndDate = date("Y-m-d", mktime(0,0,0, $startDate["month"], $endDateDay, $startDate["year"]));
		$rc = $this->cmpdate($model,$dispEndDate,$dispStartDate);
		if($rc < 0){
			// 逆だったときは月末まで
			return array('start' => $dispStartDate, 'end' => $tmpEndManth);
		} 
		return array('start' => $dispStartDate, 'end' => $dispEndDate);
	}


	function isExpdate(Model $model, $id){
		if($id == null) return(0);
		 $model->recursive = -1;
		
		$found = $model->findById($id);
		if(!empty($found)){
			return $this->cmpdate($model,$found[$model->name]['expdate']);
		}
		return 0;
	}
	
	/*
	 * getLang
	 * return String lang
	 */
	function getLang(Model $model){
		return(Configure::read('Config.language'));
	}
	/*
	 * getLang
	 * return String lang
	 */
	function setLang(Model $model,$lang = VALUE_System_Default_Lang){
		CakeSession::write('Config.language',$lang);
		Configure::write('Config.language',$lang);
		return;
	}

	/**
	* function writeLog
	* @brief syslog 書き込み
	* @param  mixed  $args  パラメータの内容を syslog テーブルに登録
	* @retval void
	*/
 	function writeLog(Model $model,$args){
$this->log("===== CommonBehavior writeLog --- start",LOG_DEBUG);
		try{
			$auth = CakeSession::read('auth');
			if(empty($auth)){
//$this->log($args,LOG_DEBUG);
				// auth が取れないときは shell なので、user_id があれば
				// そこから情報をとる
				if(Hash::check($args,'user_id')){
					$this->{'User'} = ClassRegistry::init('User');
					$user = $this->User->find('first',array(
						'conditions' => array('id' => Hash::get($args,'user_id')),
						'recursive' => -1));
					$auth = $user['User'];	
				}
			}
			$this->{'Eventlog'} = ClassRegistry::init('Eventlog');
			if($this->Eventlog->insertLog($args,$auth,CakeSession::id())){
	//$this->log("===== writeLog --- ok",LOG_DEBUG);
				return($this->Eventlog->getLastInsertID());
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
	$this->log("===== writeLog --- err",LOG_DEBUG);
			return null;
		}
	}
	/*
	 * makeFieldName
	 * @param String $default:ex. 'name_yomi' サポート外の言語が指定されたときに使用するフィールド
	 * @param String $prefix :ex. 'name_'  
	 * @param String $suffix :ex. '_name'
	 * @param String $lang   :ex  'jpn' 'eng' 現在の言語のときは null
	 * return $str lang
	 */
	function makeFieldName(Model $model, $default, $prefix = null, $suffix = null ,$lang = null ){
		$fieldname = $prefix;
		$lang = (is_null($lang)) ? $this->getLang($model) : $lang;
		switch($lang){
			case 'jpn':
			case 'eng':
				$fieldname .= $lang . $suffix;
				break;
			default:
				$fieldname = $default;
				break;
		}
		return($fieldname);
	}

/**
 * for Tree Behavior
 */

	function getChildren(Model $model, $id = null, $options = array('type' => 'all'), $recursive = -1){
		if(empty($id)) {
			return false;
		}
		// get current 
		$fields = array('id','lft','rght');
		$current = $model->read($fields,$id);
		if(!$current){
			return false;
		}

		// set options
		if(!empty($options['type'])){
			$type = $options['type'];
			unset($options['type']);
		} else {
			$type = 'all';
		}
		$model->recursive = $recursive;
		// get child node
		$options['conditions'][$model->name.'.lft BETWEEN ? AND ?'] = 
			array($current[$model->name]['lft'],$current[$model->name]['rght']);
		return($model->find($type ,$options));
	}

	function getParents(Model $model, $id = null, $options = array('type' => 'all'), $recursive = -1){
		if(empty($id)){
			return false;
		}

		// get current
		$fields = array('id','lft','rght');
		$current = $model->read($fields,$id);
		if(!$current){
			return false;
		}
		// set options
		if(!empty($options['type'])){
			$type = $options['type'];
			unset($options['type']);
		} else {
			$type = 'all';
		}

		$model->recursive = $recursive;
		// get parent node
		$options['conditions'][$model->name . '.lft <'] = $current[$model->name]['lft'];
		$options['conditions'][$model->name . '.rght >'] = $current[$model->name]['rght'];
		
		return($model->find($type ,$options));
	}
	/**
	 * mkrandamstring
	 * ランダム文字列生成（_mkrandamstring のラッパー）
	 * 呼び出し形式
	 * $this->Common->mkrandamstring(文字数);　（デフォルトはbootstrap.php にて）
	 * @param integer   $mojisu   文字列の長さ
	 * @param boolean   $flg      セキュリティ重視かどうか
	 *
	 * @return string   生成された文字列
	 *
	 * @see _mkrandamstring
	 */
	function mkrandamstring(Model $model,$mojisu = AUTOPWD_DEFAULT_LEN ,$flg = null ){
		if($flg == null){
			if ((defined('OPTION_PWD_Rule') && OPTION_PWD_Rule)) {	// セキュリティ重視のとき
				return $this->_mkrandamstring($model,$mojisu,OPTION_PWD_Rule);
			} else {
				return $this->_mkrandamstring($model,$mojisu,false);
			}
		} else {
			return $this->_mkrandamstring($model,$mojisu,$flg);
		}
	}

	/**
	 * _mkrandamstring
	 * ランダム文字列生成（mkrandamstring　から呼ばれる）
	 * セキュリティをチェックするときは、そのオプションに合わせてチェックする
	 * @param integer   $mojisu   文字列の長さ
	 * @param boolean   $chkflg   セキュリティ重視かどうか
	 *
	 * @return string   生成された文字列
	 *
	 * @see security_chk
	 */
	function _mkrandamstring(Model $model,$mojisu ,$chkflg){
		$len = $mojisu;
		srand ( (double) microtime () * 1000000);
		$seed = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
		$pass = "";

		/**
		 * @todo	セキュリティ要件と合致するまでやり直す
		 */
		while(1){
			$pass = '';
			for($cnt = $len ; $cnt > 0 ; $cnt--) {
				$pos = rand(0,61);
				$pass .= $seed[$pos];
			}
			if($chkflg){
				if($this->security_chk($model,$pass)){
					break;
				}
			} else {	// チェックをしない
				break;
			}
		}
//$this->log("mkrandamstring [$pass]",LOG_DEBUG);
		return $pass;
	}

	/**
	 * security_chk
	 * パスワードの安全性チェック　2010.11.8（_mkrandamstring　から呼ばれる）
	 * bootstrap.php の正規表現でチェック
	 * @param string    $pass     チェック対象文字列
	 *
	 * @return boolean   チェック結果
	 */
	function security_chk(Model $model,$pass){
		// 数字が入っているか
		try{
			
			$rule = $model->MySecurity->get_rule_item('all');	
$this->log('security_chk[');
$this->log($rule);
			if(!empty($rule)){
				// 全体のチェック　（不当な文字が含まれていないか）
				$match_ptn = "/^".$rule['reg']."+$/";
				if(!preg_match($match_ptn,$pass)){
$this->log('不当な文字が入っている['.$pass.']');
					return false;
				}
				// 必要な文字が入っているか
				foreach($rule['ary'] as $k => $ptn){
					$match_ptn = "/".$ptn."+/";
					if(!preg_match($match_ptn,$pass)){
$this->log('必要な文字がない['.$pass.']');
						return false;
					}
				}
				return true;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

/**
 * mktmpname
 * @todo ランダム文字列生成
 * @param int $mojisu : 文字数
 * @var string : ランダム文字列
 */	

	function mktmpname(Model $model,$mojisu = 16){
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
	 * getFieldData 
	 * @todo 	言語に対応するユーザ名を求める
	 * @param   array  $data : db で読み込んだ　array
	 * @param   string $p : mkFieldName の３つのパラメータ
	 *          $p[0] : デフォルトフィールド名　
	 *   		$p[1] :　接頭語
	 *   		$p[2] :　接尾語
	 *  
	 * @return  string $_str : 言語に対応した名前
	 */
	function getFieldData(Model $model,$data = null,$p = null){
		try{
			$_lang = $this->getLang($model);
			$_field = $this->makeFieldName($model,$p[0],$p[1],$p[2],$_lang);
			$_str = Hash::get($data,$_field);
			if(strlen($_str) == 0){
				$_str = Hash::get($data,$p[0]);
			}
			return $_str;
		} catch (Exception $e){
			return '';
		}
	}

	/**
	 *  キーワード検索でサニタイズ対応
	 *  いろいろやってみたけど、検索条件としてはアソシエーションの深いのはダメみたい
	 */
	function mkCondition(Model $model,$searchFields,$keyword = null){
		$cond = array();
		try{
			if(empty($keyword)){
				return $cond;
			}
			
			$searchKeyword = array();
			$searchSuffix = array();
			
			// 一応深くてもいけるようにしてみたがあまり意味はなかったかも
			foreach ($searchFields as $searchfield) {

				$pieces = explode(".", $searchfield);
				$max = count($pieces) -1 ;
				if($max < 1) continue;
				$suffixary = array();
				for($i = 0; $i < $max-1; $i++){
					$suffixary[] = $pieces[$i]; 
				}
				$suffix = join('.',$suffixary);
				$searchKeyword[$pieces[$max-1]][$pieces[$max]] = $keyword;
				$searchSuffix[$pieces[$max-1]] = $suffix;
			}

			$ModelName = null;
			$newFields = array();
			foreach ($searchKeyword as $_model => $fields){
				$newFields[$_model] = array();	
				if($model->name == $_model){
					$backup_this_data = $model->data;
					$model->data = array($_model => $fields);
					$model->beforeValidate();
					$newFields[$_model] = $model->data[$_model]; 
					$model->data = $backup_this_data;
				} else {
					$model->loadModel($_model);
					$backup_this_data = $model->{$_model}->data;
					$model->{$_model}->data = array($_model => $fields);
					$model->{$_model}->beforeValidate();
					$newFields[$_model] = $model->{$_model}->data[$_model];
					$model->{$_model}->data = $backup_this_data;
				}
			}

			//	検索条件を作成
			foreach ($newFields as $modelname => $searchfield) {
				foreach($searchfield as $field => $word){
					if(!empty($word)){
						$key = (empty($searchSuffix[$modelname])) ? '' : $searchSuffix[$modelname] . '.';
						$key .= $modelname . '.' . $field . ' LIKE';
						$cond[$key] = '%' . $word . '%';
					}
				}
			}
//debug($cond);			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $cond;
	}
	
/**
* 文字数が$max値以内かチェック（半角1文字、全角2文字で計算する）
* 
*/
	function maxStringLength(Model $model,$check, $max) {

		$check_str = array_shift($check);
//$this->log($check_str);
//$this->log(mb_strwidth($check_str));
//$this->log((mb_strwidth($check_str) <= $max));	
		return (mb_strwidth($check_str) <= $max);	
	}	

	public function setFlash(Model $model, $message, $element = 'default', $params = array(), $key = 'flash') {
$this->log('setFlash start');
		CakeSession::write('Message.' . $key, compact('message', 'element', 'params'));
	}
	
}
