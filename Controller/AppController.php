<?php
/**
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       jp.co.asahi-eg.fts
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package		app.Controller
 * @link		http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
/**
* コンポーネント
*
* @var array $components
* @access public
*/
	var $components = array( 'RequestHandler',	//!< @brief リクエストハンドラ
								'Session',			//!< @brief セッション関数
								'Cookie',			//!< @brief クッキー関数
								'GetAgent',			//!< @brief Agent 取得
								'Common',			//!< @brief 共通関数群
								'DataCheck',		//!< @brief データチェック
								'Transition.Transition',	//!< @brief 画面遷移管理
//								'DebugKit.Toolbar',	// for debug (画面の右上にツールバーが出る)
//								'EncryptionZip',		//!< @brief 暗号ZIP処理関数(7 Zip使用)
								'Maintenance.Maintenance' => array(//!< @brief メンテナンスモード 2015/12/01
								  'maintenanceUrl' => array(
									 'controller' => 'users',
									 'plugin' => null,
									 'action' => 'maintenance'),
								),

								'Search.Prg',
								'ChkRole',

//								'FileUpload.Upload'	//For FileUpload

								);

/**
* モデル
*
* @var 	array $uses
* @access public
*/
    var $uses = array(	'User',			//!< @brief ユーザテーブル
						'Contract',		//!< @brief 契約（部門）テーブル
						'Information',	//!< @brief お知らせテーブル
    					'Role',			//!< @brief オプションテーブル
    					'Group',		//!< @brief 権限テーブル
						'MySecurity',	//!< @brief セキュリティ（テーブルは使用しない）
						'Eventlog',		//!< @brief 実行ログ（イベント） テーブル
						'Filelog',		//!< @brief 実行ログ（アップロード）　テーブル


//						'Syslog',		//!< @brief Syslog テーブル (削除予定)
					);

/**
* ヘルパー
*　TwitterBootstrap　プラグインは　Bootstrap 2.x 用
* BoostCake プラグインは Bootstrap 3.x 用　なので差し替えます。
* Bs3Form ヘルパーと併用できるようにしておきます。
* @var		array $helpers
* @access	public
*/

	var $helpers = array(
							'Session',
//							'Less.less',	// 将来復活するかも

							'Paginator' => array('className' => 'BoostCake.BoostCakePaginator'),

							'Role',		//!< @brief 権限とオプション判定
							'Common',	//!< @brief 共通関数群
							'CheckBrowser',	//!< @brief ブラウザチェック
							'DateTimePicker',	//!< @brief 日付入力支援
							'Js',
						  );

	var $myAgent;				//!< @brief アクセスしているブラウザ（log に記載）
	var $me;
	var $myNavi = array();		//!< @brief ナビバーフラグ
/**
* function beforeFilter
* @brief  全体の前処理（必ず通る）
*			主にセッション関係をチェック
* @param  void
* @retval void
*
*/
	function beforeFilter(){
		// プラグインがロードされているときだけヘルパーをロード
		if(CakePlugin::loaded('Encrypt')){
			$this->helpers[] = 'Encrypt.Encrypt';
		}
		if(CakePlugin::loaded('Vscan')){
			$this->helpers[] = 'Vscan.Vscan';
		}
		if(CakePlugin::loaded('Tfg')){
			$this->helpers[] = 'Tfg.Tfg';
		}
		if(CakePlugin::loaded('Pscan')){
			$this->helpers[] = 'Pscan.Pscan';
		}

//		$rtn = $this->DataCheck->setlang($this->request);
        if ($this->Session->check('Config.language')) {
            Configure::write('Config.language', $this->Session->read('Config.language'));
		}
		$this->myAgent = $this->GetAgent->get();			//!< @brief ブラウザ情報
		$this->set('referer', $this->referer(array('action' => 'index'))); // リファラ情報
//$this->log($this->myAgent);
    	if(empty($this->myAgent)) return;
		switch ($this->controller){
			case 'js':
			case 'css':
			case 'theme':
$this->log('beforeFilter skip.');
				/**
				* ログインが必要ないのでスキップ
				*/
				return;
			default:
				/**
				* 次のチェックへ
				*/
				break;

		}

		ini_set("session.cache_expire",SESSION_CACHE_EXPIRE);	//!< @brief セッション寿命 Env/constdef.php
		set_time_limit(PHP_TIME_LIMIT * 60);					//!< @brief php のタイムリミット Env/constdef.php
		$this->disableCache();

    	$this->set("VERSION",VERSION);							//!< @brief version	Env/version.php

		$this->theme = Configure::read('Config.theme');		//!< @brief テーマ
//$this->log("@@@@@@@@@@@@@@ theme [".$this->theme."]");
//$this->log($this->request->params);

    	$this->Eventlog->setAgent($this->myAgent);			//!< @brief log使用
		/**
		* 言語切替
		*/
		$rtn = $this->DataCheck->setlang($this->request);
//$this->log($this->request->params,LOG_DEBUG);
		/**
		* uri を調べて処理ごとのセキュリティ確認
		*/
		$controller = '';
		$action = '';
		if(isset($this->request->params['controller'])){
			$controller = $this->request->params['controller'];
		}
		if(isset($this->request->params['action'])){
			$action = $this->request->params['action'];
		}

		switch ($controller){
			case 'css':
			case 'js':
			case 'inquiries':
			case 'files':
				return;
			case 'histories':
				if($action == 'index'){
					// 履歴は見るだけ
					return;
				}
				// 問い合わせはとりあえずログインの必要なし
				break;
			case 'MultiUploadfiles':			//!< @brief フラッシュアップロードはここに来る模様
$this->log("@@@@@@@@@@@@@@ MultiUploadfiles");
				return;
			case 'mail_approvals': //メール認証の場合はログイン無し(Nidds)
$this->log("@@@@@@@@@@@@@@ mail_approvals");
				return;
			default:
				break;
		}
//$this->log($action);
		switch ($action){
			case 'inquiry_complete':	//!< @brief 問い合わせ確認
			case 'inquiry':				//!< @brief 問い合わせ
			case 'resetpwd':			//!< @brief パスワード再発行（仮パスワード発行受付）
			case 'resetpwd_confirm':	//!< @brief パスワード再発行（仮パスワード発行確認）
			case 'resetpwd_apply':		//!< @brief パスワード再発行（仮パスワード発行）
			case 'mkpass':				//!< @brief パスワード再発行（本パスワード発行受付）
			case 'mkpass_confirm':		//!< @brief パスワード再発行（本パスワード発行確認）
			case 'mkpass_apply':		//!< @brief パスワード再発行（本パスワード発行）
			case 'exprenew':			//!< @brief 期限延長申請（受付）
			case 'exprenew_confirm':	//!< @brief 期限延長申請（確認）
			case 'exprenew_apply':		//!< @brief 期限延長申請（申請）
			case 'manual':				//!< @brief マニュアル
			case 'lockout':				//!< @brief ロックアウト
			case 'lockout_apply':				//!< @brief ロック解除申請
			case 'maintenance':			//!< @brief メンテナンスモード
				/**
				* ログインが必要ないのでスキップ
				*/
				return;
			case 'webroot':
$this->log("@@@@@@@@@@@@@@　Unit Test ですか？");
				/**
				* テストのとき？
				*/
				return;
			default:
				/**
				* 次のチェックへ
				*/
				break;
		}
		/**
		* ユーザ情報とれたらとります。
		*/
		$auth = $this->Session->read('auth');
		$this->me = $auth;
//$this->log($auth);
		/**
		* ログイン画面関連のチェック
		*/
$this->log("@@@@@@@@@@@@@@ 2");
$this->log('action['.$action.']');
		switch ($action){
			case 'login':				//!< @brief ログイン
			case 'securimage':			//!< @brief 画像認証表示用
			case 'expEnd':				//!< @brief 期限切れ画面
			case 'publicIndex':			//!< @brief お知らせRSS表示用
			case 'publicView':			//!< @brief お知らせ詳細表示用
			case 'logout':				//!< @brief ログアウト
			case 'display':				//!< @brief ページコントローラ用
			case 'swfupload':			//!< @brief ブラウザによってはセッションが切れる
			case 'treejson':			//!< @brief json
			case 'nifco':
			case 'shared':
			case 'molex':
			case 'stanley':
$this->log("@@@@@@@@@@@@@@ 3");
				return;
			default:
				break;
		}

		/**
		* ログイン後の画面チェック
		* 　セッションの確認
		*/
		$auth = $this->DataCheck->getAuth();
		if(is_null($auth)){
$this->log("auth is null.");
			/**
			* ログイン画面に戻る
			*/
$this->log('redirect to logout1(auth がとれない)',LOG_DEBUG);
$this->log('controller['.$controller.'] action['.$action.']',LOG_DEBUG);
			return $this->redirect(array(
					'controller' => 'users',
					'plugin' => null,
					'action'=>'logout'),301,true);
		} else {
			// 最新データが読めたらセッションを書き換える
			$this->Session->write('auth', $auth);
			$this->me = $auth;
		}

 		// uri がじか打ちかどうかチェック
 		if($this->DataCheck->chkReferer()){
 			// ok
 		} else {
 			// 古いブラウザだったら許可するとかのチェック
 			if($this->DataCheck->chkAgent()){
 			} else {
			/**
 			* ログイン画面に戻る
 			*/
 $this->log('URL がじか打ちっぽいので logout',LOG_DEBUG);
 			return $this->redirect(array(
 					'controller' => 'users',
 					'plugin' => null,
 					'action'=>'logout'),301,true);
 			}
 		}
		/**
		* 最新データが読めたらセッションを書き換える
		*/
//		if(is_null($auth)){
//$this->log("auth is NULL.",LOG_DEBUG);
//		} else {
//			$this->Session->write('auth', $auth);
//			$this->me = $auth;
//		}

		/**
		 * 正当な経路から来ているかチェック
		 */
		$_permission = $this->DataCheck->chkCourse();
//$this->log(__METHOD__.'['.__LINE__.']_permission['.$_permission.']',LOG_DEBUG);
		if($_permission){
		} else {
$this->log(__METHOD__.'['.__LINE__.']_permission NG　controller['.$controller.'] action['.$action.']',LOG_DEBUG);
			if (defined('DEBUG_MakingPermission') && DEBUG_MakingPermission) {
				/**
				 * 本番のときはパーミッションに引っかかったら強制ログアウト。
				 */
$this->log('redirect to logout3 パーミッションに引っかかので強制ログアウト',LOG_DEBUG);
				return $this->redirect(array(
						'controller' => 'users',
						'plugin' => null,
						'action'=>'logout'),301,true);

			} else {
$this->log(__METHOD__."[".__LINE__."]テスト中のためとりあえず継続する",LOG_DEBUG);
$this->Session->setFlash(__('権限がありません:') .'controller['.$controller.'] action['.$action.']');
					/**
					 * テスト中はデバッグログに記載してスルー
					 */
			}
		}

/**
* 動的にテーマチェンジとかするときはおそらくこのタイミング 2011.11.10
*/
		/**
		* ログイン中ユーザ
		*/
		$this->set('auth', $auth);
		if(isset($auth['group_id'])){
			$this->myNavi = $this->Role->getNavi($auth['group_id']);
			// 承認権限の調整中（将来は部門で管理かも）
		}
		/**
		* 有効期限（残り日数）の表示　（オプションによる）
		* role で制御に変更　2017
		*/
		if(isset($this->myNavi['remaindays'])){
//		if(defined('OPTION_Countdown_remainder_days') && OPTION_Countdown_remainder_days) {	// 定義されていて true のとき
			// 残り日数を契約、個人　それぞれ取得
			$remain_days = array();
			$remain_days['user'] = $this->User->get_remain_days($auth);
			$remain_days['contract'] = $this->Contract->get_remain_days($auth['contract_id']);
			$this->set('navi_remaindays',$remain_days);
		}

		/**
		* ログイン中ユーザの契約または部門
		*/
		if(isset($auth['contract_id'])){
			$this->set('contract', $auth['contract_id']);
		}

/**********************************************************
 *  バッジをつけるための項目を数える
 **********************************************************/

		/**
		* 不達メール対処 2011.05.31　（オプションによる）
		*
		* データ取得部分は model に移動
		* タブやリンクなど出すための設定
		*/
		if (defined('ERRMAILS')){
			if (defined('ERRMAILS_SHOW_Alert') && ERRMAILS_SHOW_Alert) {
				$this->loadModel('Errmails.Errmail');
				$this->set('navi_errmail',$this->Errmail->countErrmail($auth['id']));
			}
		}
		/**
		* 受信情報カウント　（オプションによる）
		*
		* データ取得部分は model に移動
		* タブやリンクなど出すための設定
		*/
		$this->loadModel('Status');
		$this->set('navi_status',$this->Status->countMidoku($auth['id']));

		/**
		* 送信情報エラーカウント　（オプションによる）
		*
		* データ取得部分は model に移動
		* タブやリンクなど出すための設定
		* →　smtp エラーも　不達メールとして出すので廃止2016/08/23
		*/

		// 承認待ちなどの個数をバッジで出すためのカウント
		$this->loadModel('Approval');
//debug($this->myNavi);

		$approval = $this->Approval->countMyRequest($auth['id'],$this->myNavi['all']);
		$this->set('navi_approval',$approval);
		$this->set('myNavi',$this->myNavi);

		/**
		* ディスク容量チェック　（2017.06）　#6863
		*
		* 空き容量が規定より少なくなったら送信を制限する
		*/
        $has_space = $this->Common->diskSpaceCheck(Configure::read('Upfile.dir'),Configure::read('VALUE_REMAIN_DISK_SPACE'));
		$this->set('has_space',$has_space);

		/**
		* クッキーをセーブ（セッション切れ対策）
		*/
		$this->Common->saveCookie();

	}

/**
* refreshAuth
* @brief 自分のユーザ情報を変更したとき、Authを最新のものにする
* @param mix $this->me
* @retval mix $auth :
*/
	public function refreshAuth(){
		try{
			$id = $this->me['id'];
			$this->User->recursive = 0;
			// 今回は ID だけで読む
			$user = $this->User->findById($id);
$this->log('refreshAuth');
$this->log($user);
			if(!empty($user)){
				// セッションとクッキーを書き換える
				$extuser = array_merge($user['UserExtension'],$user['User']);

				$this->Session->write('auth', $extuser);
				$this->me = $extuser;
				$this->Common->saveCookie();
				return $extuser;
			}
			return null;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': ERROR :'. $e->getMessage());
			return null;
		}
	}

	/**
	* function writeLog
	* @brief syslog 書き込み
	* @param  mixed  $args  パラメータの内容を syslog テーブルに登録
	* @retval void
	*/
	function writeLog($args,$user = null){
		try{
			$auth = $user;
			if(empty($auth)){
				$auth = $this->me;
			}
			if(empty($auth)){
$this->log('writeLog this->me is empty',LOG_DEBUG);
				$auth = $this->Session->read('auth');
			}
			if($this->Eventlog->insertLog($args,$auth,$this->Session->id())){
				return($this->Eventlog->getLastInsertID());
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
$this->log("===== writeLog --- err",LOG_DEBUG);
		}
		return null;

	}
	/*
	 * getLang
	 * return String lang
	 */
	function getLang(){
		return(Configure::read('Config.language'));
	}
	/*
	 * getLang
	 * return String lang
	 */
	function setLang($lang = VALUE_System_Default_Lang){
		$this->Session->write('Config.language',$lang);
		Configure::write('Config.language',$lang);
		return;
	}

/**
 * ctrl method　共通化
 *　一括処理の飛び先とパラメータ設定
 * @param string $id
 * @return void
 */
	public function ctrl($item = 'id', $id = null) {
		try{
$this->log('ctrl start! id['.$id.']',LOG_DEBUG);
			$action = $this->request->data['actions']['action'];
            $address_group_id = '';
            if (isset($this->request->data['address_id'])){
                $address_group_id = $this->request->data['address_id'];
            }

			if($action == '#'){
				// 処理を選んでいなかったら一覧に戻る
				$this->redirect(array('action' => 'index'));
			}
			// 処理を controller と　action に分解（プラグインの考慮は後日）
			$str = explode('/',$action);
			// チェックしたIDの取り出し
			$ary = array_filter($this->request->data[$item]);

            //actionがアドレス帳追加以外で、自己送信不可の場合、自分のIDを消す
            $auth = $this->Session->read('auth');
            $_to_self = $this->Role->chkRole($auth['group_id'],array('controller' => 'send',
											'action' => 'self'));
            if ($str[1] == 'add' || $str[1] == 'delconf'){
                if(!$_to_self){
                    if(($key = array_search($auth['id'],$ary)) !== false) {
                        unset($ary[$key]);
                        //自分のIDのみ選択していた場合
                        if($str[1] == 'add' && count($ary) == 0){
                            $this->Session->setFlash(__('You can not send it to yourself.'),'Flash/error');
                            $this->redirect(array('action' => 'index'));
                        }
                }}
            }
			// ひとつ以上選択していたら
			if(count($ary) > 0){
				// url 作成のパラメータ
				$param = array_merge ($ary,array('controller' => $str[0],
										'action' =>  $str[1],
										'from' => @$str[2],
										'base' => false,
                                        'address_group_id' => $address_group_id,
                                    ));

				// リダイレクトURI作成
				$url = Router::url($param,
									array('escape'=>true,'full'=>false));
				$this->redirect($url);
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		$this->Session->setFlash(__('Please select.'),'Flash/error');
		$this->redirect(array('action' => 'index'));
	}

}
