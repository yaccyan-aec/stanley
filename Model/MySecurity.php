<?php
App::uses('CakeSession', 'Model/Datasource');

/**
* @file MySecurity 
* @brief パスワードセキュリティモデル
*	当面はテーブルを使用しないでシステムごとに bootstrap の設定で管理
*	将来、ユーザごとの細かい使い分けが必要になったらテーブルを見るよう改修する。
*
* @package jp.co.asahi-eg.fts2
* @author Asahi Engineering co., ltd.
* @since PHP 5.3 CakePHP(tm) v 2.3 クラスの運用開始年月日
* @version 5.0.0
*/
/**
* MySecurity モデル
*/
class MySecurity extends AppModel {

/**
* $name          クラス名
* @var string
* @access public
*/
	var $name = 'MySecurity';
	
/**
* $displayField          表示フィールド
* @var string
* @access public
*/
	public $displayField = 'type';

/**
* $useTable          テーブル（使用しない）
*	public $useTable = 'Security';	//!< @brief テーブルを実装したらこちらを使用する
*
* @var boolean
* @access public
*/
	public $useTable = false;		//!< @brief テーブルを使わない
	
/**
* $_schema          擬似フィールドの設定
* @var array
* @access public
*/
/*
	public $_schema = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 10, 'key' => 'primary'),
		'type' => array('type' => 'text', 'null' => true, 'default' => null),
		'data' => array('type' => 'text', 'null' => true, 'default' => null),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
	);
*/	
	private $_data = array();

	var $Session;
/**
* function __construct
* @brief 初期値を設定
* 			Configure より初期値を取り出して設定しておく。
*
* @param  void
* @retval void
*/
	function __construct($id = false, $table = null, $ds = null){
		parent::__construct($id,$table,$ds);
		$_config = Configure::read('Password_Security');

		$this->_data['id'] = 1;
		$this->_data['type'] = 'default';
		$this->_data['data'] = $this->getData($_config);
		
		$this->_session = new CakeSession();
		//$this->loadComponent('Cookie');

	}

/**
 * getData
 * @todo find後の処理（空白を作らないために補完）
 * @param    array $results    
 * @return   array
 */
	function getData($data = array()) {
		try{
			$newdata = $data;
			// パスワード変更のときの最低限のルール
			if(isset($newdata['rule']['type'])){
				$msg = '';
				$ary = array();
				switch($newdata['rule']['type']){
					// パスワードの縛りがすこしゆるいタイプ
					case 'low':
						$msg = '(A-Za-z) (0-9)';
						$ary = array('[a-zA-Z]','[0-9]');
						break;
						
					// パスワードの縛りの普通タイプ	
					case 'normal':
					default:
						$msg = '(A-Z) (a-z) (0-9)';
						$ary = array('[A-Z]', '[a-z]', '[0-9]');
						break;
		
				}
				$newdata['rule']['msg'] = $msg;
				$newdata['rule']['ary'] = $ary;
			}
			// パスワードに使用できる文字
			if(isset($newdata['rule']['chartype'])){
				$regstr = '';
				switch($newdata['rule']['chartype']){
					case 'a':
					case 'A':
						$regstr = '[0-9a-zA-Z]';
						break;
					default:
						$regstr = '[0-9a-zA-Z!#$%&*+-.\/:=?^_]';
						break;
				}
				$newdata['rule']['reg'] = $regstr;
			}
			return $newdata;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $data;
	}
	
/**
* function getary
* @brief 設定内容を求める
* 			現在はConfigure の内容を返す。
*			テーブルがあれば当該タイプに該当するものを返す
* @param  string $typs 
* @retval array
*/
	public function getary($type = 'default'){
		return $this->_data['data'];
	}

/**
* function is_debug
* @brief デバッグモードを調べる
* 			現在はConfigure の内容を返す。
*			テーブルがあれば当該タイプに該当するものを返す
* @param  string $typs 
* @retval boolean	true: デバッグ中 / false: 本番
*/
	public function is_debug($type = 'default'){
		if(empty($this->_data['data'])){
			return false;
		}
		return $this->_data['data']['is_debug'];	
	}


/**
* function get_password_item
* @brief パスワードセキュリティの内容を調べる
* 			現在はConfigure の内容を返す。
*			テーブルがあれば当該タイプに該当するものを返す
* @param  string $item 項目名
* @param  string $type 
* @retval mixed	 有効であれば設定値、無効であれば null
*/
	public function get_password_item($item = 'all' , $type = 'default'){
		try{
			if(empty($this->_data['data'])){
				return null;
			}
			if(empty($this->_data['data']['pwd'])){
				return null;
			}
			if($this->_data['data']['pwd']['is_enable']){
				if($item === 'all'){
					return $this->_data['data']['pwd'];
				}
				return $this->_data['data']['pwd'][$item];
			}
		
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return null;
	}

/**
* function get_lockout_item
* @brief ロックアウトセキュリティの内容を調べる
* 			現在はConfigure の内容を返す。
*			テーブルがあれば当該タイプに該当するものを返す
* @param  string $item 項目名
* @param  string $type 
* @retval mixed	 有効であれば設定値、無効であれば null
*/
	public function get_lockout_item($item = 'all' , $type = 'default'){
		try{
//debug($this->_data);
			if(empty($this->_data['data'])){
				return null;
			}
			if(empty($this->_data['data']['lockout'])){
				return null;
			}
			if($this->_data['data']['lockout']['is_enable']){
				if($item === 'all'){
					return $this->_data['data']['lockout'];
				}
				return $this->_data['data']['lockout'][$item];
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return null;
	}

/**
* function get_rule_item
* @brief ロックアウトセキュリティの内容を調べる
* 			現在はConfigure の内容を返す。
*			テーブルがあれば当該タイプに該当するものを返す
* @param  string $item 項目名
* @param  string $type 
* @retval mixed	 有効であれば設定値、無効であれば null
*/
	public function get_rule_item($item = 'all' , $type = 'default'){
		try{
			if(empty($this->_data['data'])){
				return null;
			}
			if(empty($this->_data['data']['rule'])){
				return null;
			}
			if($item === 'all'){
				return $this->_data['data']['rule'];
			}
			return $this->_data['data']['rule'][$item];
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return null;
	}
	
/**
* function clearCount
* @brief カウンタクリア
* 			ロックアウト対象であればクリアする
* @retval void 
*/
	public function clearCount($counter = array()){
		
		$rc = $this->get_lockout_item();
		if(is_null($rc)){
			return ;
		}
		if(empty($counter)){
$this->log('---- session delete [counter] ',LOG_DEBUG);			
			// パラメータなしのときはまとめて削除
			$this->_session->delete('counter');
		} else {
			foreach($counter as $key){
$this->log('---- session delete ['.$_key.'] ',LOG_DEBUG);			
				$_key = 'counter.'.$key;
				$this->_session->delete($_key);
			}
		}
		return ;
	}

/**
* function getCount
* @brief セッションリトライカウンタ
* 			現在はConfigure の内容を返す。
*			テーブルがあれば当該タイプに該当するものを返す
* @retval mixed	 有効であれば設定値、無効であれば null
*/
	public function getCount($key = 'idpass'){
		$rc = $this->get_lockout_item();
		if(is_null($rc)){
			return 0;
		}
		$_key = 'counter.'.$key;
//		$count = $this->Cookie->read($_key);
		$count = $this->_session->read($_key);
		if(is_null($count)) return 0;
		return $count;
	}

/**
* function addCount
* @brief セッションリトライカウンタのカウントアップ
* 			現在はConfigure の内容を返す。
*			テーブルがあれば当該タイプに該当するものを返す
* @retval mixed	 有効であれば設定値、無効であれば null
*/
	public function addCount($key = 'human'){
		$rc = $this->get_lockout_item();
		if(is_null($rc)){
$this->log('addCount lock 指定なし',LOG_DEBUG);			
			return 0;
		}
		$_key = 'counter.'.$key;
		$count = $this->getCount($key);
		$count++;
		$this->_session->write($_key,$count);
$this->log('###### addCount key['.$key.'] count['.$count.']',LOG_DEBUG);			
		// セッションカウントは裏でトータル
		$sess_count = 0;
		if($key != 'human'){
			// 画像認証のとき以外はセッションカウントも行う
			$sesskey = 'counter.session';
			$sess_count = $this->getCount('session');
			$sess_count++;
$this->log('###### addCount session count['.$sess_count.']',LOG_DEBUG);			
			$this->_session->write($sesskey,$sess_count);
		}
//$this->log('addCount key['.$key.'] count['.$count.'] sess['.$sess_count.']',LOG_DEBUG);			
		return $count;
	}

/**
* function chkLimit
* @brief ロックアウトかどうかの確認
* 			現在はConfigure の内容を返す。
*			テーブルがあれば当該タイプに該当するものを返す
* @retval bool	 true:login NG / false: ok
*/
	public function chkLimit($count = 0, $item = 'retry_limit_session'){
		$limit = $this->get_lockout_item($item);
$this->log('count['.$count.'] item['.$item.'] limit['.$limit.']');
		if(is_null($limit)) return false;
		if($limit == 0) 	return false;
		if($limit <= $count) return true;
		return false;
	}
	
/**
* function setLockoutInit
* @brief ロックアウトのオプション選択かどうかの確認
* 			現在はConfigure の内容を返す。
*			テーブルがあれば当該タイプに該当するものを返す
* @retval bool	 true:login ok / false:lockout
*/
	public function setLockoutInit($key = null,$type = null){
		$secary = array();
		try{
			$k = is_null($key) ? 'my_security' : $key;
			// セキュリティオプションを調べる
//debug($k);	
			$secary = $this->_session->read($k);
//debug($secary);	
			if(empty($secary)){
				// オプションがあればセット
				$secary = $this->get_lockout_item('all',$type);
				$this->_session->write($k,$secary);
			}
			
   		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $secary;
	}
	
/**
* function reasonFailLogin
* @brief ロックアウトかどうかの確認
* 			現在はConfigure の内容を返す。
*			テーブルがあれば当該タイプに該当するものを返す
* @retval url
*/
	public function reasonFailLogin(){
		try{
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
	}
}
?>
