<?php
/**
 *  for local test settings
 *	@name         aec-fts2 デモ
 */
/**
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       jp.co.asahi-eg.fts
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * パスの設定は、プラグインも使うため早めに
 */ 
/**
 * リニューアル用 
 * WEB_ROOT : このサーバの root (最後の/はいらない）
 * DATA_ROOT: アップロードするフォルダの root (最後の/はいらない）
 */
define('MY_DATA', '/data/vhost/aec-fts2/fts_new');		// このアプリが使用するデータの基本パス
define('VALUE_Domain', 'aec-fts2.jsy.ne.jp');			// shell だとドメインがとれないので

//--------------------------------------------
// サーバ内フォルダ （最後の /　は不要）
//--------------------------------------------

Configure::write('Upfile.dir', MY_DATA . DS . 'updata');	//!< @brief  upload dir

 /**
 * リニューアル用 (shared のときは、contract を別のものに差し替える）
 */
//CakePlugin::loadAll();	// あるのを全部入れるらしい
CakePlugin::load('BoostCake');		//!< @brief デザイン用
//CakePlugin::load('FastCSV');		//!< @brief CSV出力
CakePlugin::load('FileUpload');		//!< @brief upload(swfupload で使用)
//CakePlugin::load('Less');			//!< @brief css の代わりにless を直接使用するとき
CakePlugin::load('Search');			//!< @brief 検索プラグイン
CakePlugin::load('SearchPagination');	//!< @brief 検索ページネーションSearch プラグインと連携予定
CakePlugin::load('Transition');		//!< @brief ページ遷移を管理


 /**
 * リニューアル用 自作(shared のときは、contract を別のものに差し替える）
 */
CakePlugin::load('Templates',array('bootstrap'=>true ));		//!< @brief テンプレート*
CakePlugin::load('Inquiries',array('bootstrap'=>true ));		//!< @brief 問い合わせ*
CakePlugin::load('Histories',array('bootstrap'=>true ));		//!< @brief 更新履歴*
//CakePlugin::load('Syslogs',array('bootstrap'=>true ));			//!< @brief ログ出力*
CakePlugin::load('Reserves',array('bootstrap'=>true ));			//!< @brief 送信リザーブ管理*
CakePlugin::load('Errmails',array('bootstrap'=>true ));			//!< @brief 不達メール管理*

//CakePlugin::load('Contracts',array('bootstrap'=>true ));		//!< @brief 契約簡易版 (shared 以外)

 
Configure::write('debug', 0);				//!< @brief debug Level
if(Configure::read('debug') > 0){
	// デバッグモードのときだけ
	CakePlugin::load('DebugKit');		//!< @brief デバッグキット
}

define('LOGIN_PERMISSION',true);			//!< @brief [T] ログイン可否

// ↓ここ変更　2014.06.14
define('DEBUG_LogSQL', 0);					//!< @brief [0] 0:出さない/1:SQLクエリー/2:SQL実行詳細
define('DEBUG_View',false);					//!< @brief [F] for View debug 2014.04.21
define('DEBUG_MakingPermission',true);		//!< @brief [T] uri の経路チェックをスルー(デバッグ中は[T])
/**
 * SQL文をLOG_DEBUGに出力するかどうか　2010.04.07
 * 通常は false
 * （true にするとログファイルのサイズが大きくなるので注意）
 * define 名変更：2011.06.27
 */
define('VALUE_DefaultPWD','fts2pwd0');			//!< @brief メールが飛ばないときのデフォルトパスワード

/**
 * メール情報
 * Config/email.php とリンク
 *　email.php に複数の設定を書いておいて VALUE_MailConfig で切り替えてもよいし
 *　そこは一定にしておいて、VALUE_Mail_xxx の内容を切り替えてもよい。
 *
 */
define('VALUE_MailConfig','default');	//!< @brief メールサーバ設定用 

//define('VALUE_Mail_Transport','Debug');		//!< @brief Debugのときはメールを送らない 
define('VALUE_Mail_Transport','Smtp');			//!< @brief 本当にメールを飛ばすとき 
define('VALUE_Mail_FromKey','bird@jsy.ne.jp');	//!< @brief 送信者メールアドレス 
define('VALUE_Mail_FromVal','fts_DEMO');	//!< @brief 送信者名 
define('VALUE_Mail_Host','192.168.0.110');		//!< @brief メールサーバHost 
define('VALUE_Mail_Port', 25 );					//!< @brief メールサーバPort 
define('VALUE_Mail_Timeout',30);				//!< @brief タイムアウト 
define('VALUE_Mail_Username','bird');			//!< @brief ユーザ 
define('VALUE_Mail_Password','0bird');			//!< @brief パスワード 
define('VALUE_Mail_Client', null);				//!< @brief X-Mailer に書かれる 
define('VALUE_Mail_Log', true);					//!< @brief ログ出力有無 
define('VALUE_Mail_Charset','utf-8');			//!< @brief 本文 charset 
define('VALUE_Mail_HeaderCharset','utf-8');		//!< @brief ヘッダ charset 
define('VALUE_Mail_Type','text');				//!< @brief text / html 

/**
 * メールの優先度を規定　2014.09.30
 * 基準値は constdef で指定
*/
/*
define('VALUE_Mail_Priority_ContentSend',	VALUE_Mail_Priority_Normal);	//!< @brief 送信 
define('VALUE_Mail_Priority_ContentLPwd',	VALUE_Mail_Priority_Normal);	//!< @brief ログインパスワード 
define('VALUE_Mail_Priority_ContentZPwd',	VALUE_Mail_Priority_Normal);	//!< @brief ZIPパスワード 
define('VALUE_Mail_Priority_Approval',		VALUE_Mail_Priority_High);		//!< @brief 承認 
define('VALUE_Mail_Priority_ResetPwd',		VALUE_Mail_Priority_Urgent);	//!< @brief パスワード変更 
define('VALUE_Mail_Priority_Inquiry',		VALUE_Mail_Priority_Urgent);	//!< @brief 問い合わせ 
define('VALUE_Mail_Priority_Apply',			VALUE_Mail_Priority_Urgent);	//!< @brief 申請 
define('VALUE_Mail_Priority_Info',			VALUE_Mail_Priority_Urgent);	//!< @brief 管理者メール 
*/
// すぐに送信かどうかの閾値　↓より少ないときはすぐに送信する　
// 100　のときはすべてすぐに送信（レスポンス遅）
// 50　　通常はこのへんが妥当かと
// -2 　のときはすべてQueue に登録してバッチで送信（レスポンス速）
define('VALUE_Mail_Priority_Threshold',		100);		//!< @brief 閾値 



define('LOGO_DEFAULT','logo.png');					//!< @brief デフォルトロゴ 
define('LOGO_LINK','http://www.asahi-eg.co.jp/product/web-system/fts2/');		//!< @brief ロゴクリック時のリンク先 
define("PAGE_TITLE", "FTS new [DEMO]");	//!< @brief Title ＜必須＞

define('HUMAN_TEST',true);					//!< @brief 画像認証の有無 
define('HUMAN_TEST_CODE_LENGTH',1);			//!< @brief  文字数
define('HUMAN_TEST_IMAGE_TYPE',HUMAN_TEST_IMAGE_TYPE_PNG);	//!< @brief  画像タイプ JPG or PNG


//--------------------------------------------
// Securityoptions テーブルを使用する　2011.08.30 変更
// イキ：公開する /  コメントアウト：公開しない
// if(defined('OPTION_Security')){} で判定する。
//--------------------------------------------
define('OPTION_Security',true);

Configure::write('Password_Security',
     array(
       'is_debug' => true,			// true: デバッグモード / false: 本番
     								// デバッグモードはパスワード履歴に平文をのこす
     								// 本番モードは平文を残さない
       'pwd' => array(
           'is_enable' => true,   // true : 有効 / false ：無効
           'time_limit' => 90,    // 有効日数 0 : 無期限
           'history_limit' => 5  // 履歴保持 0 : なし
        ),
       'lockout' => array(
           'is_enable' => false,      // true : 有効 / false ：無効
           'retry_limit_session' => 5,  // session リトライ許容回数
           'retry_limit_id' => 5,       // ID リトライ許容回数 0 のときはロックアウトしない
           'lockout_date' => 0 ,     // ロックアウト期間（日数）0 : 無期限
           'uri' => 'lockout' ,      // ロックアウト時に移動するページ
           'is_pwd_continue' => false,  // ロックアウト解除時に既存パスワードをそのまま許可するかどうか
           'is_chgpwd_demand' => true,   // ロックアウト解除時にパスワード変更を強制するかどうか
			// 以下追加項目
           'is_lockout_session_limit' => true, // セッションリトライ回数がオーバーしたらロックアウト（2012.10)
		   'is_human_test_retry_count' => false // 画像認証エラーもカウントする（2012.10)
        ),
 		'rule' => array(
			'type' => 'low',		// low :(A-Za-z) (0-9)
									// normal:(A-Z) (a-z) (0-9)
			'chartype' => 'a',		// 'A' : alphanumeric
									// 'S' : alpha + symbol
//			'type' => 'normal',
//			'chartype' => 's',
			
		)
     )
    );

/*************************************************
 * お客様ごとに変えるもの
 ************************************************/
//--------------------------------------------
// デフォルトテーマ（とりあえず日本語）2010.02.23
//--------------------------------------------
	Configure::write('Config.theme', 'default');
//	Configure::write('Config.theme', 'new');
Configure::write('Config.language', 'jpn');	//!< @brief  デフォルト言語


//--------------------------------------------
// フラッシュアップロードファイル合計最大サイズ
// myconfig.js の file_size_limit は、１ファイルあたりの最大をみます。
// ここは数字でなければなりません。
// define('VALUE_UploadMax','300M'); は表示だけです
//--------------------------------------------

define('VALUE_file_size_limit' , 400*1024*1024);	// ファイル最大サイズ (総量制限にも使用)
define('VALUE_file_upload_limit' , 30);				// アップロードファイル最大数 0:無制限
define('VALUE_file_queue_limit' , 0);				// アップロード待ち最大数 0:無制限

//--------------------------------------------
// Copyright
//--------------------------------------------
define('MAIL_SEL_PREFIX','FTSnewDEMO');				// 	メールのシリアル番号識別子
define('MAIL_SUBJECT_PREFIX','[FTS new DEMO]');		// メール表題の先頭につける決まり文句

define('VALUE_Copyright','Copyright (c) 2014- ASAHI ENGINEERING CO .,LTD. All Rights Reserved.');
define('VALUE_Package','FTS2');						//!< @brief パッケージ名  ＜必須＞
Configure::write('VALUE_CompanyName',
		array(	'jpn' => '旭エンジニアリング株式会社',
				'eng' => 'ASAHI ENGINEERING CO .,LTD.'));

/**
define('MAIL_SEL_PREFIX','FTS2_Stanley');		// 	本番
define('MAIL_SUBJECT_PREFIX','[FTS2]');		// メール表題の先頭につける決まり文句

define('VALUE_Copyright','Copyright (c) 2012- STANLEY ELECTRIC CO., LTD. All Rights Reserved.');
define('VALUE_Package','FTS2');						//!< @brief パッケージ名  ＜必須＞
Configure::write('VALUE_CompanyName',
		array(	'jpn' => 'スタンレー電気株式会社',
				'eng' => 'STANLEY ELECTRIC CO., LTD.'));
**/

/**
 * zip パスワードをDB登録するときの対応
 **/
//define('VALUE_ZPWD_Security','high');			//!< @brief zip パスワードのセキュリティ：高 
//define('VALUE_ZPWD_Security','medium');		//!< @brief zip パスワードのセキュリティ：中 
//define('VALUE_ZPWD_Security','low');			//!< @brief zip パスワードのセキュリティ：低 
define('VALUE_ZPWD_Security','none');			//!< @brief zip パスワードのセキュリティ：なし 

/**
 * Database 設定
 * Config/database.php とリンク
 */
//--------------------------------------------
// Database (default)
//--------------------------------------------

//define('VALUE_default_datasource' 	, 'MysqlLog');
define('VALUE_default_datasource' 	, 'Database/Mysql');
define('VALUE_default_persistent' 	, false);
define('VALUE_default_host' 		, 'localhost');
define('VALUE_default_port' 		, 3306);
define('VALUE_default_login' 		, 'web');
define('VALUE_default_password' 	, 'aec3750');
define('VALUE_default_database' 	, 'fts_demo');
define('VALUE_default_prefix' 		, 'fts2_');
define('VALUE_default_encoding' 	, 'utf8');

//--------------------------------------------
// Database (test)
//--------------------------------------------
//define('VALUE_test_datasource' 		, 'MysqlLog');
define('VALUE_test_datasource' 		, 'Database/Mysql');
define('VALUE_test_persistent' 		, false);
define('VALUE_test_host' 			, 'localhost');
define('VALUE_test_port' 			, 3306);
define('VALUE_test_login' 			, 'web');
define('VALUE_test_password' 		, 'aec3750');
define('VALUE_test_database' 		, 'fts_new_test');
define('VALUE_test_prefix' 			, 'fts2_');
define('VALUE_test_encoding' 		, 'utf8');

/**
 * time limt default 設定
 */
define('VALUE_content_limit_default' 	, 3);	//!< @brief デフォルトタイムリミット（日）

define('VALUE_tmppwd_limit_default' 	, 14);	//!< @brief 仮パスワードタイムリミット（日）

/**
 * 継続申請ガイド
 */
define('VALUE_expapply_guide' 	, 'app/webroot/docs/shared/jpn/guide/index.php');
define('VALUE_expapply_format' 	, 'app/webroot/docs/common/jpn/fts2_man.pdf');
/**
 * プライバシーポリシー
 */
define('PRIVACY_POLICY','http://www.asahi-eg.co.jp/policy/');
//define('PRIVACY_POLICY_jpn','http://www.stanley.co.jp/privacypolicy.html');
//define('PRIVACY_POLICY_eng','http://www.stanley.co.jp/e/privacypolicy.html');

//--------------------------------------------
// お問合せ宛先
// TO は複数宛先のときは,で羅列する
//--------------------------------------------
define('INQUIRY_TO','dog@jsy.ne.jp');
//define('OPTION_Inquiry_GetEmailLink',false);			// お問い合わせでメールアドレスからの名寄せ　true:あり / false:なし

//--------------------------------------------
// 期限切れのときの延長申請送信先アドレス（ユーザのみ）	:2011.08.30:変更
// TO は複数宛先のときは,で羅列する。先頭がTO、それ以降はCC
//--------------------------------------------
define('OPTION_Expdate_Relese_Apply_Addrs',INQUIRY_TO);	// お問い合わせと同じ

//--------------------------------------------
//ロックアウト解除申請送信先アドレス	:2011.08.30:変更
// TO は複数宛先のときは,で羅列する。先頭がTO、それ以降はCC
//--------------------------------------------
define('OPTION_Lockout_Relese_Apply_Addrs',INQUIRY_TO);	// お問い合わせと同じ



