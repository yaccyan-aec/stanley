<?php
App::uses('AppModel', 'Model');
App::uses('Crypt','Vendor');
/**
 * Reserve Model
 *
 * @property Content $Content
 * @property Syslog $Syslog
 */
class Mailqueue extends AppModel {

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
	var $sanitizeItems = array(
								'mail_data' => array('html' => false,
												'serialize' => true,
												'encode' => 'none',
												));

	// 削除フラグ（tinyint) => 削除日(datetime) のフィールド名カスタマイズ
	// デフォルトは 'deleted' => 'deleted_date'
/**
 * Use behavior
 *
 * 削除フラグ（tinyint) => 削除日(datetime) のフィールド名カスタマイズ
 * デフォルトは 'deleted' => 'deleted_date'
 */
	var $actsAs = array('SoftDelete' => array(
			'is_deleted' => 'deleted',
		));

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
		'eventlog_id' => array(
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
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Eventlog' => array(
			'className' => 'Eventlog',
			'foreignKey' => 'eventlog_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Status' => array(
			'className' => 'Status',
			'foreignKey' => 'status_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

/**
 * putQueue
 * @todo 	キューに登録送信通知 と パスワード通知
 * @param	int $cid : Content ID
 * @param 	int $logid : EventLog のID
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @param   array :メールサーバ設定
 * @return  bool
 */
	function putQueue($type, $id_1 = 0, $id_2 = 0, $priority = null, $mailServer = array()){
		$rc = true;
		$_lang = $this->getLang();		// 画面言語をセーブ
$this->log("#### putQueue start lang[".$_lang."]");
		try{
$this->log("putQueue start type[".$type."]");
			switch($type){
				case 'tmppwd':			// 仮パスワード通知
$this->log("tmppwd start ");
					$rc = $this->TmpPwdSend($id_1,$id_2,VALUE_Mail_Priority_Urgent);
					break;

				case 'mkpass':			// パスワード通知
$this->log("mkpass start ");
//	function MkPassSend($data_ary,$logid,$template,$priority = 50){
					$rc = $this->MkPassSend($id_1,$id_2,$type,VALUE_Mail_Priority_Urgent);
					break;
//				case 'mkpass_force':			// パスワード通知　（強制変更）
//$this->log("mkpass_force start ");
//	function MkPassSend($data_ary,$logid,$template,$priority = 50){
//					$rc = $this->MkPassSendForce($id_1,$id_2,'mkpass',VALUE_Mail_Priority_Urgent);
//					break;

				case 'inquiry':			// お問い合わせ
				case 'err_inquiry':		// 不達お問い合わせ
$this->log("inquiry start [".$type."]");
					if(is_null($priority)){
						$rc = $this->InquirySend($id_1,$id_2,$type,VALUE_Mail_Priority_Urgent);
					} else {
						$rc = $this->InquirySend($id_1,$id_2,$type,$priority);
					}
					break;

				case 'lo_apply':		// ロック解除申請 VALUE_Mail_Priority_Urgent
$this->log("lockout_apply start ");
					$rc = $this->ApplySend($id_1,$id_2,$type,VALUE_Mail_Priority_Urgent);
					break;
				case 'lo_notify':		// ロック解除通知(パスワード変更なし) VALUE_Mail_Priority_Urgent
				case 'lo_notify_p':		// ロック解除通知(パスワード変更あり)VALUE_Mail_Priority_Urgent
$this->log("lo_notify　ロック解除通知 start ");
					$rc = $this->LockoutNotifySend($id_1,$id_2,$type,VALUE_Mail_Priority_Urgent);
//					if($type == 'lo_notify_p'){
//						// パスワード通知
//$this->log("lo_notify　ロック解除　パスワード通知 start ");
//						$rc = $this->MkPassSend($id_1,$id_2,'mkpass',VALUE_Mail_Priority_Urgent);
//					}
					break;

				case 'exp_apply':		// 期限延長申請
$this->log("exp_apply start ");
					$rc = $this->ApplySend($id_1,$id_2,$type,VALUE_Mail_Priority_Urgent);
					break;

				case 'user_edit':		// ユーザ変更
				case 'user_add':		// ユーザ追加
$this->log("[".$type."] start ");
					$rc = $this->UserEditSend($id_1,$id_2,$type,VALUE_Mail_Priority_Urgent);
					break;

				case 'upload':			// 送信
$this->log("Content start ");
					$rc = $this->ContentSend($id_1,$id_2,VALUE_Mail_Priority_Normal);
					break;

				case 'login_pwd':		// ワンタイムパスワード
$this->log("One Time pwd start ");
					$rc = $this->OneTimePwd($id_1,'new',VALUE_Mail_Priority_Normal);
					break;

				case 'zip_pwd':			// zip パスワード VALUE_Mail_Priority_Normal
$this->log("zip_pwd　start");
					$rc = $this->ZipPwd($id_1,VALUE_Mail_Priority_Normal);
					break;

				case 'aprv_apply':		// 承認依頼 VALUE_Mail_Priority_High
$this->log("Approval start ");
					$rc = $this->ApprovalSend($id_1, $id_2,$type,VALUE_Mail_Priority_High);
					break;
				case 'aprv_o':			// 承認通知 VALUE_Mail_Priority_High
				case 'aprv_x':			// 却下通知 VALUE_Mail_Priority_High
$this->log("Approval Result start ");
					$rc = $this->ApprovalResultSend($id_1, $id_2,$type,VALUE_Mail_Priority_High);
					break;

				case 'notify':			// 不達通知
$this->log("notify start ");
					if(is_null($priority)){
						$rc = $this->NotifySend($id_1,$id_2,$type,VALUE_Mail_Priority_Normal);
					} else {
						$rc = $this->NotifySend($id_1,$id_2,$type,$priority);
					}
					break;

				case 'alert_exp':			// 期限切れ予告
				case 'alert_expend':		// 期限切れ通知
$this->log("alert_exp start ");
					$rc = $this->AlertSend($id_1,$id_2,$type,VALUE_Mail_Priority_Normal);
					break;

				case 'upload_over':		// アップロード超過アラート
$this->log("upload_over start ");
					$rc = $this->UploadAlertSend($id_1,$id_2,$type,VALUE_Mail_Priority_Normal);
					break;

				case 'abort_pscan':		// PSCAN 強制キャンセルアラート
$this->log("abort_pscan start ");
					$rc = $this->PscanAlertSend($id_1,$id_2,$type,VALUE_Mail_Priority_Normal);
					break;

				case 'mail_test':		// メールサーバ設定のテストメール送信
$this->log("mailserver_test start ");
					$rc = $this->MailTestSend($mailServer,$id_2,VALUE_Mail_Priority_High);
					break;

				default :
$this->log("その他（未実装）");
					break;

			}
			$this->setLang($_lang);	// 画面言語を戻す
$this->log("Mailqueue end rc[" . $rc . "]");

			return $rc;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		$this->setLang($_lang);		// 画面言語を戻す
		return false;
	}


/**
 * ContentSend
 * @todo 	送信通知 と パスワード通知
 * @param	int $cid : Content ID
 * @param 	int $logid : EventLog のID
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */
	function ContentSend($cid,$logid,$priority = 50){
		// 言語のセーブ
$this->log("uploadMail start cid[".$cid."] evlog[".$logid."]");
		$_rc = true;
		try{
			$this->loadModel('Content');
			$this->loadModel('Maillog');
			$this->loadModel('Status');
            // ここで有効期限の最終調整
            $this->Content->setExpdate($cid);
			$this->Content->recursive = 2;
			$content = $this->Content->findById($cid);
//$this->log($content);
			if(empty($content)) return false;
			$ary = array();
			$err = 0;

			foreach($content['Status'] as $k => $v){
				// キューに登録
				if($this->q_send($content,$v,$logid,$priority)){

					// 登録成功
				} else {
					// 登録失敗
					$_rc = false;
					$err++;
				}
$this->log('---------- cc , bcc をクリア1');
//$this->log($content);
					if(Hash::check($content,'Content.cc')){
$this->log('---------- cc をクリア1');
						$content = Hash::remove($content,'Content.cc');
					}
					if(Hash::check($content,'Content.bcc')){
$this->log('---------- bcc をクリア1');
						$content = Hash::remove($content,'Content.bcc');
					}
//$this->log($content);

			}
$this->log('あらためてよみなおし');
			$content = $this->Content->findById($cid);
//$this->log($content);
			if($content['Content']['status_code'] == 0){
$this->log('キューに登録するだけだったとき');
				if($err == 0){
					// 送信予約成功
$this->log('call content setstatus ==== A');
					$this->Content->setStatus($content['Content']['id'],VALUE_Status_Waiting);
				} else {
					// 送信予約に失敗がある
$this->log('call content setstatus ==== B');
					$this->Content->setStatus($content['Content']['id'],VALUE_Status_Error);
				}
			}
			return $_rc;
		} catch(Exception $e){
$this->log("send err:".$e->getMessage());
		}
		return false;
	}
/**
 * makeURI
 * @todo 	メールに記載するアドレスをつくる
 * @param 	bool $encode  true:uri　エンコードあり / false:uri エンコードなし
 * @return   void
 */
	function makeURI($param = array(),$encode = MAIL_URI_ENCODE){
		// ここで url エンコードされる
		$_url = Router::url($param);
		if(!$encode){
			// URI　エンコードを元に戻す
			$_url = urldecode($_url);
		}
		$_base = Router::fullBaseUrl();
//		$uri = $_base . DS . MY_APP . $_url;
		$uri = $_base . $_url;
		return($uri);
	}

/**
 * makeSno
 * @todo 	メールごとのシリアル番号作成
 * @return   void
 */
	function makeSno($content,$status){
		$_sno = 'C' . $content['Content']['id'];
		$_sno .= 'W' . $content['User']['id'];
		$_sno .= 'S'. $status['id'];
		return $_sno;
	}
	function makeSystemSno($type,$user = array()){
		$_sno = '';
		try{
$this->log('makeSystemSno type['.$type.']',LOG_DEBUG);
			switch ($type){
				case 'err_inquiry':	// 不達問合せ
					$_sno = 'EIQ_';	// 2015.09.10 変更
					return $_sno;
				case 'inquiry':		// 問い合わせ
					$_sno = 'IQ_';
					return $_sno;
				case 'aprv_apply':	// 承認依頼
					$_sno = 'AA_';
					return $_sno;
				case 'aprv_o':		// 承認
					$_sno = 'AO_';
					return $_sno;
				case 'aprv_x':		// 却下
					$_sno = 'AR_';
					return $_sno;
				case 'alert_exp':		// 期限間近のお知らせ
					$_sno = 'AL_';
					return $_sno;
				case 'alert_expend':	// 期限切れのお知らせ
					$_sno = 'ALE_';
					return $_sno;
				case 'upload_over':	// アップロード超過アラート
					$_sno = 'OVSZ_';
					return $_sno;
				case 'abort_pscan':	// Pscan 強制キャンセルアラート
					$_sno = 'ABTP_';
					return $_sno;
				// -----------------------------------------------
				// 	ここまではユーザIDを入れずに return
				// -----------------------------------------------
				case 'tmp_pwd_send':	// 仮パスワード発行
					$_sno = 'TP_';
					break;
				case 'mk_pass_send':	// パスワード発行
					$_sno = 'MP_';
					break;
				case 'exp_apply':	// 期限延長申請
					$_sno = 'EA_';
					break;
				case 'user_add':	// ユーザ追加
					$_sno = 'UA_';
					break;
				case 'user_edit':	// ユーザ編集
					$_sno = 'UE_';
					break;
				case 'notify':		// 不達通知
					$_sno = 'NF_';
					break;
				case 'lo_apply':		// ロック解除申請
					$_sno = 'LA_';
					break;
				case 'lo_notify':		// ロック解除通知
				case 'lo_notify_p':		// ロック解除通知
					$_sno = 'LN_';
					break;
				case 'zip_pwd':		// zip パスワード通知
				// これ使わないかも
					$_sno = 'Z_';
					break;
				case 'mail_test':		// メールサーバ設定テストメール送信
					$_sno = 'MT_';
					break;
				default :
					return $_sno;
					break;
			}
			$_sno .= 'W' . $user['User']['id'];
			return $_sno;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $_sno;
	}


/**
 * q_send
 * @todo 	送信メールをキューに登録
 * @param   array $content
 * @param 	array $status
 * @param 	int   $evlogid  イベントログID
 * @return   void
 */
	function q_send($content,$status,$evlogid,$priority = 50){
		$my_lang = $this->getLang();
		$rtn = true;
		try{
			/**
			 * メールの言語を決める
			 */
			//---------------- ここから 宛先の言語
			$this->loadModel('Status');
			$to_lang = $this->Status->getToLang($status['id']);
			$this->setLang($to_lang);

			// 送信者の名前
			$this->loadModel('User');
$this->log('------------ q_send');
$this->log($this->User->validationErrors);
			$fromary = $this->User->getFromNames($content['User']['id'],$to_lang);
			$from = implode(' : ',$fromary);
			// ユーザ有効期限の設定が必要なら変更
$this->log('------------ setGuestExpdate start ');
			$this->User->setGuestExpdate($status['email'],$content['Content']['expdate']);
$this->log('------------ setGuestExpdate end ');

			// 受信者の名前
			$toary = $this->Status->getToNamesFromUser($status['id'],$to_lang);
//			$toary = $this->Status->getToNames($status['id'],$to_lang);
			$to = implode(' : ',$toary);

			// ログインURI
			$uri = $this->makeURI(array(
									'controller' => 'users',
									'plugin' => null,
									'action'=>'login',
									'i' => $status['email'],
									'?' => array ('lang' => $to_lang)));
			// シリアル番号
			$sno = $this->makeSno($content,$status);

			/**
			 * 本文テンプレート用パラメータ設定
			 */
			$viewVars = array(
				'from' => $from,
				'to' => $to,
				'message' => $content['Content']['message'],
				'uri' => $uri,
				'lang' => $to_lang,
				'loginid' => $status['email'],
				'loginpwd' => null,
				'title' => $content['Content']['title'],
				'uploadfile_count' => $content['Content']['uploadfile_count'],
				'uploadfile_totalsize' => $content['Content']['uploadfile_totalsize'],
				'expdate' => $content['Content']['expdate'],
				'param' => array(
									'controller' => 'users',
									'plugin' => null,
									'action'=>'login',
									'i' => $status['email'],
									'?' => array ('lang' => $to_lang)
				),
			);

			/**
			 * CakeEmail 用パラメータ設定
			 */
			$reply_to =  $content['User']['email'];

			$data = array(
				'from' => (array( $content['User']['email'] => '【'.$from.'】')),
				'to'   => $status['email'],
// ------------------- エラーメール戻り先↓	パスワード通知も同じものがコピーされる
				'replyTo' => $content['User']['email'],
				'subject' => MAIL_SUBJECT_PREFIX . $content['Content']['title'],
				'emailFormat' => VALUE_Mail_Type,
				'template' => array('upload','default'),	// template,layout
				'viewVars' => $viewVars,
				);
			// プラグインが入っていないときはユーザに返す
			if(Hash::check($content,'Content.cc')){
$this->log('------------ cc いれます');
				$data['cc'] = $content['Content']['cc'];
			}
			if(Hash::check($content,'Content.bcc')){
				$data['bcc'] = $content['Content']['bcc'];
			}

			// returnPathの設定
            //$data = $this->setReturnPath($data,$content['User']['email']);

			$this->recursive = -1;
			$qdata = $this->create();
			$qdata[$this->name]['sno'] = $sno;
			$qdata[$this->name]['priority'] = $priority;
			$qdata[$this->name]['template'] = 'upload';
			$qdata[$this->name]['mail_from'] = $content['User']['email'];
			$qdata[$this->name]['mail_to'] = $status['email'];
			$qdata[$this->name]['lang'] = $to_lang;
			$qdata[$this->name]['eventlog_id'] = $evlogid;
			$qdata[$this->name]['status_id'] = $status['id'];
			$qdata[$this->name]['mail_charset'] = VALUE_Mail_Charset;	// とりあえずきめうち
			$qdata[$this->name]['mail_type'] = VALUE_Mail_Type;			// とりあえずきめうち
			$qdata[$this->name]['status_code'] = VALUE_Status_Waiting;			// とりあえずきめうち
			$qdata[$this->name]['mail_data'] = $data;
			$qdata[$this->name]['modified'] = null;

			$rtn = $this->saveQueue($qdata,$priority);

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
			$rtn = false;
		}
		// 言語を戻す
		$this->setLang($my_lang);
		return $rtn;
	}
/**
 * OneTimePwd
 * @todo 	パスワード通知メールをキューに登録
 * @param   mix    $qdata : 送信用queue データ
 * @param 	string $pwd   : パスワード
 * @return   void
 */
	function OneTimePwd($qdata,$pwd = null,$priority = 50){
//$this->log($qdata);
		$my_lang = $this->getLang();
		$rtn = true;
		try{
			$to_lang = $qdata[$this->name]['lang'];
			$this->setLang($to_lang);

			$this->recursive = -1;
			$new = $this->create($qdata[$this->name]);
			if(Hash::check($new,$this->name.'.mail_data.cc')){
//$this->log('--------- del cc');
				$new = Hash::remove($new,$this->name.'.mail_data.cc');
			}
			if(Hash::check($new,$this->name.'.mail_data.bcc')){
//$this->log('--------- del bcc');
				$new = Hash::remove($new,$this->name.'.mail_data.bcc');
			}
$this->log('--------- q_loginpwd');
			$new[$this->name]['id'] = null;
			$new[$this->name]['template'] = 'login_pwd';
			$new[$this->name]['mail_data']['template'][0] = 'login_pwd';

			$new[$this->name]['created'] = null;
			$new[$this->name]['priority'] = $priority;
			$new[$this->name]['mail_data']['subject'] = MAIL_SUBJECT_PREFIX . __d('mail','Information of password');
			$new[$this->name]['mail_data']['viewVars']['loginpwd'] = $pwd;
//			$new[$this->name]['mail_data']['viewVars']['expdate'] = $pwd_expdate;

			$rtn = $this->saveQueue($new,$priority);
//	$this->log($rtn);
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
			$rtn = false;
		}
		// 言語を戻す
		$this->setLang($my_lang);
		return $rtn;
	}

/**
 * setReturnPath
 * @todo 送信関係のヘッダのリターンパス設定
 * @param    array $data :
 * @param    string $address :
 * @return   array
 */
	public function setReturnPath($data, $address = null){
		try{
			if(defined('ERRMAILS')){
				// プラグインを使用するとき
				// エラーメール受付専用アドレス（別サーバでもいいのかなぁ
				$data['returnPath'] = ERRMAIL_ADDRESS;
			} else {
				// プラグインがないときはデフォルト
				if($address == null){
				} else {
					// 各ユーザに返したいときは動的に変更できる
					$data['returnPath'] = $address;
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $data;
	}

/**
 * setCc
 * @todo 送信関係のヘッダのリターンパス設定
 * @param    array $data :
 * @param    string $address :
 * @return   array
 */
	public function setCc($data, $address = null){
		try{
			if(defined('ERRMAILS')){
				// プラグインを使用するとき
				// エラーメール受付専用アドレス（別サーバでもいいのかなぁ
				$data['returnPath'] = ERRMAIL_ADDRESS;
			} else {
				// プラグインがないときはデフォルト
				if($address == null){
				} else {
					// 各ユーザに返したいときは動的に変更できる
					$data['returnPath'] = $address;
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $data;
	}

/**
 * setBcc
 * @todo 送信関係のヘッダのリターンパス設定
 * @param    array $data :
 * @param    string $address :
 * @return   array
 */
	public function setBcc($data, $address = null){
		try{
			if(defined('ERRMAILS')){
				// プラグインを使用するとき
				// エラーメール受付専用アドレス（別サーバでもいいのかなぁ
				$data['returnPath'] = ERRMAIL_ADDRESS;
			} else {
				// プラグインがないときはデフォルト
				if($address == null){
				} else {
					// 各ユーザに返したいときは動的に変更できる
					$data['returnPath'] = $address;
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $data;
	}

/**
 * saveQueue
 * @todo Queue にセーブ　重要度によってはすぐに送信
 * @param    int $id :
 * @return   bool
 */
	public function saveQueue($qdata = null, $priority = 50, $mailServer = array()){
		try{
			if(isset($qdata[$this->name])){
				$rtn = $this->save($qdata);
				// 重要度を見てすぐに送るべきならここで送る
				if($priority <= VALUE_Mail_Priority_Threshold){
$this->log('######### ここでメールを送ります');
					$this->loadModel('SendMail');

					$rtn = $this->SendMail->sendFromQueue($this->getLastInsertID(),$mailServer);
$this->log('---- すぐにメールを送ったときはメール送信の結果をとる['.$rtn.']');
				}
				return $rtn;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

/**
 * setStatus
 * @todo ステータスコード設定
 * @param    int $id :
 * @param    int $status :
 * @return   bool
 */
	public function setStatus($id = null, $status = null){
		if(is_null($id)) return false;
		if(is_null($status)) return false;
		if($this->save(array(	'id' => $id,
								'status_code' => $status))){
			return true;
		}
//$this->log($this->validationErrors);
		return false;
	}

/**
 * getQueue
 * @todo キューから一番古いのを取得
 * @param    int $num : とってくる個数（デフォルトは１）
 * @return   array $list : id のリスト
 */
	public function getQueue($num = 1){
		try{
			$cond = array('status_code' => VALUE_Status_Waiting
			//						   'is_deleted' => false,
						 );
			$fields = array('id' ,'sno');
			$order = array('priority','created','id');
			$list = $this->find('list',array( 	'conditions' => $cond,
												'recursive' => -1,
												'fields' => $fields,
												'order' => $order,
												'limit' => $num));
			return($list);
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
			return $str;
		}
	}


/**
 * TmpPwdSend
 * @todo 	仮パスワード通知
 * @param	int $tid : Tmppassword ID
 * @param 	int $logid : Eventlog ID
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */
	function TmpPwdSend($tid,$logid,$priority = 50){
		// 言語のセーブ
$this->log("TmpPwdSend start tid[".$tid."] logid[".$logid."]");
		$my_lang = $this->getLang();
		$_rc = true;
		try{
			// 送信先ユーザを求める
			$this->loadModel('Tmppassword');
			$newpwd = $this->mkrandamstring(AUTOPWD_TMP_DEFAULT_LEN);
			$tmppassword = $this->Tmppassword->setpwd($tid,$newpwd);
//$this->log($tmppassword);
			if(!$tmppassword) return false;
			$tmppassword = $this->Tmppassword->findById($tid);
//$this->log($tmppassword);
			$this->loadModel('User');
			$this->User->recursive = 0;
			$user = $this->User->findByEmail($tmppassword['Tmppassword']['email']);
//$this->log($user);
			if(empty($user)) return false;

			// 同じメールの未送信があったらキャンセル
			$cond = array(	'template' => 'tmppwd',
							'mail_to' => $user['User']['email']);
			$this->cancel($cond);
			/**
			 * メールの言語を決める
			 */
			//---------------- ここから 宛先の言語
			$to_lang = $user['User']['lang'];
			$this->setLang($to_lang);
			// 送信者の名前
			$from_name = __d('mail','%s admin' ,VALUE_Package);
			$from = sprintf('【%s】 <%s>', $from_name ,VALUE_Mail_FromKey);
//			$from = sprintf('[%s] <%s>',VALUE_Mail_FromVal,VALUE_Mail_FromKey);
			// 受信者の名前

			$toary = $this->User->getFromNames($user['User']['id'],$to_lang);
			$to = implode(' : ',$toary);
			// ログインURI
			$uri = $this->makeURI(array(
									'controller' => 'users',
									'plugin' => null,
									'action'=>'mkpass',
									'?' => array (
										'lang' => $user['User']['lang'],
										'loginid' => $user['User']['email'],
										'tmp' => $newpwd
									)));
//$this->log($uri);
			// シリアル番号
			$sno = $this->makeSystemSno('tmp_pwd_send',$user);
//$this->log($sno);
			/**
			 * 本文テンプレート用パラメータ設定
			 */
			$theme = Configure::read('Config.theme');

			$title = __d($theme,'Infomation of Temporary Password');
			$viewVars = array(
				'from' => $from,
				'to' => $to,
				'uri' => $uri,
				'loginid' => $user['User']['email'],
				'loginpwd' => $newpwd,
				'title' => $title ,
				'expdate' => $tmppassword['Tmppassword']['expdate'],
				'lang' => $to_lang,
				'param' => array(
					'controller' => 'users',
					'plugin' => null,
					'action'=>'mkpass',
					'?' => array (
						'lang' => $user['User']['lang'],
						'loginid' => $user['User']['email'],
						'tmp' => $newpwd
				)),
			);

			/**
			 * CakeEmail 用パラメータ設定
			 */
			$data = array(
				'from' => (array(VALUE_Mail_FromKey => '【'.$from_name .'】')),
//				'sender' => (array(VALUE_Mail_FromKey => '['.$from_name .']')),
				//				'from' => (array(VALUE_Mail_FromKey => '['.VALUE_Mail_FromVal.']')),
				'to'   => $user['User']['email'],
				'replyTo' => VALUE_Mail_FromKey,
				'subject' => MAIL_SUBJECT_PREFIX . $title ,
				'emailFormat' => VALUE_Mail_Type,
				'template' => array('tmppwd','default'),	// template,layout
				'viewVars' => $viewVars,
				);

			$this->recursive = -1;
			$qdata = $this->create();
			$qdata[$this->name]['sno'] = $sno;
			$qdata[$this->name]['priority'] = $priority;
			$qdata[$this->name]['template'] = 'tmppwd';
			$qdata[$this->name]['mail_from'] = VALUE_Mail_FromKey;
			$qdata[$this->name]['mail_to'] = $user['User']['email'];
			$qdata[$this->name]['lang'] = $user['User']['lang'];
			$qdata[$this->name]['eventlog_id'] = $logid;
			$qdata[$this->name]['status_id'] = 0;
			$qdata[$this->name]['mail_charset'] = VALUE_Mail_Charset;	// とりあえずきめうち
			$qdata[$this->name]['mail_type'] = VALUE_Mail_Type;			// とりあえずきめうち
			$qdata[$this->name]['status_code'] = VALUE_Status_Waiting;			// とりあえずきめうち
			$qdata[$this->name]['mail_data'] = $data;
			$qdata[$this->name]['modified'] = null;
//$this->log($qdata);
			$rtn = $this->saveQueue($qdata,$priority);
		} catch(Exception $e){
			$rtn = false;
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		$this->setLang($my_lang);
		return $rtn;
	}


/**
 * MkPassSend
 * @todo 	パスワード通知
 * @param	array $data_ary:
 *   [User] => Array
 *       (
 *           [id] => null
 *           [email] => 'xxxx@aaa.co.jp'
 *           [pwd] => 'xxxxxxxx'
 *       )
 * @param 	int $logid : Eventlog ID
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */
	function MkPassSend($data_ary,$logid,$template,$priority = 50){
		// 言語のセーブ
$this->log("MkPassSendForce start logid[".$logid."] temp[".$template."]");
		$my_lang = $this->getLang();
		$_rc = true;
		try{
			/**
			 * メールの言語を決める
			 */
			$this->loadModel('User');
			$this->User->recursive = -1;
			$user = $this->User->findByEmail($data_ary['User']['email']);
			if(!$user){
$this->log('user is null');
				return false;
			}
			// 同じメールの未送信があったらキャンセル
			$cond = array(	'template' => $template,
							'mail_to' => $user['User']['email']);
			$this->cancel($cond);


			//---------------- ここから 宛先の言語
			$to_lang = $user['User']['lang'];
			$this->setLang($to_lang);

			// 送信者の名前
			$from_name = __d('mail','%s admin' ,VALUE_Package);
			$from = sprintf('【%s】 <%s>',$from_name ,VALUE_Mail_FromKey);
			// 受信者の名前
			$toary = $this->User->getFromNames($user['User']['id'],$to_lang);
			$to = implode(' : ',$toary);

			// ログインURI
			$uri = $this->makeURI(array(
									'controller' => 'users',
									'plugin' => null,
									'action'=>'login',
									'i' => $data_ary['User']['email'],
									'?' => array ('lang' => $to_lang)));
			// シリアル番号
			$sno = $this->makeSystemSno('mk_pass_send',$user);

			/**
			 * 本文テンプレート用パラメータ設定
			 */
			$loginpwd = null;
			$expdate = null;
			// 管理者が手動でパスワードを変更したとき
//			if($template == 'ChangePwd'){
			if(Hash::check($data_ary,'User.new_password')){
//$this->log('手動パスワード変更');
				$loginpwd = @Hash::get($data_ary,'User.new_password');
				$expdate = $user['User']['pwd_expdate'];
			} else {
				// 強制的に変更
$this->log('mkOneTimePwd 1');
				$this->loadModel('User');
				$loginpwd = $this->User->mkOneTimePwd($user['User']['id'], false);
				$new_user = $this->User->find('first',array(	'conditions' =>
																	array('id' => $user['User']['id']),
															'recursive' => -1));
				$expdate = $new_user['User']['pwd_expdate'];
			}

			$title = __d('mail','Information of password');
			$viewVars = array(
				'from' => $from,
				'to' => $to,
				'uri' => $uri,
				'loginid' => $user['User']['email'],
				'loginpwd' => $loginpwd,
				'title' => $title ,
				'expdate' => $expdate,
				'user_id' => $user['User']['id'],
				'group_id' => $user['User']['group_id'],
				'lang' => $to_lang,
				'param' => array(
					'controller' => 'users',
					'plugin' => null,
					'action'=>'login',
					'i' => $data_ary['User']['email'],
					'?' => array ('lang' => $to_lang)
				),
			);

			/**
			 * CakeEmail 用パラメータ設定
			 */
			$data = array(
				'from' => (array(VALUE_Mail_FromKey => '【'.$from_name .'】')),
				'to'   => $data_ary['User']['email'],
				'replyTo' => VALUE_Mail_FromKey,
				'subject' => MAIL_SUBJECT_PREFIX . $title ,
				'emailFormat' => VALUE_Mail_Type,
				'template' => array('mkpass','default'),	// template,layout
				'viewVars' => $viewVars,
				);
//$this->log($data);
			$this->recursive = -1;
			$qdata = $this->create();
			$qdata[$this->name]['sno'] = $sno;
			$qdata[$this->name]['priority'] = $priority;
			$qdata[$this->name]['template'] = $template;
			$qdata[$this->name]['mail_from'] = VALUE_Mail_FromKey;
			$qdata[$this->name]['mail_to'] = $user['User']['email'];
			$qdata[$this->name]['lang'] = $user['User']['lang'];
			$qdata[$this->name]['eventlog_id'] = $logid;
			$qdata[$this->name]['status_id'] = 0;
			$qdata[$this->name]['mail_charset'] = VALUE_Mail_Charset;	// とりあえずきめうち
			$qdata[$this->name]['mail_type'] = VALUE_Mail_Type;			// とりあえずきめうち
			$qdata[$this->name]['status_code'] = VALUE_Status_Waiting;	// とりあえずきめうち
			$qdata[$this->name]['mail_data'] = $data;
			$qdata[$this->name]['modified'] = null;
//$this->log($qdata);

			$rtn = $this->saveQueue($qdata,$priority);
		} catch(Exception $e){
			$rtn = false;
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		$this->setLang($my_lang);
		return $rtn;
	}

/**
 * ApplySend
 * @todo 	申請通知（ロックアウト解除申請、期限延長申請）
 * @param	array $data:
 *   [User] => Array
 *       (
 *           [email] => 'xxxx@aaa.co.jp'
 *       )
 * @param 	int $logid : Eventlog ID
 * @param	string $type : 申請タイプ
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */
	function ApplySend($data_ary,$logid,$template = null,$priority = 50){
		// 言語のセーブ
$this->log("ApplySend start logid[".$logid."]");
//$this->log($data_ary);
		$my_lang = $this->getLang();
		$_rc = true;
		try{
			/**
			 * タイプによりテンプレートを選択
			 */
			if($template == null) {
				return false;
			}
			/**
			 * メールの言語を決める
			 */
			$this->loadModel('User');
			$this->User->recursive = 0;
			$user = $this->User->findByEmail($data_ary['User']['email']);
//$this->log($user);
			if(!$user){
$this->log('user is null');
				return false;
			}
			// 同じメールの未送信があったらキャンセル
			$cond = array(	'template' => $template,
							'mail_from' => $data_ary['User']['email']);
			$this->cancel($cond);

			//---------------- ここから 宛先の言語
//			$to_lang = $user['User']['lang'];
			$to_lang = 'jpn';	// とりあえず申請は日本語だけ
			$this->setLang($to_lang);

			// 送信者の名前
			$from = sprintf('【%s】 <%s>',VALUE_Mail_FromVal,VALUE_Mail_FromKey);
			// 受信者の名前
			$to = __d('mail','%s admin' ,VALUE_Package);

			// シリアル番号
			$sno = $this->makeSystemSno($template,$user);

			/**
			 * 本文テンプレート用パラメータ設定
			 */
			$title = __d('mail',$template ,VALUE_Package);
			$viewVars = array(
				'from' => $from,
				'to' => $to,
				'user' => $user,
				'title' => $title ,
			);

			/**
			 * CakeEmail 用パラメータ設定
			 */
			$data = array(
				'from' => (array(VALUE_Mail_FromKey => '【'.$user['User']['email'].'】')),
				'to'   => OPTION_Expdate_Relese_Apply_Addrs,
				'replyTo' => VALUE_Mail_FromKey,
				'subject' => MAIL_SUBJECT_PREFIX . $title ,
				'emailFormat' => VALUE_Mail_Type,
				'template' => array($template,'default'),	// template,layout
				'viewVars' => $viewVars,
				);
//$this->log($data);
			$this->recursive = -1;
			$qdata = $this->create();
			$qdata[$this->name]['sno'] = $sno;
			$qdata[$this->name]['priority'] = $priority;
			$qdata[$this->name]['template'] = $template;
			$qdata[$this->name]['mail_from'] = $user['User']['email'];
			$qdata[$this->name]['mail_to'] = OPTION_Expdate_Relese_Apply_Addrs;
			$qdata[$this->name]['lang'] = $to_lang;	// きめうち
			$qdata[$this->name]['eventlog_id'] = $logid;
			$qdata[$this->name]['status_id'] = 0;
			$qdata[$this->name]['mail_charset'] = VALUE_Mail_Charset;	// とりあえずきめうち
			$qdata[$this->name]['mail_type'] = VALUE_Mail_Type;			// とりあえずきめうち
			$qdata[$this->name]['status_code'] = VALUE_Status_Waiting;	// とりあえずきめうち
			$qdata[$this->name]['mail_data'] = $data;
			$qdata[$this->name]['modified'] = null;
//$this->log($qdata);

			$rtn = $this->saveQueue($qdata,$priority);
		} catch(Exception $e){
			$rtn = false;
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		$this->setLang($my_lang);
		return $rtn;
	}

/**
 * InquirySend
 * @todo 	お問い合わせ送信（名寄せあり）
 * @param	array $data:
 *   [User] => Array
 *       (
 *           [email] => 'xxxx@aaa.co.jp'
 *       )
 * @param 	int $logid : Eventlog ID
 * @param	string $type : 申請タイプ
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */
	function InquirySend($iqid,$logid,$template = null,$priority = 50){
		// 言語のセーブ
$this->log("InquirySend start logid[".$logid."]");
		$my_lang = $this->getLang();
		$_rc = true;
		try{
			/**
			 * タイプによりテンプレートを選択
			 */
			if($template == null) {
				return false;
			}

			//---------------- ここから 宛先の言語
//			$to_lang = $user['User']['lang'];
			$to_lang = 'jpn';	// とりあえず申請は日本語だけ
			$this->setLang($to_lang);

			// 送信者の名前
			$from = sprintf('【%s】 <%s>',VALUE_Mail_FromVal,VALUE_Mail_FromKey);
			// 受信者の名前
			$to = __d('mail','%s admin' ,VALUE_Package);

			// シリアル番号
			$sno = $this->makeSystemSno($template);
			$this->loadModel('Inquiry');

			$data_ary = $this->Inquiry->findById($iqid);
			$sno .= $iqid;

			/**
			 * 名寄せ
			 */
			$this->loadModel('User');
			if($this->User->exists($data_ary['Inquiry']['user_id'])){
				$this->User->recursive = 0;
				$user = $this->User->findById($data_ary['Inquiry']['user_id']);
//debug($user);
				$data_ary['User'] = $user;
			}

			/**
			 * 本文テンプレート用パラメータ設定
			 */
			$title = __d('mail',$template ,VALUE_Package);
			$viewVars = array(
				'from' => $from,
				'to' => $to,
				'data' => $data_ary,
				'title' => $title ,
			);

			/**
			 * CakeEmail 用パラメータ設定
			 */
			$data = array(
				'from' => (array(VALUE_Mail_FromKey => '【'.$data_ary['Inquiry']['email'].'】')),
				'to'   => OPTION_Expdate_Relese_Apply_Addrs,
				'replyTo' => $data_ary['Inquiry']['email'],
				'subject' => MAIL_SUBJECT_PREFIX . $title ,
				'emailFormat' => 'html',
				'template' => array($template,'default'),	// template,layout
				'viewVars' => $viewVars,
				);

			$this->recursive = -1;
			$qdata = $this->create();
			$qdata[$this->name]['sno'] = $sno;
			$qdata[$this->name]['priority'] = $priority;
			$qdata[$this->name]['template'] = $template;
			$qdata[$this->name]['mail_from'] = $data_ary['Inquiry']['email'];
			$qdata[$this->name]['mail_to'] = OPTION_Expdate_Relese_Apply_Addrs;
			$qdata[$this->name]['lang'] = $to_lang;	// きめうち
			$qdata[$this->name]['eventlog_id'] = $logid;
			$qdata[$this->name]['status_id'] = 0;
			$qdata[$this->name]['mail_charset'] = VALUE_Mail_Charset;	// とりあえずきめうち
			$qdata[$this->name]['mail_type'] = 'html';					// とりあえずきめうち
			$qdata[$this->name]['status_code'] = VALUE_Status_Waiting;	// とりあえずきめうち
			$qdata[$this->name]['mail_data'] = $data;
			$qdata[$this->name]['modified'] = null;
//debug($qdata);

			$rtn = $this->saveQueue($qdata,$priority);
		} catch(Exception $e){
			$rtn = false;
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		$this->setLang($my_lang);
		return $rtn;
	}

/**
 * UserEditSend
 * @todo 	送信通知 と パスワード通知
 * @param	int $cid : Content ID
 * @param 	int $logid : EventLog のID
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */
	function UserEditSend($user,$logid,$type,$priority = 50){
		// 言語のセーブ
$this->log("UserEditSend start  evlog[".$logid."]");
		$_rc = true;
		try{
			// 新規か更新かでテンプレートを変更
			$is_new = ($user['User']['id'] == null) ? true : false;
			$template = $type;

			//---------------- ここから 宛先の言語
			$to_lang = $user['User']['lang'];
			$this->setLang($to_lang);

			// 送信者の名前
			$from_name = __d('mail','%s admin' ,VALUE_Package);
			$from = sprintf('【%s】 <%s>',$from_name,VALUE_Mail_FromKey);

			// 受信者の名前
			$this->loadModel('User');
			$toary = array();
			if($is_new){
				// 新規のときはここで　user id を求める。
				// かならずここで１つヒットするはず
				$_user = $this->User->find('all',array(
							'conditions' => array(	'email' => $user['User']['email'],
													'is_deleted' => false)));
//$this->log($_user);
				$toary = $this->User->getFromNames($_user[0]['User']['id'],$to_lang);
			} else {
				$toary = $this->User->getFromNames($user['User']['id'],$to_lang);
			}
			$to = implode(' : ',$toary);

			// シリアル番号
			$data_ary = $this->User->findByEmail($user['User']['email']);
			$sno = $this->makeSystemSno($template,$data_ary);
			// ログインURI
			$uri = $this->makeURI(array(
									'controller' => 'users',
									'plugin' => null,
									'action'=>'login',
									'i' => $user['User']['email'],
									'?' => array ('lang' => $user['User']['lang']
									)));

			/**
			 * 本文テンプレート用パラメータ設定
			 */
			$title = __d('mail',$template ,VALUE_Package);
			$viewVars = array(
				'from' => $from,
				'to' => $to,
				'data' => $data_ary,
				'login_id' => $user['User']['email'],
				'title' => $title ,
				'uri' => $uri ,
				'lang' => $to_lang,
				'param' => array(
					'controller' => 'users',
					'plugin' => null,
					'action'=>'login',
					'i' => $user['User']['email'],
					'?' => array ('lang' => $user['User']['lang'])
				),
			);

			/**
			 * CakeEmail 用パラメータ設定
			 */
			$data = array(
				'from' => array(VALUE_Mail_FromKey => '【'.$from_name.'】'),
				'to'   => $user['User']['email'],
				'replyTo' => VALUE_Mail_FromKey,
				'subject' => MAIL_SUBJECT_PREFIX . $title ,
				'emailFormat' => VALUE_Mail_Type,
				'template' => array($template,'default'),	// template,layout
				'viewVars' => $viewVars,
				);

			$this->recursive = -1;
			$qdata = $this->create();
			$qdata[$this->name]['sno'] = $sno;
			$qdata[$this->name]['priority'] = $priority;
			$qdata[$this->name]['template'] = $template;
			$qdata[$this->name]['mail_from'] = VALUE_Mail_FromKey;
			$qdata[$this->name]['mail_to'] = $data_ary['User']['email'];
			$qdata[$this->name]['lang'] = $to_lang;	// きめうち
			$qdata[$this->name]['eventlog_id'] = $logid;
			$qdata[$this->name]['status_id'] = 0;
			$qdata[$this->name]['mail_charset'] = VALUE_Mail_Charset;	// とりあえずきめうち
			$qdata[$this->name]['mail_type'] = VALUE_Mail_Type;			// とりあえずきめうち
			$qdata[$this->name]['status_code'] = VALUE_Status_Waiting;	// とりあえずきめうち
			$qdata[$this->name]['mail_data'] = $data;
			$qdata[$this->name]['modified'] = null;

			$rtn = $this->saveQueue($qdata,$priority);
			return $rtn;
		} catch(Exception $e){
$this->log("send err:".$e->getMessage());
		}
		return false;
	}

/**
 * NotifySend
 * @todo 	不達通知送信
 * @param	array $data:
 *   [User] => Array
 *       (
 *           [email] => 'xxxx@aaa.co.jp'
 *       )
 * @param 	int $logid : Eventlog ID
 * @param	string $type : 申請タイプ
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */

	function NotifySend($eid,$logid,$template = null,$priority = 0){
$this->log('NotifySend start id['.$eid.'] logid['.$logid.'] tpl['.$template.'] pri['.$priority.']');
		if(!CakePlugin::loaded('Errmails')){
$this->log("errmails　プラグインが必要です。");
			return false;
		}
		// 言語のセーブ
$this->log("NotifySend start logid[".$logid."]");
		$my_lang = $this->getLang();
		$_rc = true;
		try{
			/**
			 * タイプによりテンプレートを選択
			 */
			if($template == null) {
				return false;
			}
			$this->loadModel('Errmails.Errmail');
			$data_ary = $this->Errmail->getNotifyData($eid);
//$this->log($data_ary);
			//---------------- ここから 宛先の言語
			$to_lang = $data_ary['User']['lang'];
			$this->setLang($to_lang);

			// 送信者の名前
			$from_name = __d('mail','%s admin' ,VALUE_Package);
			$from = sprintf('【%s】 <%s>',$from_name,VALUE_Mail_FromKey);

			// 受信者の名前
			$this->loadModel('User');
			$toary = $this->User->getFromNames($data_ary['User']['id'],$to_lang);
			$to = implode(' : ',$toary);

			// シリアル番号
			$sno = $this->makeSystemSno($template, $data_ary);

			$sno .= 'E' . $eid;
			/**
			 * 本文テンプレート用パラメータ設定
			 */
			$title = __d('errmails',$template ,VALUE_Package);
			$viewVars = array(
				'from' => $from,
				'to' => $to,
				'data' => $data_ary,
				'title' => $title ,
			);
//debug($veiwVars);
			/**
			 * CakeEmail 用パラメータ設定
			 */
			$data = array(
				'from' => array(VALUE_Mail_FromKey => '【'.$from_name.'】'),
				'to'   => $data_ary['User']['email'],
				'replyTo' => VALUE_Mail_FromKey,
				'subject' => MAIL_SUBJECT_PREFIX . $title ,
				'emailFormat' => VALUE_Mail_Type,
				'template' => array('Errmails.'.$template,'default'),	// template,layout
				'viewVars' => $viewVars,
				);
//$this->log($data);
			$this->recursive = -1;
			$qdata = $this->create();
			$qdata[$this->name]['sno'] = $sno;
			$qdata[$this->name]['priority'] = $priority;
			$qdata[$this->name]['template'] = $template;
			$qdata[$this->name]['mail_from'] = VALUE_Mail_FromKey;
			$qdata[$this->name]['mail_to'] = $data_ary['User']['email'];
			$qdata[$this->name]['lang'] = $to_lang;	// きめうち
			$qdata[$this->name]['eventlog_id'] = $logid;
			$qdata[$this->name]['status_id'] = 0;
			$qdata[$this->name]['mail_charset'] = VALUE_Mail_Charset;	// とりあえずきめうち
			$qdata[$this->name]['mail_type'] = VALUE_Mail_Type;					// とりあえずきめうち
			$qdata[$this->name]['status_code'] = VALUE_Status_Waiting;	// とりあえずきめうち
			$qdata[$this->name]['mail_data'] = $data;
			$qdata[$this->name]['modified'] = null;
			$qdata[$this->name]['etc'] = $eid;	// Errmails - ID

//$this->log($qdata);

			$_rc = $this->saveQueue($qdata,$priority);
		} catch(Exception $e){
			$_rc = false;
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		$this->setLang($my_lang);
		return $_rc;
	}

/**
 * AlertSend
 * @todo 	アラートメール送信
 *			期限切れの予告などを通知
 * @param	int $user_id:
 * @param 	int $logid : Eventlog ID
 * @param	string $type : 申請タイプ
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */

	function AlertSend($user_id,$logid,$template = null,$priority = 0){
$this->log('AlertSend start user_id['.$user_id.'] logid['.$logid.'] tpl['.$template.'] pri['.$priority.']');
		// 言語のセーブ
$this->log("AlertSend start logid[".$logid."]");
		$my_lang = $this->getLang();
		$_rc = true;
		try{
			/**
			 * タイプによりテンプレートを選択
			 */
			if($template == null) {
				return false;
			}
			$this->loadModel('User');
			$data_ary = $this->User->findById($user_id);
//$this->log($data_ary);
			//---------------- ここから 宛先の言語
			$to_lang = $data_ary['User']['lang'];

			// 宛先言語が決まっていなかったらシステムデフォルト
			if($to_lang == 'auto') $to_lang = VALUE_System_Default_Lang;
			$this->setLang($to_lang);

			// 送信者の名前
			$from_name = __d('mail','%s admin' ,VALUE_Package);
			$from = sprintf('【%s】 <%s>',$from_name,VALUE_Mail_FromKey);

			// 受信者の名前
			$this->loadModel('User');
			$toary = $this->User->getFromNames($data_ary['User']['id'],$to_lang);
			$to = implode(' : ',$toary);

			// シリアル番号
			$sno = $this->makeSystemSno($template, $data_ary);

			$sno .= 'W' . $user_id;

			// ログインURI
			// バッチのときはここでは正しくドメインが取れないので
			// テンプレートの中で手作りする。
/*
			$uri = $this->makeURI(array(
									'controller' => 'users',
									'plugin' => null,
									'action'=>'login',
									'i' => $data_ary['User']['email'],
									'?' => array ('lang' => $to_lang)));
*/
			/**
			 * 本文テンプレート用パラメータ設定
			 */
			$title = __d('mail',$template ,VALUE_Package);
			$viewVars = array(
				'from' => $from,
				'to' => $to,
				'data' => $data_ary,
				'title' => $title ,
//				'uri' => $uri,
			);
//$this->log($viewVars);
			/**
			 * CakeEmail 用パラメータ設定
			 */
			$data = array(
				'from' => array(VALUE_Mail_FromKey => '【'.$from_name.'】'),
				'to'   => $data_ary['User']['email'],
				'replyTo' => VALUE_Mail_FromKey,
				'subject' => MAIL_SUBJECT_PREFIX . $title ,
				'emailFormat' => VALUE_Mail_Type,
				'template' => array($template,'default'),	// template,layout
				'viewVars' => $viewVars,
				);
//$this->log($data);
			$this->recursive = -1;
			$qdata = $this->create();
			$qdata[$this->name]['sno'] = $sno;
			$qdata[$this->name]['priority'] = $priority;
			$qdata[$this->name]['template'] = $template;
			$qdata[$this->name]['mail_from'] = VALUE_Mail_FromKey;
			$qdata[$this->name]['mail_to'] = $data_ary['User']['email'];
			$qdata[$this->name]['lang'] = $to_lang;	// きめうち
			$qdata[$this->name]['eventlog_id'] = $logid;
			$qdata[$this->name]['status_id'] = 0;
			$qdata[$this->name]['mail_charset'] = VALUE_Mail_Charset;	// とりあえずきめうち
			$qdata[$this->name]['mail_type'] = VALUE_Mail_Type;					// とりあえずきめうち
			$qdata[$this->name]['status_code'] = VALUE_Status_Waiting;	// とりあえずきめうち
			$qdata[$this->name]['mail_data'] = $data;
			$qdata[$this->name]['modified'] = null;

//$this->log($qdata);

			$_rc = $this->saveQueue($qdata,$priority);
		} catch(Exception $e){
			$_rc = false;
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		$this->setLang($my_lang);
		return $_rc;
	}

/**
 * UploadAlertSend
 * @todo 	アップロードサイズ超過アラートメール送信
 *			期限切れの予告などを通知
 * @param	int $user_id:
 * @param 	int $logid : Eventlog ID
 * @param	string $type : 申請タイプ
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */

	function UploadAlertSend($content_id,$logid,$template = null,$priority = 0){
$this->log('UploadAlertSend start content_id['.$content_id.'] logid['.$logid.'] tpl['.$template.'] pri['.$priority.']',LOG_DEBUG);
		// 言語のセーブ
		$my_lang = $this->getLang();
		$_rc = true;
		try{
			/**
			 * タイプによりテンプレートを選択
			 */
			if($template == null) {
				return false;
			}
			$this->loadModel('Content');
			$this->Content->bindModel(array('belongsTo' => array(
					'Owner' => array(
						'className' => 'User',
						'foreignKey' => 'owner_id',
						'conditions' => '',
						'fields' => '',
						'order' => ''
					),
				)),true);

			$data_ary = $this->Content->find('first',array(
				'conditions' => array('Content.id' => $content_id),
				'contain' => array(
					'User',
					'Owner'),
				'recursive' => false
				));
$this->log($data_ary,LOG_DEBUG);

			$this->loadModel('Contract');
			$contract_data = $this->Contract->find('first',array(
				'conditions' => array('Contract.id' => $data_ary['Owner']['contract_id']),
				'recursive' => -1));

$this->log($contract_data,LOG_DEBUG);
			$this->loadModel('Eventlog',true);
			$logdata = $this->Eventlog->find('first',array(
				'conditions' => array('Eventlog.id' => $logid),
				'recursive' => -1));
//$this->log($logdata,LOG_DEBUG);

			//---------------- ここから 宛先の言語
			// ここでは、システムからアドミンへのメールとする。
			// 将来宛先が変更になるときはここを調整する。

			$to_lang = 'jpn';	// とりあえず申請は日本語だけ
			$this->setLang($to_lang);

			// 送信者の名前
			$from_name = __d('mail','%s admin' ,VALUE_Package);
			$from = sprintf('【%s】 <%s>',VALUE_Mail_FromVal,VALUE_Mail_FromKey);
			// 受信者の名前
			$to = __d('mail','%s admin' ,VALUE_Package);

			// シリアル番号
			$sno = $this->makeSystemSno($template);
			$sno .= 'C'.$content_id;

			/**
			 * 本文テンプレート用パラメータ設定
			 */
			$title = __d('mail',$template ,VALUE_Package);
			$viewVars = array(
				'from' => $from,
				'to' => $to,
				'content' => $contract_data,
				'logdata' => $logdata,
				'title' => $title ,
			);

			/**
			 * CakeEmail 用パラメータ設定
			 */
			$data = array(
				'from' => array(VALUE_Mail_FromKey => '【'.$from_name.'】'),
				'to'   => OPTION_Expdate_Relese_Apply_Addrs,
				'replyTo' => VALUE_Mail_FromKey,
				'subject' => MAIL_SUBJECT_PREFIX . $title ,
				'emailFormat' => VALUE_Mail_Type,
				'template' => array($template,'default'),	// template,layout
				'viewVars' => $viewVars,
				);
//$this->log($data);
			$this->recursive = -1;
			$qdata = $this->create();
			$qdata[$this->name]['sno'] = $sno;
			$qdata[$this->name]['priority'] = $priority;
			$qdata[$this->name]['template'] = $template;
			$qdata[$this->name]['mail_from'] = VALUE_Mail_FromKey;
			$qdata[$this->name]['mail_to'] = OPTION_Expdate_Relese_Apply_Addrs;
			$qdata[$this->name]['lang'] = $to_lang;	// きめうち
			$qdata[$this->name]['eventlog_id'] = $logid;
			$qdata[$this->name]['status_id'] = 0;
			$qdata[$this->name]['mail_charset'] = VALUE_Mail_Charset;	// とりあえずきめうち
			$qdata[$this->name]['mail_type'] = VALUE_Mail_Type;			// とりあえずきめうち
			$qdata[$this->name]['status_code'] = VALUE_Status_Waiting;	// とりあえずきめうち
			$qdata[$this->name]['mail_data'] = $data;
			$qdata[$this->name]['modified'] = null;
//$this->log($qdata);

			$rtn = $this->saveQueue($qdata,$priority);

		} catch(Exception $e){
			$_rc = false;
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		$this->setLang($my_lang);
		return $_rc;
	}

/**
 * PscanAlertSend
 * @todo 	PACAN 強制キャンセルアラートメール送信（Pscan オプション使用時のみ）
 *			期限切れの予告などを通知
 * @param	int $user_id:
 * @param 	int $logid : Eventlog ID
 * @param	string $type : 申請タイプ
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */

	function PscanAlertSend($content_id,$logid,$template = null,$priority = 0){
$this->log('PscanAlertSend start content_id['.$content_id.'] logid['.$logid.'] tpl['.$template.'] pri['.$priority.']',LOG_DEBUG);
		// 言語のセーブ
		$my_lang = $this->getLang();
		$_rc = true;
		try{
			/**
			 * タイプによりテンプレートを選択
			 */
			if($template == null) {
				return false;
			}
			$this->loadModel('Content');
			$this->Content->bindModel(array('belongsTo' => array(
					'Owner' => array(
						'className' => 'User',
						'foreignKey' => 'owner_id',
						'conditions' => '',
						'fields' => '',
						'order' => ''
					),
				)),true);

			$data_ary = $this->Content->find('first',array(
				'conditions' => array('Content.id' => $content_id),
				'contain' => array(
					'User',
					'Owner'),
				'recursive' => false
				));
//$this->log($data_ary,LOG_DEBUG);

			$this->loadModel('Contract');
			$contract_data = $this->Contract->find('first',array(
				'conditions' => array('Contract.id' => $data_ary['Owner']['contract_id']),
				'recursive' => -1));

//$this->log($contract_data,LOG_DEBUG);
			$this->loadModel('Eventlog',true);
			$logdata = $this->Eventlog->find('first',array(
				'conditions' => array('Eventlog.id' => $logid),
				'recursive' => -1));
//$this->log($logdata,LOG_DEBUG);

			//---------------- ここから 宛先の言語
			// ここでは、システムからアドミンへのメールとする。
			// 将来宛先が変更になるときはここを調整する。

			$to_lang = 'jpn';	// とりあえず申請は日本語だけ
			$this->setLang($to_lang);

			// 送信者の名前
			$from_name = __d('mail','%s admin' ,VALUE_Package);
			$from = sprintf('【%s】 <%s>',VALUE_Mail_FromVal,VALUE_Mail_FromKey);
			// 受信者の名前
			$to = __d('mail','%s admin' ,VALUE_Package);

			// シリアル番号
			$sno = $this->makeSystemSno($template);
			$sno .= 'C'.$content_id;

			/**
			 * 本文テンプレート用パラメータ設定
			 */
			$title = __d('mail',$template ,VALUE_Package);
			$viewVars = array(
				'from' => $from,
				'to' => $to,
				'content' => $contract_data,
				'logdata' => $logdata,
				'title' => $title ,
			);

			/**
			 * CakeEmail 用パラメータ設定
			 */
			$data = array(
				'from' => array(VALUE_Mail_FromKey => '【'.$from_name.'】'),
				'to'   => OPTION_Expdate_Relese_Apply_Addrs,
				'replyTo' => VALUE_Mail_FromKey,
				'subject' => MAIL_SUBJECT_PREFIX . $title ,
				'emailFormat' => VALUE_Mail_Type,
				'template' => array($template,'default'),	// template,layout
				'viewVars' => $viewVars,
				);
//$this->log($data);
			$this->recursive = -1;
			$qdata = $this->create();
			$qdata[$this->name]['sno'] = $sno;
			$qdata[$this->name]['priority'] = $priority;
			$qdata[$this->name]['template'] = $template;
			$qdata[$this->name]['mail_from'] = VALUE_Mail_FromKey;
			$qdata[$this->name]['mail_to'] = OPTION_Expdate_Relese_Apply_Addrs;
			$qdata[$this->name]['lang'] = $to_lang;	// きめうち
			$qdata[$this->name]['eventlog_id'] = $logid;
			$qdata[$this->name]['status_id'] = 0;
			$qdata[$this->name]['mail_charset'] = VALUE_Mail_Charset;	// とりあえずきめうち
			$qdata[$this->name]['mail_type'] = VALUE_Mail_Type;			// とりあえずきめうち
			$qdata[$this->name]['status_code'] = VALUE_Status_Waiting;	// とりあえずきめうち
			$qdata[$this->name]['mail_data'] = $data;
			$qdata[$this->name]['modified'] = null;
//$this->log($qdata);

			$rtn = $this->saveQueue($qdata,$priority);

		} catch(Exception $e){
			$_rc = false;
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		$this->setLang($my_lang);
		return $_rc;
	}

/**
 * ApprovalSend
 * @todo 	承認依頼　送信
 * @param	array $data:
 *   [User] => Array
 *       (
 *           [email] => 'xxxx@aaa.co.jp'
 *       )
 * @param 	int $logid : Eventlog ID
 * @param	string $type : 申請タイプ
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */

	function ApprovalSend($cid,$logid,$template = null,$priority = 0){
$this->log('承認依頼メール');
		// 言語のセーブ
$this->log("ApprovalSend start cid[".$cid."] evlog[".$logid."] template[".$template."]");
		$my_lang = $this->getLang();
		$_rc = true;
		try{
			/**
			 * タイプによりテンプレートを選択
			 */
			if($template == null) {
				return false;
			}
			$this->loadModel('Approval');
			// 取り出す項目を制限（role とかがたくさん出ないように）
			$contain = array(
					'Content' => array(
						'User' => array(
							'Group' => array(
								'fields' => array('id','name','jpn','eng')
							),
							'UserExtension' => array(
							)
						),
						'Status' => array(
							'User' => array(
								'fields' => array('id','name','division')
							)
						),
						'Uploadfile' => array(
						),

					),
					'AprvReqUser' => array(
						'Group' => array(
							'fields' => array('id','name','jpn','eng'),
						),
					));
			$data_ary = $this->Approval->find('first',array(
				'conditions' => array('content_id' => $cid),
				'recursive' => false,
				'contain' => $contain,
				)
			);

$this->log('=========== aprv read',LOG_DEBUG);
//$this->log($data_ary ,LOG_DEBUG);

			//---------------- ここから 宛先の言語
			$to_lang = $data_ary['AprvReqUser']['lang'];
			$this->setLang($to_lang);

			// 送信者の名前
			$this->loadModel('User');
			$from_name = $this->User->getFromNames($data_ary['Content']['User']['id'],$to_lang);
			// 受信者の名前
			$to_name = $this->User->getFromNames($data_ary['AprvReqUser']['id'],$to_lang);

			// シリアル番号
			$sno = $this->makeSystemSno($template, $data_ary);
			$sno .= $data_ary['Approval']['sno'];
			/**
			 * 本文テンプレート用パラメータ設定
			 */
			$title = __d('mail',$template);

			// ログインURI（loginid つき）
			$uri =  Router::url(array(
				'controller' => 'users',
				'plugin' => null,
				'action' => 'login',
				'r' => 'on',
				'i' => $data_ary['AprvReqUser']['email']
			 ),true);

$this->log('basename2['.$uri.']',LOG_DEBUG);
			$status = array();
			$this->loadModel('Status');
			foreach($data_ary['Content']['Status'] as $_k => $_v){

				$names = $this->Status->getToNamesFromUser($_v['id'],$to_lang);
				$str = implode($names,':');
				$str .= ' ( ' .$_v['email'] . ' ) ';
				$status[] = $str;
			}
			$data_ary['Content']['Status'] = $status;
//$this->log($data_ary);
			/**
			 *  テンプレート用パラメータ
			 */
			$viewVars = array(
				'from' => implode($from_name,':'),
				'to' => implode($to_name,':'),
				'data' => $data_ary,
				'title' => $title ,
				'uri' => $uri,
				'param' => array('controller' => 'users',
								'plugin' => null,
								'action' => 'login',
								'r' => 'on',
								'i' => $data_ary['AprvReqUser']['email'],
								'?' => array('lang' => $to_lang)),
			);
			/**
			 * CakeEmail 用パラメータ設定
			 */
			$from = sprintf('【%s】 <%s>',implode($from_name,':'),$data_ary['Content']['User']['email']);
			$data = array(
				'from' => array(VALUE_Mail_FromKey => '【'.$from_name['name'].'】'),
				'to'   => $data_ary['AprvReqUser']['email'],
				'replyTo' => $data_ary['Content']['User']['email'],
				'subject' => MAIL_SUBJECT_PREFIX . $title . '(' .$data_ary['Approval']['sno']. ')',
				'emailFormat' => VALUE_Mail_Type,
				'template' => array($template,'default'),	// template,layout
				'viewVars' => $viewVars,
				);
//$this->log($data,LOG_DEBUG);
			$this->recursive = -1;
			$qdata = $this->create();
			$qdata[$this->name]['sno'] = $sno;
			$qdata[$this->name]['priority'] = $priority;
			$qdata[$this->name]['template'] = $template;
			$qdata[$this->name]['mail_from'] = $data_ary['Content']['User']['email'];
			$qdata[$this->name]['mail_to'] = $data_ary['AprvReqUser']['email'];
			$qdata[$this->name]['lang'] = $to_lang;
			$qdata[$this->name]['eventlog_id'] = $logid;
			$qdata[$this->name]['status_id'] = 0;
			$qdata[$this->name]['mail_charset'] = VALUE_Mail_Charset;	// とりあえずきめうち
			$qdata[$this->name]['mail_type'] = VALUE_Mail_Type;					// とりあえずきめうち
			$qdata[$this->name]['status_code'] = VALUE_Status_Waiting;	// とりあえずきめうち
			$qdata[$this->name]['mail_data'] = $data;
			$qdata[$this->name]['modified'] = null;
			$qdata[$this->name]['etc'] = null;	// Errmails - ID

//$this->log($qdata,LOG_DEBUG);

			$_rc = $this->saveQueue($qdata,$priority);

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $_rc;
	}
/**
 * ApprovalResultSend
 * @todo 	承認、却下　送信　（仕掛中）
 * @param	array $data:
 *   [User] => Array
 *       (
 *           [email] => 'xxxx@aaa.co.jp'
 *       )
 * @param 	int $logid : Eventlog ID
 * @param	string $type : 申請タイプ
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */
	function ApprovalResultSend($cid,$logid,$template = null,$priority = 0){
$this->log('承認、却下 メール');
		// 言語のセーブ
$this->log("ApprovalResultSend start cid[".$cid."] evlog[".$logid."] template[".$template."]");
		$my_lang = $this->getLang();
		$_rc = true;
		try{
			/**
			 * タイプによりテンプレートを選択
			 */
			if($template == null) {
				return false;
			}
			$this->loadModel('Approval');
			// 取り出す項目を制限（role とかがたくさん出ないように）
			$contain = array(
					'Content' => array(
						'User' => array(
							'Group' => array(
								'fields' => array('id','name','jpn','eng')
							),
							'UserExtension' => array(
							)
						),
						'Status' => array(
							'User' => array(
								'fields' => array('id','name','division')
							)
						),
						'Uploadfile' => array(
						),

					),
					'AprvUser' => array(
						'Group' => array(
							'fields' => array('id','name','jpn','eng'),
						),
					),
					);
			$data_ary = $this->Approval->find('first',array(
				'conditions' => array('content_id' => $cid),
				'recursive' => 3,
				'contain' => $contain,
				)
			);

$this->log('=========== aprv read',LOG_DEBUG);

			//---------------- ここから 宛先の言語
			$to_lang = $data_ary['Content']['User']['lang'];
			$this->setLang($to_lang);


			// 送信者の名前
			$this->loadModel('User');
			$from_name = $this->User->getFromNames($data_ary['AprvUser']['id'],$to_lang);
			// 受信者の名前
			$to_name = $this->User->getFromNames($data_ary['Content']['User']['id'],$to_lang);

			// シリアル番号
			$sno = $this->makeSystemSno($template, $data_ary);
			$sno .= $data_ary['Approval']['sno'];
			/**
			 * 本文テンプレート用パラメータ設定
			 */
			$_theme = 'mail';
			$myTheme = Configure::read('Config.theme');
			if($template == 'aprv_x' && $myTheme != 'default'){
				$_theme = $myTheme;
			}
$this->log('表題変換['.$_theme.']'.LOG_DEBUG);
			$title = __d($_theme,$template);
$this->log('表題変換結果['.$title.']'.LOG_DEBUG);

			// ログインURI（loginid つき）
			$uri =  Router::url(array(
				'controller' => 'users',
				'plugin' => null,
				'action' => 'login',
				'i' => $data_ary['Content']['User']['email']
			 ),true);

$this->log('basename1['.$uri.']',LOG_DEBUG);
			/**
			 *  テンプレート用パラメータ
			 */
			$status = array();
			$this->loadModel('Status');
			foreach($data_ary['Content']['Status'] as $_k => $_v){

				$names = $this->Status->getToNamesFromUser($_v['id'],$to_lang);
//				$names = $this->Status->getToNames($_v['id']);
				$str = implode($names,':');
				$str .= ' ( ' .$_v['email'] . ' ) ';
				$status[] = $str;
			}
			$data_ary['Content']['Status'] = $status;

			$viewVars = array(
				'from' => implode($from_name,':'),
				'to' => implode($to_name,':'),
				'data' => $data_ary,
				'title' => $title ,
				'uri' => $uri,
				'lang' => $to_lang,
				'param' => array(
					'controller' => 'users',
					'plugin' => null,
					'action' => 'login',
					'i' => $data_ary['Content']['User']['email']
				),
			);
			/**
			 * CakeEmail 用パラメータ設定
			 */
			$from = sprintf('【%s】 <%s>',implode($from_name,':'),$data_ary['Content']['User']['email']);
			$data = array(
//				'from' => $from,
				'from' => array(VALUE_Mail_FromKey => '【'.$from_name['name'].'】'),
				'to'   => $data_ary['Content']['User']['email'],
				'replyTo' => $data_ary['AprvUser']['email'],
				'subject' => MAIL_SUBJECT_PREFIX . $title . '(' .$data_ary['Approval']['sno']. ')',
				'emailFormat' => VALUE_Mail_Type,
				'template' => array($template,'default'),	// template,layout
				'viewVars' => $viewVars,
				);
//$this->log($data,LOG_DEBUG);
			$this->recursive = -1;
			$qdata = $this->create();
			$qdata[$this->name]['sno'] = $sno;
			$qdata[$this->name]['priority'] = $priority;
			$qdata[$this->name]['template'] = $template;
			$qdata[$this->name]['mail_from'] = $data_ary['AprvUser']['email'];
			$qdata[$this->name]['mail_to'] = $data_ary['Content']['User']['email'];
			$qdata[$this->name]['lang'] = $to_lang;
			$qdata[$this->name]['eventlog_id'] = $logid;
			$qdata[$this->name]['status_id'] = 0;
			$qdata[$this->name]['mail_charset'] = VALUE_Mail_Charset;	// とりあえずきめうち
			$qdata[$this->name]['mail_type'] = VALUE_Mail_Type;					// とりあえずきめうち
			$qdata[$this->name]['status_code'] = VALUE_Status_Waiting;	// とりあえずきめうち
			$qdata[$this->name]['mail_data'] = $data;
			$qdata[$this->name]['modified'] = null;
			$qdata[$this->name]['etc'] = null;	// Errmails - ID

//$this->log($qdata,LOG_DEBUG);

			$_rc = $this->saveQueue($qdata,$priority);

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $_rc;
	}

/**
 * LockoutNotifySend
 * @todo 	ロック解除通知メール送信
 * @param	array $data:
 *   [User] => Array
 *       (
 *           [email] => 'xxxx@aaa.co.jp'
 *       )
 * @param 	int $logid : Eventlog ID
 * @param	string $type : 申請タイプ
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */

	function LockoutNotifySend($user,$logid,$template = null,$priority = 0){
$this->log('LockoutNotifySend start  logid['.$logid.'] tpl['.$template.'] pri['.$priority.']');
		// 言語のセーブ
$this->log("LockoutNotifySend start logid[".$logid."]");
		$my_lang = $this->getLang();
		$_rc = true;
		try{
			/**
			 * タイプによりテンプレートを選択
			 * 'lo_notify'   : ロック解除通知（パスワード変更なし）
			 * 'lo_notify_p' : ロック解除通知（パスワード変更あり）
			 */
			if($template == null) {
				return false;
			}
			$to_lang = $user['User']['lang'];
			$this->setLang($to_lang);

			// 送信者の名前（
			$from_name = __d('mail','%s admin' ,VALUE_Package);
			$from = sprintf('【%s】 <%s>',$from_name,VALUE_Mail_FromKey);

			// 受信者の名前
			$this->loadModel('User');
			$toary = $this->User->getFromNames($user['User']['id'],$to_lang);
			$to = implode(' : ',$toary);

			// シリアル番号
			$sno = $this->makeSystemSno($template, $user);

			/**
			 * 本文テンプレート用パラメータ設定
			 */
			$title = __d('mail',$template ,VALUE_Package);
			$viewVars = array(
				'from' => $from,
				'to' => $to,
				'data' => $user,
				'title' => $title ,
			);
//debug($veiwVars);
			/**
			 * CakeEmail 用パラメータ設定
			 */
			$data = array(
				'from' => array(VALUE_Mail_FromKey => '【'.$from_name.'】'),
				'to'   => $user['User']['email'],
				'replyTo' => VALUE_Mail_FromKey,
				'subject' => MAIL_SUBJECT_PREFIX . $title ,
				'emailFormat' => VALUE_Mail_Type,
				'template' => array($template,'default'),	// template,layout
				'viewVars' => $viewVars,
				);
//$this->log($data);
			$this->recursive = -1;
			$qdata = $this->create();
			$qdata[$this->name]['sno'] = $sno;
			$qdata[$this->name]['priority'] = $priority;
			$qdata[$this->name]['template'] = $template;
			$qdata[$this->name]['mail_from'] = VALUE_Mail_FromKey;
			$qdata[$this->name]['mail_to'] = $user['User']['email'];
			$qdata[$this->name]['lang'] = $to_lang;	// きめうち
			$qdata[$this->name]['eventlog_id'] = $logid;
			$qdata[$this->name]['status_id'] = 0;
			$qdata[$this->name]['mail_charset'] = VALUE_Mail_Charset;	// とりあえずきめうち
			$qdata[$this->name]['mail_type'] = VALUE_Mail_Type;					// とりあえずきめうち
			$qdata[$this->name]['status_code'] = VALUE_Status_Waiting;	// とりあえずきめうち
			$qdata[$this->name]['mail_data'] = $data;
			$qdata[$this->name]['modified'] = null;
//			$qdata[$this->name]['etc'] = $user['User']['id'];	// Errmails - ID

//$this->log($qdata);

			$_rc = $this->saveQueue($qdata,$priority);
		} catch(Exception $e){
			$_rc = false;
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		$this->setLang($my_lang);
		return $_rc;
	}

///////////////////////////////////////////////////
/**
 * MailTestSend
 * @todo 	仮パスワード通知
 * @param	array $mailserver: メールサーバ情報
 * @param 	int $logid : Eventlog ID
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */
	function MailTestSend($mailServer,$logid,$priority = 0){
		$my_lang = $this->getLang();
		$_rc = true;
		try{
			// 宛先の言語
			$to_lang = $my_lang;
			$this->setLang($to_lang);

			$this->loadModel('User');
			$user['User']['id'] = $this->User->getIDfromEmail($mailServer['user_name']);

			// シリアル番号
			$sno = $this->makeSystemSno('mail_test',$user);

			/**
			 * 本文テンプレート用パラメータ設定
			 */
			$theme = Configure::read('Config.theme');

			$title = __('Send test mail');
			$viewVars = array(
				'from' => $mailServer['user_name'],
				'to' => $mailServer['test_mail_to'],
			);

			/**
			 * CakeEmail 用パラメータ設定
			 */
			$data = array(
				'from' => (array($mailServer['user_name'] => '【'.$title.'】')),
//				'sender' => (array(VALUE_Mail_FromKey => '['.$from_name .']')),
				//				'from' => (array(VALUE_Mail_FromKey => '['.VALUE_Mail_FromVal.']')),
				'to'   => $mailServer['test_mail_to'],
				'replyTo' => $mailServer['user_name'],
				'subject' => MAIL_SUBJECT_PREFIX . $title ,
				'emailFormat' => VALUE_Mail_Type,
				'template' => array('mail_test','default'),	// template,layout
				'viewVars' => $viewVars,
				'returnPath' => $mailServer['user_name'],
				);

			$this->recursive = -1;
			$qdata = $this->create();
			$qdata[$this->name]['sno'] = $sno;
			$qdata[$this->name]['priority'] = $priority;
			$qdata[$this->name]['template'] = 'mail_test';
			$qdata[$this->name]['mail_from'] = $mailServer['user_name'];
			$qdata[$this->name]['mail_to'] = $mailServer['test_mail_to'];
			$qdata[$this->name]['lang'] = $my_lang;
			$qdata[$this->name]['eventlog_id'] = $logid;
			$qdata[$this->name]['status_id'] = 0;
			$qdata[$this->name]['mail_charset'] = VALUE_Mail_Charset;	// とりあえずきめうち
			$qdata[$this->name]['mail_type'] = VALUE_Mail_Type;			// とりあえずきめうち
			$qdata[$this->name]['status_code'] = VALUE_Status_Waiting;			// とりあえずきめうち
			$qdata[$this->name]['mail_data'] = $data;
			$qdata[$this->name]['modified'] = null;

			$rtn = $this->saveQueue($qdata,$priority,$mailServer);
		} catch(Exception $e){
			$rtn = false;
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		$this->setLang($my_lang);
		return $rtn;
	}



//////////////////////////////////////////////////
/**
 * cancel
 * @todo 	未送信だけど必要なくなったものをキャンセル（softdelete）
 * @param	array  $cond : 条件
 * @return  int				削除した件数
 */
	public function cancel($cond = array()){
		try{
			$num = 0;
			$this->recursive = -1;
			$list = $this->find('list',array(
									'fields' => array('id','sno'),
									'conditions' => $cond));
//$this->log($list);
			foreach($list as $id => $sno){
				$num++;
				$this->delete($id);
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $num;
	}

/**
 * ZipPwd
 * @todo 	zip パスわーど通知
 * @param	array $data:
 *   [User] => Array
 *       (
 *           [email] => 'xxxx@aaa.co.jp'
 *       )
 * @param 	int $logid : Eventlog ID
 * @param	string $type : 申請タイプ
 * @param 	int $priority : 優先度（通常=50 : いますぐ送信=0）
 * @return  bool
 */

	function ZipPwd($qdata,$priority = 50){
//$this->log($qdata);
		$my_lang = $this->getLang();
		$rtn = true;
		try{
			if(!CakePlugin::loaded('Encrypt')){
$this->log('Encrypt プラグインが必要です。',LOG_DEBUG);
				return false;
			}
			$to_lang = $qdata[$this->name]['lang'];
			$this->setLang($to_lang);

			$this->recursive = -1;
			$new = $this->create($qdata[$this->name]);
			if(Hash::check($new,$this->name.'.mail_data.cc')){
//$this->log('--------- del cc');
				$new = Hash::remove($new,$this->name.'.mail_data.cc');
			}
			if(Hash::check($new,$this->name.'.mail_data.bcc')){
//$this->log('--------- del bcc');
				$new = Hash::remove($new,$this->name.'.mail_data.bcc');
			}
//$this->log('--------- q_zippwd',LOG_DEBUG);
			$new[$this->name]['id'] = null;
			$new[$this->name]['template'] = 'zip_pwd';
			$new[$this->name]['mail_data']['template'][0] = 'zip_pwd';

			$new[$this->name]['created'] = null;
			$new[$this->name]['priority'] = $priority;
			$new[$this->name]['mail_data']['subject'] = MAIL_SUBJECT_PREFIX . __d('encrypt','Information of ZIP password');
//$this->log($new,LOG_DEBUG);
			$rtn = $this->saveQueue($new,$priority);
//	$this->log($rtn);
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
			$rtn = false;
		}
		// 言語を戻す
		$this->setLang($my_lang);
		return $rtn;
	}


/* Vendor につくった暗号化テスト zpwd で使用したい
 * type =='high' のときは　結果文字列が88文字ぐらいになる。
*/
	public function ango($text = null,$type = null){

		$enc1 = Crypt::encrypt($text,$type);
		debug($enc1);
		$dec1 = Crypt::decrypt($enc1);
		return $dec1;
	}


}
