<?php
App::uses('AppModel', 'Model');
App::uses('CakeEmail', 'Network/Email');
/**
 * SendMail Model
 *
 * メール送信
 */
class SendMail extends AppModel {

	var $name = 'SendMail';

	var $useTable = false;// テーブルを使わない

	var $email;

/**
 * __construct
 * @todo 	コンストラクタ　（初期値設定）
 * @return   void
 */
	function __construct($id = false, $table = null, $ds = null){
		parent::__construct($id,$table,$ds);
		// 定数は　Config/Env/myconfig.php で設定
		$this->email =  new CakeEmail(VALUE_MailConfig);
	}

/**
 * mail_init
 * @todo 	 初期化(メールサーバ設定OFFのとき)
 * @return   void
 */
	function mail_init($data=array()){

        $_mytheme = Configure::read('Config.theme');

        if ($_mytheme == 'stanley' || empty(VALUE_Mail_Username) || empty(VALUE_Mail_Password)){
            $defaultSet = array(
                'transport' => VALUE_Mail_Transport,
                'sender' => array(VALUE_Mail_FromKey => VALUE_Mail_FromVal),
                'host' => VALUE_Mail_Host,
                'port' => VALUE_Mail_Port,
                'timeout' => VALUE_Mail_Timeout,
            // -------------------------------------------------------------------
            //  SMTP Error: 503 5.5.1 Error: authentication not enabled　が出るときは
            //  username と password の項目をコメントアウトしてみる。
            // -------------------------------------------------------------------
                // スタンレーの場合は以下をコメントとする。
                'username' => VALUE_Mail_Username,
                'password' => VALUE_Mail_Password,
                'client' => VALUE_Mail_Client,
                'log' => VALUE_Mail_Log,
                'charset' => VALUE_Mail_Charset,
                'headerCharset' => VALUE_Mail_HeaderCharset,
                // 20200317追記
                // 'replyTo' => VALUE_Mail_Username,
                // 'replyTo' => array(VALUE_Mail_FromKey => VALUE_Mail_FromVal),
                'replyTo' => $data['Mailqueue']['mail_data']['from'],
            );
        }else{
            $defaultSet = array(
                'transport' => VALUE_Mail_Transport,
                'sender' => array(VALUE_Mail_FromKey => VALUE_Mail_FromVal),
                'host' => VALUE_Mail_Host,
                'port' => VALUE_Mail_Port,
                'timeout' => VALUE_Mail_Timeout,
            // -------------------------------------------------------------------
            //  SMTP Error: 503 5.5.1 Error: authentication not enabled　が出るときは
            //  username と password の項目をコメントアウトしてみる。
            // -------------------------------------------------------------------
                'username' => VALUE_Mail_Username,
                'password' => VALUE_Mail_Password,
                'client' => VALUE_Mail_Client,
                'log' => VALUE_Mail_Log,
                'charset' => VALUE_Mail_Charset,
                'headerCharset' => VALUE_Mail_HeaderCharset,
                // 20200317追記
                'replyTo' => VALUE_Mail_Username, // JUNKMAILに入れないためsenderと同じにする
            );
        }

        //returnPathの設定
        if(defined('ERRMAILS')){
            // プラグインを使用するとき
            // エラーメール受付専用アドレス（別サーバでもいいのかなぁ
            $defaultSet['returnPath'] = ERRMAIL_ADDRESS;
        } else {
            // プラグインがないときはデフォルト
            if($data['Mailqueue']['mail_from'] == null){
            } else {
                // 各ユーザに返したいときは動的に変更できる
                $defaultSet['returnPath'] = $data['Mailqueue']['mail_from'];
            }
        }

		if (!empty($this->email)) {
			$this->email->reset();
			$this->email->config($defaultSet);
		} else {
			$this->email =  new CakeEmail($defaultSet);
		}
	}
/**
 * mail_init_on
 * @todo 	 初期化(メールサーバ設定ONのとき)
 * @param  array : DBのメールサーバ情報
 * @return   void
 */
	function mail_init_on($data=array()){
		$custom = array(
			'transport' => VALUE_Mail_Transport,
			//'sender' => array(VALUE_Mail_FromKey => VALUE_Mail_FromVal),
			'sender' => $data['User']['email'],
			'host' => $data['UserExtension']['server_name'],
			'port' => $data['UserExtension']['port'],
			'timeout' => VALUE_Mail_Timeout,
	// -------------------------------------------------------------------
	//  SMTP Error: 503 5.5.1 Error: authentication not enabled　が出るときは
	//  username と password の項目をコメントアウトしてみる。
	// -------------------------------------------------------------------
			'username' => $data['User']['email'],
			'password' => $data['UserExtension']['server_password'],
			'client' => VALUE_Mail_Client,
			'log' => VALUE_Mail_Log,
			'charset' => VALUE_Mail_Charset,
			'headerCharset' => VALUE_Mail_HeaderCharset,
			'returnPath' => $data['User']['email'],
			);

        if (!empty($this->email)) {
			$this->email->reset();
			$this->email->config($custom);
		} else {
            // ↓　ここで新しくインスタンスを生成
			$this->email =  new CakeEmail($custom);
		}

	}

/**
 * mail_init_test
 * @todo 	 初期化(メールサーバ設定のテストメール送信の時)
 * @param  array :現在設定のメールサーバ情報
 * @return   void
 */
	function mail_init_test($data=array()){
		$customTest = array(
			'transport' => VALUE_Mail_Transport,
			//'sender' => array(VALUE_Mail_FromKey => VALUE_Mail_FromVal),
			'sender' => $data['user_name'],
			'host' => $data['server_name'],
			'port' => $data['port'],
			'timeout' => VALUE_Mail_Timeout,
	// -------------------------------------------------------------------
	//  SMTP Error: 503 5.5.1 Error: authentication not enabled　が出るときは
	//  username と password の項目をコメントアウトしてみる。
	// -------------------------------------------------------------------
			'username' => $data['user_name'],
			'password' => $data['server_password'],
			'client' => VALUE_Mail_Client,
			'log' => VALUE_Mail_Log,
			'charset' => VALUE_Mail_Charset,
			'headerCharset' => VALUE_Mail_HeaderCharset,
			//'returnPath' => $data['User']['email'],
			);
        if (!empty($this->email)) {
			$this->email->reset();
			$this->email->config($customTest);
		} else {
            // ↓　ここで新しくインスタンスを生成
			$this->email =  new CakeEmail($customTest);
		}
	}
/**
 * sendFromQueue
 * @todo 	Mailqueue に登録されているものを送信する。
 * @param 	int $id
 * @param 	array $mailserver : メールサーバ情報
 * @return 	bool
 */
	function sendFromQueue($id = null, $mailServer = array()){
		if($id == null) return false;
		$_lang = $this->getLang();
$this->log(__FILE__ .':'. __LINE__ .'セーブします: getlang['.$_lang.']');
		$rtn = true;
		try{
			$this->loadModel('Mailqueue');
			$this->loadModel('User');
			$this->loadModel('Status');
			$this->Mailqueue->recursive = 0;
			$qdata = $this->Mailqueue->findById($id);

			if(!isset($qdata['Mailqueue'])){
				return false;
			}
			switch($qdata['Mailqueue']['template']){
				case 'upload':
$this->log('upload',LOG_DEBUG);
					// ステータスを　「処理中」に
					$this->Mailqueue->setStatus($id,VALUE_Status_Doing);
					// 送信通知だったら ワンタイムパスワードがあるかどうか調べる
					$udata = $this->User->find('first',array(
						'conditions' => array('id' => $qdata['Status']['user_id']),
						'recursive' => -1,
						));
					// ---------- ロックアウトされていた場合、解除してよいかどうかチェックする
					$is_lockout = $this->User->isLockoutUser($udata['User']);
					if($is_lockout){
$this->log('--- ロックアウト中 user['.$udata['User']['email'].']',LOG_DEBUG);
						// ロックアウト中
						if($this->User->can_autoRelease($udata['User'])){
$this->log('--- ロックアウト中 自動解除 user['.$udata['User']['email'].']',LOG_DEBUG);
							// 自動解除する
							$this->User->lockout_release($udata['User']['id']);
							// パスワード変更フラグをオフ
							$this->User->id = $udata['User']['id'];
							$data = $this->User->saveField('is_chgpwd','N');

						}
					}
					// zip パスワードがあるか調べる
$this->log('--- zip パスワードがあるか調べる',LOG_DEBUG);
//$this->log($qdata,LOG_DEBUG);
					$this->loadModel('Uploadfile');
					$zpwd = $this->Uploadfile->getZPwd($qdata['Status']['content_id']);
$this->log('--- zip パスワード['.$zpwd.']',LOG_DEBUG);
					if(!empty($zpwd)){
$this->log('--- zip パスワード insert['.$zpwd.']',LOG_DEBUG);
						$qdata['Mailqueue']['mail_data']['viewVars']['zpwd'] = $zpwd;
					}
//$this->log($qdata,LOG_DEBUG);
					$has_otp = $this->User->has_OneTimePwd($qdata['Status']['user_id']);
					// ここで送信しないとパスワードが先に行っちゃう
					$npw = (is_array($has_otp)) ?  'pwd' : '' ;
					$sendrc = $this->SendMsg($qdata,$npw,$zpwd);
					if($sendrc){
$this->log('送信成功',LOG_DEBUG);
						if(is_array($has_otp)){
							//!< @brief パスワードがあったらqueue に登録
$this->log('パスワードがあるのでキューに登録',LOG_DEBUG);
							// パスワード変更フラグをクリア
							$this->User->set_IsChgpwd($qdata['Status']['user_id']);
							$this->Mailqueue->putQueue('login_pwd',$qdata);
						}

						if(!empty($zpwd)){
$this->log('ZIP　パスワードがあるのでキューに登録',LOG_DEBUG);
							$this->Mailqueue->putQueue('zip_pwd',$qdata);
						}
						$this->Mailqueue->setStatus($id,VALUE_Status_Done);

						/**
						 *  AutoStore のタイミングはこのへん
						 *  送信が成功したらセーブする
						 *  送信宛先をStore対象か調べて、対象だったらセーブする
						 *  送信エラーだったらセーブしない場合は、SendMsg のあとに移動
						 *  呼び出し手順：
						 *  mailqueue_id から status の to メールアドレスを取得
						 *  mailqueue_id から　content_id を取得
						 *  呼び出しイメージ（関数名は仮）
						 *  -----------------------------------------------
						 *  if($this->AutoStore->is_Z_include($to_address)){
						 *  	$this->AutoStore->auto_store_data($content_id);
						 *  }
						 *
						 *  註：今後　暗号zip しているかどうかのチェックを追加する。
						 */

						if(CakePlugin::loaded('AutoStore')){
$this->log('------------ q_send start ストレージするならここから');
							//　呼び出し用　content_id を求める
							$_mail_to = $qdata['Mailqueue']['mail_to'];
$this->log('mail_to['.$_mail_to.']');
							$this->loadModel('AutoStore.AutoStore');
							if($this->AutoStore->is_Z_include($_mail_to)){
								$status = $this->Status->find('first',array(
									'conditions' => array('Status.id' => $qdata['Mailqueue']['status_id']),
									'recursive'  => -1,
									));
$this->log('格納します');
								$this->AutoStore->auto_store_data($status['Status']['content_id']);
							}
						//	$this->AutoStore->test();
$this->log('------------ q_send start ストレージするならここまで');
						}
					} else {
						// 送信エラー
						$this->Mailqueue->setStatus($id,VALUE_Status_Error);
						$rtn = false;
					}
					break;

				case 'login_pwd':
$this->log('login_pwd');

					$sttid = $qdata['Mailqueue']['status_id'];
					$flg = (is_null($sttid)) ? false : true ;
$this->log('mkOneTimePwd 2');
					$newpwd = $this->User->mkOneTimePwd($qdata['Status']['user_id'],$flg);
					// パスワードを変更したらパスワード期限を設定
					$this->loadModel('MySecurity');
					$limit = $this->MySecurity->get_password_item('time_limit');
					$expdate = $this->getexpday($limit);

					$qdata['Mailqueue']['mail_data']['viewVars']['expdate'] = $expdate;

					if($this->SendMsg($qdata,$newpwd)){
						$this->Mailqueue->setStatus($id,VALUE_Status_Done);
					} else {
						$this->Mailqueue->setStatus($id,VALUE_Status_Error);
						$rtn = false;
					}
					break;

				case 'tmppwd':
					if($this->SendMsg($qdata)){
						$this->Mailqueue->setStatus($id,VALUE_Status_Done);
					} else {
						$this->Mailqueue->setStatus($id,VALUE_Status_Error);
						$rtn = false;
					}
					return $rtn;

					case 'mkpass':
$this->log('mkpass');
//$this->log($qdata['Mailqueue']['mail_data']['viewVars']);
					if(isset($qdata['Mailqueue']['mail_data']['viewVars']['loginpwd'])){
$this->log('管理者によるパスワード変更はこっち');
						$newpwd = $qdata['Mailqueue']['mail_data']['viewVars']['loginpwd'];
						if($this->SendMsg($qdata,$newpwd)){
							$this->Mailqueue->setStatus($id,VALUE_Status_Done);
						} else {
							$this->Mailqueue->setStatus($id,VALUE_Status_Error);
							$rtn = false;
						}
					} elseif (isset($qdata['Mailqueue']['mail_data']['viewVars']['user_id'])){
$this->log('送信に伴うパスワード変更はこっち');
						$uid = $qdata['Mailqueue']['mail_data']['viewVars']['user_id'];
						// パスワード発行、有効期限設定(必要なときだけ)
$this->log('mkOneTimePwd 3');
						$newpwd = $this->User->mkOneTimePwd($uid,true);
						$this->loadModel('MySecurity');
						$limit = $this->MySecurity->get_password_item('time_limit');
						$expdate = $this->getexpday($limit);

						$this->User->setPwdExpdate($uid,$expdate);
						$user = $this->User->findById($uid);
						$qdata['Mailqueue']['mail_data']['viewVars']['expdate'] = $expdate;
						if($this->SendMsg($qdata,$newpwd)){
							$this->Mailqueue->setStatus($id,VALUE_Status_Done);
						} else {
							$this->Mailqueue->setStatus($id,VALUE_Status_Error);
							$rtn = false;
						}
					} else {
						$rtn = false;
					}
					return $rtn;
				case 'err_inquiry':
$this->log('err_inquiry');
					$qdata = $this->chkAttachInq($qdata);
				case 'exp_apply':	// 期限延長申請
				case 'inquiry':		// お問い合わせ
				case 'change_pwd':		// パスワード手動変更
				case 'user_edit':		// ユーザ変更
				case 'alert_exp':		// 期限間近のお知らせ
				case 'alert_expend':	// 期限切れのお知らせ
				case 'upload_over':		// アップロード超過アラート
				case 'abort_pscan':		// PSCAN 強制キャンセルアラート
$this->log('exp_apply,inquiry,change_pwd,user_edit　...');
					if($this->SendMsg($qdata,'')){
						$this->Mailqueue->setStatus($id,VALUE_Status_Done);
					} else {
						$this->Mailqueue->setStatus($id,VALUE_Status_Error);
						$rtn = false;
					}

					return $rtn;

				case 'notify':
$this->log('notify['.$id.']');
					// 添付ファイルが必要ならつける
					$qdata = $this->chkAttach($qdata);
					if($this->SendMsg($qdata,'')){
						$this->Mailqueue->setStatus($id,VALUE_Status_Done);
						// 	Errmail テーブルにもフラグをセット
						$this->loadModel('Errmails.Errmail');
						$this->Errmail->setNotifyFlg($id,'Y');
					} else {
						$this->Mailqueue->setStatus($id,VALUE_Status_Error);
						$rtn = false;
					}
					return $rtn;


				case 'auto_pwd':
$this->log('auto_pwd');
					if(isset($qdata['Mailqueue']['mail_data']['viewVars']['user_id'])){
						$uid = $qdata['Mailqueue']['mail_data']['viewVars']['user_id'];
						// パスワード発行、有効期限設定（強制）
$this->log('mkOneTimePwd 4');
						$newpwd = $this->User->mkOneTimePwd($uid,false);

						$this->loadModel('MySecurity');
						$limit = $this->MySecurity->get_password_item('time_limit');
						$expdate = $this->getexpday($limit);

						$this->User->setPwdExpdate($uid,$expdate);
						$user = $this->User->findById($uid);

						$qdata['Mailqueue']['mail_data']['viewVars']['expdate'] = $expdate;

						if($this->SendMsg($qdata,$newpwd)){
							$this->Mailqueue->setStatus($id,VALUE_Status_Done);
						} else {
							$this->Mailqueue->setStatus($id,VALUE_Status_Error);
							$rtn = false;
						}
					} else {
						$rtn = false;
					}
					return $rtn;

				case 'user_add':
$this->log('user_add');
					// ステータスを　「処理中」に
					$this->Mailqueue->setStatus($id,VALUE_Status_Doing);
					$sendrc = $this->SendMsg($qdata,'pwd');
					$data_ary = $qdata['Mailqueue']['mail_data']['viewVars']['data'];
					$logid = $qdata['Mailqueue']['eventlog_id'];
					if($sendrc){
$this->log('パスワードがあるのでキューに登録２');
						$this->Mailqueue->MkPassSend($data_ary,$logid,'auto_pwd',VALUE_Mail_Priority_Urgent);
						$this->Mailqueue->setStatus($id,VALUE_Status_Done);
					} else {
						// 送信エラー
						$this->Mailqueue->setStatus($id,VALUE_Status_Error);
						$rtn = false;
					}
					return $rtn;


				case 'zip_pwd':		// zip パスワード
$this->log('zip_pwd');
					if($this->SendMsg($qdata)){
						$this->Mailqueue->setStatus($id,VALUE_Status_Done);

					} else {
						$this->Mailqueue->setStatus($id,VALUE_Status_Error);
					$rtn = false;
					}
					return $rtn;

				case 'aprv_apply':	// 承認依頼
				case 'aprv_o':		// 承認
				case 'aprv_x':		// 却下
				case 'lo_apply':	// ロック解除申請
$this->log('aprv_apply');
					if($this->SendMsg($qdata)){
						$this->Mailqueue->setStatus($id,VALUE_Status_Done);
					} else {
						$this->Mailqueue->setStatus($id,VALUE_Status_Error);
						$rtn = false;
					}
					return $rtn;

				case 'lo_notify':	// ロック解除通知
$this->log('lo_notify');
					if($this->SendMsg($qdata)){
						$this->Mailqueue->setStatus($id,VALUE_Status_Done);
					} else {
						$this->Mailqueue->setStatus($id,VALUE_Status_Error);
						$rtn = false;
					}

					return $rtn;

				case 'lo_notify_p':	// ロック解除通知(パスワード変更あり)
$this->log('lo_notify_p');
					if($this->SendMsg($qdata)){
						$data_ary = $qdata['Mailqueue']['mail_data']['viewVars']['data'];
						$logid = $qdata['Mailqueue']['eventlog_id'];
						$this->Mailqueue->MkPassSend($data_ary,$logid,'auto_pwd',VALUE_Mail_Priority_Urgent);
						$this->Mailqueue->setStatus($id,VALUE_Status_Done);
					} else {
						$this->Mailqueue->setStatus($id,VALUE_Status_Error);
						$rtn = false;
					}
					return $rtn;

				case 'mail_test':  //メールサーバ設定テストメール送信
					if($this->SendMsg($qdata,'','',$mailServer)){
						$this->Mailqueue->setStatus($id,VALUE_Status_Done);
					} else {
						$this->Mailqueue->setStatus($id,VALUE_Status_Error);
						$rtn = false;
					}
					return $rtn;


				default:
$this->log('default(送信未実装)');
					return $rtn;


			}

			$this->loadModel('Content');
			$this->Content->setEventStatus($qdata['Mailqueue']['eventlog_id']);
			// ここで content のステータス変更
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. $e->getMessage());
			$rtn = false;
		}
		return $rtn;
	}




/**
 * function　formatSno
 * @todo 	シリアル番号のフォーマット
 * @param	string $sno  : もとになるSNO
 * @param 	string $type : メールタイプ
 * @return  bool
 */
	function formatSno($sno = null, $type = null){
		try{
			// シリアル番号
			if(is_null($sno)) return '';
			$prefix = '';
			switch ($type){
				case 'upload':
					$prefix = MAIL_SEL_PREFIX .'_'. MAIL_SEL_PREFIX_SEND . '_';
					break;
				case 'login_pwd':
					$prefix = MAIL_SEL_PREFIX .'_'. MAIL_SEL_PREFIX_PASSWORD . '_';
					break;
				case 'zip_pwd':
					$prefix = MAIL_SEL_PREFIX .'_'. MAIL_SEL_PREFIX_ZPWD . '_';
					break;
				default:
					$prefix = MAIL_SEL_PREFIX .'_';
//					$prefix = MAIL_SEL_PREFIX .'_'. MAIL_SEL_PREFIX_ADMIN . '_';
					break;
			}
			$_d = date(MAIL_SEL_SUFFIX_FORMAT);
			$format_sno = $prefix . $sno . $_d;
			return $format_sno;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .':' . $e->getMessage() );
			return $sno;
		}
	}
/**
* func name
* @brief to do
* @param mix $data :
* @param mix $pwd :
* @retval mix $ret :
*/
	function sendMsg($data , $pwd = '',$zpwd = '',$mailServer = array()){

		$rtn = null;
		$qdata = null;
		$_q = null;
		$_mySno	= null;
		try{
			$auth = CakeSession::read('auth');
$this->log('sendMsg start!!!',LOG_DEBUG);
			$this->loadModel('Mailqueue');
			if(is_array($data)){
				if(isset($data['Eventlog'])){
					$qdata = $data;
				} else {
					$this->Mailqueue->recursive = 0;
					$qdata = $this->Mailqueue->findById($data['Mailqueue']['id']);
				}
			} else {
				/**
				* ハッシュでないときは id かもしれないので queue を読む
				*/
				if(is_numeric($data)){
					$this->Mailqueue->recursive = 0;
					$qdata = $this->Mailqueue->findById($data);
				}
			}
			if(is_array($qdata)){
				// array ならばOK
			} else {
				//!< @brief メールの元になるデータがなかった
				return false;
			}

			//メールサーバのロールがONかどうか
			$this->loadModel('Role');
			$mailserv_on = $this->Role->chkRole($auth['group_id'],array(
				'controller' => 'users',
				'action' => 'mail_server',
			));

			//テストメール送信の時
			if (isset($mailServer['user_name'])){
				$this->mail_init_test($mailServer);
			//通常のメール
			}else{
				// メールサーバ設定の取得
				$mail_data = $this->User->mailServer($data['Eventlog']['user_id']);
				// メールサーバのロールONかつメールサーバ設定ONかつアップロードまたはパスワード通知,ZIP通知（'upload''login_pwd''zip_pwd'）のとき
				if ($mailserv_on
					&& $mail_data['UserExtension']['server_flg'] == 1
					&& ($data['Mailqueue']['template'] == 'upload' || $data['Mailqueue']['template'] == 'login_pwd'
					|| $data['Mailqueue']['template'] == 'zip_pwd'
					)){

					$this->mail_init_on($mail_data);
				}else{
					// OFFのとき
					$this->mail_init($data);
				}
			}
			/**
			 *  テーマがあるときはここで設定しないと見てくれない
			 *  テーマの場所は
			 *  app/View/Themed/mytheme/Emails/text/xxxx.ctp
			 */
			$_mytheme = Configure::read('Config.theme');
$this->log('mail theme['.$_mytheme.']');
			$this->email->theme($_mytheme);
			/**
			* メールパラメータ設定（汎用）
			*/
			$_q = $qdata['Mailqueue'];
			$_h = (empty($_q)) ? array() : $_q['mail_data'];
			$_v = (empty($_h)) ? array() : $_h['viewVars'];

			$_lang = $_q['lang'];
			$this->setLang($_lang);

			/**
			* Header Comment(sno)
			*/
			$_mySno = $this->formatSno($_q['sno'],$_q['template']);
			if(strlen($_mySno) > 0){
				// メールヘッダ
				$this->email->addHeaders(array('Comment'=> $_mySno ));
				// メール本文
				$_v['sno'] = $_mySno;
			}
			if(strlen($pwd) > 0){
				// ログインパスワード
				$_v['loginpwd'] = $pwd;
			}
//			if(strlen($zpwd) > 0){
				// zip　解凍パスワード
//				$_v['zpwd'] = $zpwd;
//			}

			/**
			* メールの組み立て
			*/
			foreach($_h as $key => $val){
				switch($key){
					case 'template':
						$this->email->template($val[0],$val[1]);
						break;
					case 'viewVars':
						$this->email->viewVars(array('content' => $_v));
						break;

					case 'sender':
						// sender を明示的に示すのはおそらくやらないと思う
						foreach($val as $k => $v){
$this->log('set sender['.$k.'] ['.$v.']');
							$this->email->sender($k,$v);
							break;
						}
						break;

					case 'to':
$this->log('set to['.$key.'] ['.$val.']');
						if(VALUE_Mail_Transport == 'Smtp' && defined('VALUE_Mail_TEST_TO')){
$this->log('to デバッグモード');
							// 本来の受信者の情報を入れたいけど入れられない
							$this->email->$key(VALUE_Mail_TEST_TO);
						} else {
$this->log('本番');
                            $addresses = explode(',', $val);
                            $this->email->to($addresses);
						}
						break;

					case 'cc':
					case 'bcc':
$this->log('cc,bcc ');

						if(VALUE_Mail_Transport == 'Smtp' && defined('VALUE_Mail_TEST_TO')){
$this->log('['.$key.'] デバッグモード（スキップ）');
						} else {
                            $addresses = explode(',', $val);
                            $this->email->$key($addresses);
						}
						break;

					default:
						$this->email->$key($val);
						break;
				}
			}
$this->log('送信スタート');
			$rtn = $this->email->send();
$this->log('送信終了 ここにくるのはとりあえずSMTP送信できたとき');
$this->log('送信成功（SMTP）['.$qdata['Status']['id'].']');
			if(!empty($qdata['Status']['id'])){
				// 宛先の受信トレイに表示
				$this->loadModel('Status');
				$this->Status->setStatus($qdata['Status']['id'],VALUE_StatusStatusCode_On);
			}
			$headers = $this->email->getHeaders(array('charset' , 'from', 'to','sender'));
//$this->log($headers);
// テーマチェンジについては後日検証
//debug($this->email->theme());
			$user_id_to = $qdata['Status']['user_id'];
			if($user_id_to == null){
				$this->loadModel('User');
				$user_id_to = $this->User->getIDfromEmail($_q['mail_to']);
			}
            $host = $this->email->config();
			$log = array(
							'mail_from' => $_q['mail_from'],
							'mail_to' => $_q['mail_to'],
							'sno' => $_mySno,
//							'user_id' => $qdata['Eventlog']['user_id'],
							'user_id_from' => $qdata['Eventlog']['user_id'],
							'user_id_to' => $user_id_to,
							'content_id' => $qdata['Eventlog']['content_id'],
							'status_id' => $qdata['Status']['id'],
							'eventlog_id' => $qdata['Eventlog']['id'],
                            'mail_host' => $host['host'],
						);
			$this->loadModel('Maillog');
			$maillog_id = $this->Maillog->insertLog($rtn,$headers,$log);

            return $maillog_id;
		} catch (Exception $e1){
$this->log(__FILE__ .':'. __LINE__ .': '. $e1->getMessage(),LOG_DEBUG);
$this->log('送信失敗（SMTP）['.@$qdata['Status']['id'].']',LOG_DEBUG);
			// エラーでもメールログに登録する
			try{
				$headers = $this->email->getHeaders();
				$headers['err'] = $e1->getMessage();
				$headers['message'] = $this->email->message(VALUE_Mail_Type);
				$user_id_to = $qdata['Status']['user_id'];
				if($user_id_to == null){
					$this->loadModel('User');
					$user_id_to = $this->User->getIDfromEmail($_q['mail_to']);
				}
                $host = $this->email->config();
				$log = array(
								'mail_from' => $_q['mail_from'],
								'mail_to' => $_q['mail_to'],
								'sno' => $_mySno,
	//							'user_id' => $qdata['Eventlog']['user_id'],
								'user_id_from' => $qdata['Eventlog']['user_id'],
								'user_id_to' => $user_id_to,
								'content_id' => $qdata['Eventlog']['content_id'],
								'status_id' => $qdata['Status']['id'],
								'eventlog_id' => $qdata['Eventlog']['id'],
                                'mail_host' => $host['host'],

							);
				$this->loadModel('Maillog');
				$maillog_id = $this->Maillog->insertLog($rtn,$headers,$log);
			} catch(Exception $e2){
$this->log(__FILE__ .':'. __LINE__ .': '. $e2->getMessage(),LOG_DEBUG);
			}
			return false;
		}
	}


/**
 * chkAttach
 * @todo 	添付データがあるかチェック(Errmail の　Notify のみで使用)
 * @return   array
 */
	function chkAttach($data = array()){
		try{
			$has_attach = Configure::read('ERRMAILS_SendNotify_HasAttach');
//$this->log('chkAttach['.$has_attach.']');
			if($has_attach){
				$fname = Hash::get($data,'Mailqueue.mail_data.viewVars.data.Errmail.fname');
//$this->log($fname);
				if($fname){
//$this->log('あります');
					$this->loadModel('Errmails.Errmail');
					$path = $this->Errmail->FileExists($fname);
//$this->log($path);
					if($path != null){
						$attach = array( ERRMAILS_Attachment_Filename =>
									array(	'file' => $path,
											'mimetype' => ERRMAILS_Bounced_Mail_MIMETYPE
									));
						$data['Mailqueue']['mail_data']['attachments'] = $attach;
					}
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $data;
	}

/**
 * chkAttachInq
 * @todo 	添付データがあるかチェック(Errmail の　問い合わせのみで使用)
 * @return   array
 */
	function chkAttachInq($data = array()){
		try{
			$errmail_id = Hash::get($data,'Mailqueue.mail_data.viewVars.data.Inquiry.errmail_id');
			$this->loadModel('Errmails.Errmail');
			$this->Errmail->recursive = -1;
			$errmail = $this->Errmail->find('first',array(
				'conditions' => array('id' => $errmail_id),
				'fields' => array('id','fname')));
			$path = $this->Errmail->FileExists($errmail['Errmail']['fname']);
			if($path != null){
				$attach = array( ERRMAILS_Attachment_Filename =>
							array(	'file' => $path,
									'mimetype' => ERRMAILS_Bounced_Mail_MIMETYPE
							));
				$data['Mailqueue']['mail_data']['attachments'] = $attach;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $data;
	}

/**
 * test
 * @todo 	send を発行すると本当にメールが飛ぶ
 * @return   void
 */
	function test(){
debug($this->email);
		$rtn = $this->email->from(array('dog@jsy.ne.jp' => 'DOG'))
					->to('ohishi@jsy.co.jp')
					->subject('TEST')
					->send('My Message');
//			$rtn = $this->email->send();
debug($rtn);
		debug($rtn['headers']);
		debug($rtn['message']);

	}


}
