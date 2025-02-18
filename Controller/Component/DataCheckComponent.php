<?php
App::uses('MyComponent' , 'Controller/Component');

/**
 * DataCheck Component.
 *
 * 
 *
 * @package		DataCheck.Controller.Component
 *
 */
class DataCheckComponent extends MyComponent {
	var $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;	// controllerの変数を使いたいので。
	}
	
	/*
	function __construct() {
		parent::__construct();
	}*/

	/**
	* function isFirst
	* @brief 初回ログインパラメータがあればセット
	* @param  array $req
	* @retval bool
	*/
	public function isFirst($req = array()){
		try{
			// 予めセットしておくべきパラメータがあったらセット
			if (empty($req->data)) {
				/**
				* 最初に画面表示するとき
				*/
				if(array_key_exists ( 'r', $req->params['named'])){
					/**
					* パラメータがあったら、セットしておく(認証）
					*/
					$this->_controller->set('firsturi','r');
				}

				if(!empty($req->params['named']['i'])){
					/**
					* パラメータでID が来ていたらセット
					*/
					$this->_controller->set('ID',$req->params['named']['i']);
				}

			}
			// セキュリティオプションがデバッグモードなら画面にカウンタを表示
			$this->loadModel('MySecurity');
			
			// ロックアウトオプションあり　＆ デバッグモード　のときに画面にカウンタを表示
			$is_debug = false;
			if(!is_null($this->MySecurity->get_lockout_item())){
				$is_debug = $this->MySecurity->is_debug();
			}
			// referer から初回かどうかをチェック
			// referer が今と同じなら初回ではないと考える
			$now_referer =  Router::url('/') . ltrim($this->_controller->referer(null,true),'/');
			$now_path = Router::url();
			
//if($is_debug) print_r('now_referer['.$now_referer.']<br>');
//if($is_debug) print_r('now_path   ['.$now_path.']<br>');

			if(strncmp($now_referer , $now_path, strlen($now_path)) != 0){	
if($is_debug) print_r('同じではないから初回　now_referer != now_path<br>');		
				return true;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
//if($is_debug) print_r('初回じゃない<br>');		
		return false;
	}

/**
* function setlang
* @brief 言語切替　（2010.12.29　関数化）
* configure の言語を適切なものに変更する。
*
* @param  void
* @retval void
*/
    function setlang($req){
		$mylang = '';
			if( isset($req->query['lang']) && !empty($req->query['lang']) ){
				/**
				* URI のパラメータに明記されているとき
				*/
				$mylang = $req->query['lang'];
			}
			if(empty($mylang)){	// まだ決まっていないとき
				if($this->_controller->Session->check('Config.language')){	// セッションがある
					/**
					* セッションから求める
					*/
					$mylang = $this->_controller->Session->read('Config.language');
				} else {	// セッションがない
					/**
					* クッキーがあれば、クッキーから最後に使った言語を求める
					*/
					$cookie = $this->_controller->Cookie->read('fts_lang');
					if(!empty($cookie)){	// クッキーがある
						$mylang = $cookie;
					} else {	// クッキーも無いときはデフォルト
						/**
						* クッキーが無いときは、デフォルト言語とする
						*/
						$mylang = $this->_controller->getLang();
					}
				}
			}
			/**
			* 決定した言語を保存し、configure を書き直す
			*/
			switch($mylang){
				case 'jpn':
				case 'eng':
					break;
				default:
					// 決まってないときはとりあえず現在の言語
					$mylang = $this->_controller->getLang();
//					$mylang = Configure::read('Config.language');	//!< @brief  デフォルト言語
					break;
			}
			$this->_controller->setLang($mylang);
		return $mylang;
    }


	
/**
* function getAuth
* @brief セッションを確認　（2014.04.01　関数化）
* セッションを確認し、切れていたら出来る限り復元する
*
* @param  void
* @return array $auth 
*/
    function getAuth(){
		$auth = $this->_controller->Session->read('auth');
		if(is_null($auth)){
			$auth = $this->revivalSession();
		} else {
			// セッションが取れていても一応最新のものを読む
			$_user = $this->_controller->User->getActiveUser($auth['id'],$auth['pwd']);
			// 途中でパスワードが変わってしまうとログアウトしてしまうのでこのセッション中は許可する
			if(isset($_user['User'])){
				$auth = $_user;
			}
		}
		return $auth;
	}


/**
* function chkReferer
* @brief referer　（2014.04.01　関数化）
* / だけのときはuri 直打ちの可能性が高いためエラー
*
* @param  mixed $auth
* @return bool  $code  
*			 true : OK
*			 false: NG
*			
*/	
    function chkReferer(){
		$referer = $this->_controller->referer();
//$this->log('referer['.$referer.']',LOG_DEBUG);
//$this->log($this->request,LOG_DEBUG);		
		/**
		* referer を調べる
		*（/ だけのときはuri 直打ちの可能性が高いためブロック）
		*/
		if(is_null($referer)){
			return false;
		}
		
		if($referer == '/'){
			return false;
		}
    	// ok case
    	return true;
	}


/**
* function revivalSession
* @brief クッキーからユーザ情報を得る
* 
* @param  void
* @return array ユーザ情報　/ null : 失敗
*/
	function revivalSession(){
		try{
$this->log("=====> _revival_session データを復活させてみます。");
			/**
			* クッキーを読む
			*/
			$_now_cookie = $this->_controller->Cookie->read('fts');
			if(empty($_now_cookie)){
$this->log("=====> クッキーがなくてダメでした。");
				return null;
			}
//$this->log($_now_cookie);
			/**
			* クッキー情報を元に当該ユーザがあるか読んでみる
			*/
			if(isset($_now_cookie['i']) && isset($_now_cookie['p'])){
				$_user = $this->_controller->User->getActiveUser($_now_cookie['i'],$_now_cookie['p']);
			} else {
$this->log("=====> クッキーの内容に必要な情報がなくてダメでした。");
				return null;
			}
			/**
			* 当該ユーザ情報を返す
			*/
			return $_user;
		} catch (Exception $e) {
$this->log(__('ユーザ復元失敗').'['.$e.']',LOG_DEBUG);
			return null;
		}
	}

/**
* function chkCourse
* @brief 経路の正当性チェック
* 
* @param  void
* @return bool  $code  
*			 true : OK
*			 false: NG
*/
	function chkCourse(){
		try{
			$gid = $this->_controller->me['group_id'];
			$this->loadModel('Role');
			switch($this->_controller->request->params['controller']){
				case 'css':
				case 'js':
				case 'theme':
$this->log(__('css,js なのでOK'),LOG_DEBUG);

					return true;
				default:	
			}
			if($this->Role->isSuper($gid)){
				return true;	// super 権限
			}
//$this->log('group['.$gid.']controller['.$this->_controller->request->params['controller'].'] action['.$this->_controller->request->params['action'].']',LOG_DEBUG);
			$rc = $this->Role->chkRole($gid,$this->_controller->request->params);
			if($rc){
//$this->log(__('権限ok'),LOG_DEBUG);
			} else {
				// error
$this->log('group['.$gid.	']controller['.$this->_controller->request->params['controller'].
							'] action['.$this->_controller->request->params['action'].
							']named['.@$this->_controller->request->params['named']['from'].']',LOG_DEBUG);
$this->log(__('権限がありません1'),LOG_DEBUG);
				return false;
			}
			return true;
		} catch (Exception $e) {
$this->log(__('経路チェックエラー').'['.$e.']',LOG_DEBUG);
			return false;
		}
	}
	
/**
* function chkAgent
* @brief 経路の正当性チェック
* 
* @param  void
* @return bool  $code  
*			 true : OK
*			 false: NG
*/
	function chkAgent(){
//$this->log($this->_controller->myAgent);
		try{
			//-------------------------------------------------------------
			//	IE7, IE8	では「コピーして編集」でコケる件をなくすため
			//	↓を活かす。しかしこれだと手打ちでも許可してしまいセキュアじゃない。
			//		因みにIE9でも「互換表示」モードのときは version 7.0 を返す
			//		将来的には「コピーして編集」のときだけ許可するようにしたい。
			//-------------------------------------------------------------
			if($this->_controller->myAgent['ua'] == 'MSIE'){
				if($this->myAgent['version'] < 9.0){
$this->log("####### IE 9.0 未満なのでおめこぼし",LOG_DEBUG);
					return true;
				}
			} elseif ($this->_controller->myAgent['ua'] == 'Shockwave'){
$this->log("####### Shockwave はフラッシュかな",LOG_DEBUG);
				return true;
			} elseif ($this->_controller->myAgent['ua'] == 'Firefox'){
$this->log("####### FireFox は動きが特殊？　こまかい対応は後で2016.02",LOG_DEBUG);
				return true;
			}
$this->log(__('手打ちは不可'),LOG_DEBUG);
			return false;
		} catch (Exception $e) {
$this->log(__('経路チェックエラー').'['.$e.']',LOG_DEBUG);
			return false;
		}
	}

/**
* function chkHuman
* @brief 画像認証チェック
* 
* @param	array $reg
* $param	bool  $flg 
* @return bool  $code  
*			 true : OK
*			 false: NG
*/
	function chkHuman($req,$flg){
		try{
//$this->log($req,LOG_DEBUG);	
//$this->log($_SESSION['securimage_code_value'],LOG_DEBUG);		
			if(defined('HUMAN_TEST') && HUMAN_TEST) {	// 定義されていて true のとき
				if($this->_controller->captcha->check($req['User']['captcha_code'])){
					// 画像認証テストOK
					return true;
				} else {
					//　画像認証テストNG
					if($flg){
						// id pass チェックがOKだったときだけ加算する
						$this->MySecurity->addCount('human');
					}
					return false;
				}
			}
		} catch (Exception $e){
$this->log('chkHuman err['.$e->getMessage().']');
			return false;
		}
		return true;
	}

/**
* function chkLogin
* @brief いろんなオプションを盛り込んだログインチェック
* 
* @param  void
* @return mix
*		array  : OK($user)
*		false : NG
*/
	function chkLogin($req){
		$rtn = false;
		try{
$this->log('chkLogin start ---',LOG_DEBUG);
//$this->log($req,LOG_DEBUG);
			
			if(!Hash::check($req,'User.email') || !Hash::check($req,'User.pwd')){
//print_r('パラメータなし<br>');
				// たぶん初回
				return false;
			}
			$_email = Hash::get($req,'User.email');
			$_pwd_hira   = Hash::get($req,'User.pwd');
			$_pwd = md5($_pwd_hira);

			// セキュリティオプションを調べる
			$this->loadModel('MySecurity');
			$my_sec = $this->MySecurity->setLockoutInit();

			/**
			* ユーザID , パスワード　が対応する有効データがあるか確認
			*/
			$this->loadModel('User');
            // ロックアウト中かどうか調べる
            if($this->User->isLockoutUser($_email,$my_sec)){
$this->log('ロックアウト中['.$_email.'] pwd['.$_pwd.']',LOG_DEBUG);
                $lockuser = $this->User->findByEmail($_email);
                $this->_controller->writeLog(
                    array(
                        'event_action' => 'ログイン',
                        'result' => '失敗',
                        'remark' => 'ロックアウト中',
                        'login_id' => $_email,
                    ),
                    $lockuser['User']);
                $this->_controller->redirect(array(
                    'controller' => 'users',
                    'action'=>'lockout',
                    $lockuser['User']['id']
                ));
                return false;	// ここはとおらない	
            }
            
            
			$user = $this->User->getActiveUser($_email,$_pwd);
			if(Hash::check($user,'id')){
				// ロックアウト中かどうか調べる
/*  ↑に移動              
				if($this->User->isLockoutUser($user,$my_sec)){
$this->log('ロックアウト中['.$_email.'] pwd['.$_pwd.']',LOG_DEBUG);			
					$this->_controller->writeLog(
						array(
							'event_action' => 'ログイン',
							'result' => '失敗',
							'remark' => 'ロックアウト中',
							'login_id' => $user['email'],
						),
						$user);
					$this->_controller->redirect(array(
						'controller' => 'users',
						'action'=>'lockout',
						$user['id']
					));
					return false;	// ここはとおらない	
				}
*/                
$this->log('ロックアウト中じゃない 正常ケース['.$_email.'] pwd['.$_pwd.']<br>',LOG_DEBUG);
				return $user;
			} else {
				// ID、PWD　エラーかどうか調べる
				$this->MySecurity->addCount($_email);
				if($this->is_deleted($_email)){
//debug('削除済['.$_email.'] pwd['.$_pwd.']');			
					$this->_controller->writeLog(
						array(
							'event_action'=>'ログイン',
							'result'=>'失敗',
							'remark'=>'削除済ユーザ',
							'login_id'=>$_email,
							)
						);
					// id/pwd エラーだけどhuman に数えたい	
				} else {
					$event_data = $this->is_pwderr($req,$_pwd);
//debug('pwd err['.$event_data.']');
					if(empty($event_data)){
						// ID エラー
						$event_data = '失敗ID['.$_email.']';
					} else {
						// パスワードエラー
						$this->User->addRetry($_email);
					}
					if(!empty($_email)){
						// ID に記載があった時だけログに書く
						$_err_user = $this->User->find('first',array(
							'conditions' => array('User.email' => $_email),
							'recursive' => -1));
//$this->log($_err_user,LOG_DEBUG);							
						$this->_controller->writeLog(
							array(
								'event_action'=>'ログイン',
								'result'=>'失敗',
								'remark'=>'ID またはパスワード違い',
								'event_data'=>$event_data,
								'login_id'=>$_email,
							),
							$_err_user
							);
//						$this->MySecurity->addCount($_email);
$this->log('ID またはパスワード違い['.$event_data.'] login_id['.$_email.']',LOG_DEBUG);
					} else {
//						$this->MySecurity->addCount('human');
$this->log('ここ、きますか？ ['.$event_data.'] ',LOG_DEBUG);
					}
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;	
	}
	

/**
* function is_deleted
* @brief 削除データか調べる
* 
* @param  array email : email
* @return bool  true : 削除済
*				false: 削除済でない
*			 
*/
	function is_deleted($email = array()){
		try{
			$this->_controller->User->recursive = -1;
			// email だけで検索して有効なものがなく
			// 削除済で検索して１つ以上てきたら削除済とみなす
			$_user0 = $this->_controller->User->find(
				'all',array(
					'conditions' => array(
						'email' => $email,
						)));
			$_user1 = $this->_controller->User->find(
				'all',array(
					'conditions' => array(
						'email' => $email,
						'is_deleted' => 1
						)));

			if(count($_user0) == 0 && count($_user1)> 0){
$this->log('==== chkAuth 削除済ユーザ['.$email.']',LOG_DEBUG);	
				// 削除済
				return true;
			}
			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

/**
* function is_pwderr
* @brief 削除データか調べる
* 
* @param  array email : email
* @return bool  true : 削除済
*				false: 削除済でない
*			 
*/
	function is_pwderr($req = array(),$cript_pwd){
		$rtntxt = '';
		try{
			$_user = $this->_controller->User->find('first',
				array( 'conditions' => array('email' => $req['User']['email']),
						'recursive' => -1 ));
			if(empty($_user)){
				$rtntxt = '失敗ID['.$req['User']['email'].']';
			} else {
				$rtntxt = '失敗パスワード['.$req['User']['pwd'].']';
				$rtntxt .= '['.$cript_pwd.']';
				$rtntxt .= '!=['.$_user['User']['pwd'].']';
			}
			if(Hash::check($_user,'lang')){
				$this->setLang($_user['lang']);
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $rtntxt;
	}







	
// for unit test
    function otameshi($r){
$this->log($r);    
		return ('okです');
    }


}

