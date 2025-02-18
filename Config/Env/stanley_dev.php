<?php
Configure::write('debug', 0);
Configure::write('Config.theme', 'Stanley');
define('VALUE_System_Default_Lang','jpn');		//!< @brief システムデフォルト言語 

define('VALUE_default_database' 	, 'stanley_dev');
define('VALUE_default_prefix' 		, 'fts_');
define('ADDRESSBOOKS_PERSONAL_FLG', true);    //個人版使用フラグ
define('ADDRESSBOOKS_SHARED_FLG', false);    //共通版使用フラグ stanley はこれで切り分け
define('MAIL_URI_ENCODE', false);  //URI エンコード指定　true: エンコードあり / false:　エンコードなし

/**
 *  テストで、全てのメールを一つのアドレスで受けたいときは、
 *  VALUE_Mail_Transport　を 'Smtp' にして
 *  VALUE_Mail_TEST_TO　にアドレスを指定する。
 *  本番用にするときは、VALUE_Mail_TEST_TO　をコメントアウトする。
 *  2016.06.06
 */
define('VALUE_Mail_TEST_TO','ohishi@asahi-eg.co.jp');		//!< @brief 全てのメールを１アドレスで受信したいときに設定
//define('VALUE_Mail_Transport','Debug');		//!< @brief Debugのときはメールを送らない
define('VALUE_Mail_Transport','Smtp');			//!< @brief 本当にメールを飛ばすとき


/**
 * パスの設定は、プラグインも使うため早めに区切り文字はDSを使用しないと
 * Windows　サーバのときに　Vscan plugin が動かない。2015.03.24 
 */ 
define('MY_DATA', '/data/stanley/data/stanley_dev');		// このアプリが使用するデータの基本パス
define('VALUE_Domain', 'dev-stanley-fts.asahi-eg.co.jp');		// shell だとドメインがとれないので
//--------------------------------------------
// サーバ内フォルダ （最後の /　は不要）
//--------------------------------------------
Configure::write('Upfile.dir', MY_DATA . DS . 'updata');	//!< @brief  upload dir

define('SEND_PERMISSION',true);				//!< @brief [T] 送信可否（未）

//--------------------------------------------
// ファイル容量チェックで使用　2017.06.01
// 個別の設定ファイルで動的に変えられるよう Configure に記載
//--------------------------------------------
// 残りディスク容量　
// 書き方は３通り
// １）　数字のみ（単位はバイト）　free がこれ以下だったらアップロード禁止
// ２） 使用率（全体容量に対する率を指定、最後に半角'%' をつける
//      例： 10% --- 残り 10% を切ったらアップロード禁止
// ３） VALUE_SI_PREFIX または VALUE_SI_PREFIX_I で指定する単位を使用する
//    単位は大文字　(i だけは小文字)
//      例：　10GB --- 残り　10GB　を切ったらアップロード禁止
Configure::write('VALUE_REMAIN_DISK_SPACE', '2GiB');

// ↓ここ変更　2014.06.14
// SQL　クエリーをログに出すかどうか bootstrap.php Configure::write('debug',2) 以上でないと出ない
define('DEBUG_LogSQL',1);					//!< @brief [0] 0:出さない/1:SQLクエリー/2:SQL実行詳細

// ナビで普段出さないものをグレイアウトして表示するかどうか
define('DEBUG_View',false);					//!< @brief [F] for View debug 2014.04.21

// role で指定されていない場合に強制ログアウトするかどうか
define('DEBUG_MakingPermission',false);		//!< @brief [T] true: uri の経路チェックでダメならログイン画面に戻る
											//		（false はデバッグモード）
/**
 * SQL文をLOG_DEBUGに出力するかどうか　2010.04.07
 * 通常は false
 * （true にするとログファイルのサイズが大きくなるので注意）
 * define 名変更：2011.06.27
 */
define('VALUE_DefaultPWD','fts5pwd0');			//!< @brief メールが飛ばないときのデフォルトパスワード

/**
 * メール情報
 * Config/email.php とリンク
 *　email.php に複数の設定を書いておいて VALUE_MailConfig で切り替えてもよいし
 *　そこは一定にしておいて、VALUE_Mail_xxx の内容を切り替えてもよい。
 *
 */

define('VALUE_Mail_FromKey','super@jsy.ne.jp');	//!< @brief 送信者メールアドレス 
define('VALUE_Mail_FromVal','super');	//!< @brief 送信者名 
// このサーバについては、port 25 でも 587 でも　username,password は不要（あるとエラーになる）
// Config/email.php　の当該箇所をコメントアウトすること。
define('VALUE_Mail_Host','mail-mirai.jsy.co.jp');		//!< @brief メールサーバHost 新サーバ
define('VALUE_Mail_Port', 587);					//!< @brief メールサーバPort 25/587
define('VALUE_Mail_Timeout',30);				//!< @brief タイムアウト 
define('VALUE_Mail_Username','shared-fts@jsy.co.jp');			//!< @brief ユーザ （いらないかも）
define('VALUE_Mail_Password','0shared-fts');			//!< @brief パスワード （いらないかも）
define('VALUE_Mail_Client', null);				//!< @brief X-Mailer に書かれる 
define('VALUE_Mail_Log', true);					//!< @brief ログ出力有無 
define('VALUE_Mail_Charset','utf-8');			//!< @brief 本文 charset 
define('VALUE_Mail_HeaderCharset','utf-8');		//!< @brief ヘッダ charset 
define('VALUE_Mail_Type','text');				//!< @brief text / html 


/**
 * リニューアル用 
 * WEB_ROOT : このサーバの root (最後の/はいらない）
 * DATA_ROOT: アップロードするフォルダの root (最後の/はいらない）
 */
//debug('webroot['.WEB_ROOT.']');


define('LOGO_DEFAULT','logo.png');					//!< @brief デフォルトロゴ 
define('LOGO_LINK','http://www.asahi-eg.co.jp/product/web-system/fts2/');		//!< @brief ロゴクリック時のリンク先 
//define('LOGO_LINK','http://fontawesome.io/icons/');		//!< @brief ロゴクリック時のリンク先 
define("PAGE_TITLE", "FTS5_stanley");	//!< @brief Title ＜必須＞

define('HUMAN_TEST',true);					//!< @brief 画像認証の有無 
define('HUMAN_TEST_CODE_LENGTH',1);			//!< @brief  文字数
define('HUMAN_TEST_IMAGE_TYPE',HUMAN_TEST_IMAGE_TYPE_PNG);	//!< @brief  画像タイプ JPG or PNG


//--------------------------------------------
// Securityoptions テーブルを使用する　2011.08.30 変更
// イキ：公開する /  コメントアウト：公開しない
// if(defined('OPTION_Security')){} で判定する。
//--------------------------------------------

Configure::write('Password_Security',
     array(
       'is_debug' => false,			// true: デバッグモード / false: 本番
     								// デバッグモードはパスワード履歴に平文をのこす
     								// 本番モードは平文を残さない
									// デバッグモードのときは、ロックアウト
       'pwd' => array(
           'is_enable' => true,   // true : 有効 / false ：無効
           'time_limit' => 90,    // 有効日数 0 : 無期限
           'history_limit' => 5  // 履歴保持 0 : なし
        ),
       'lockout' => array(
           'is_enable' => true,      // true : 有効 / false ：無効
           'retry_limit_session' => 5,  // session リトライ許容回数
           'retry_limit_id' => 5,       // ID リトライ許容回数 0 のときはロックアウトしない

           'lockout_date' => '0' , // ロックアウト期間（日数）0 : 無期限
//           'lockout_date' => '15 minutes' , // ロックアウト期間（日数）0 : 無期限
											// またはDateInterval::createFromDateString　の書式
											// http://php.net/manual/ja/datetime.formats.relative.php
											// 凡例：解除までの期間（単複どちらでもいいみたい）
											// '60 seconds' = 60秒後
											// '15 minutes' = 15分後
											// '1 hour' = 1時間後
											// '1 day' = 1日後
											// '1 week' = 1週間後
											// 
 //          'uri' => 'lockout' ,      // ロックアウト時に移動するページ（廃止）どこにも使っていない
           'is_pwd_continue' => false,  // ロックアウト解除時に既存パスワードをそのまま許可するかどうか
           'is_chgpwd_demand' => true,   // ロックアウト解除時にパスワード変更を強制するかどうか
			// 以下追加項目
           'is_lockout_session_limit' => true, // セッションリトライ回数がオーバーしたらユーザをロックアウト（2012.10)
		   'is_human_test_retry_count' => false, // 画像認証エラーもカウントする（2012.10)
		   'is_fail_count_keep_db' => true, // 失敗カウントをDBに保持（2016.11)
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
Configure::write('Config.language', VALUE_System_Default_Lang);	//!< @brief  デフォルト言語


//--------------------------------------------
// フラッシュアップロードファイル合計最大サイズ
// myconfig.js の file_size_limit は、１ファイルあたりの最大をみます。
// ここは数字でなければなりません。
// define('VALUE_UploadMax','300M'); は表示だけです
//--------------------------------------------

define('VALUE_file_size_limit' , 400*1024*1024);	// ファイル最大サイズ (総量制限にも使用)

//--------------------------------------------
// Copyright
//--------------------------------------------

define('MAIL_SEL_PREFIX','FTS_StanleyDEV');		// 	本番
define('MAIL_SUBJECT_PREFIX','[FTS_StanleyDEV]');		// メール表題の先頭につける決まり文句

define('VALUE_Copyright','Copyright (c) 2012- STANLEY ELECTRIC CO., LTD. All Rights Reserved.');
define('VALUE_Package','StanleyDEV');		// 必須
Configure::write('VALUE_CompanyName',
		array(	'jpn' => 'スタンレー電気株式会社',
				'eng' => 'STANLEY ELECTRIC CO., LTD.'));

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
define('VALUE_default_encoding' 	, 'utf8');

/**
 * time limt default 設定
 */
define('VALUE_content_limit_default' 	, 3);	//!< @brief デフォルトタイムリミット（日）
define('VALUE_content_aprv_limit_default' 	, 3);	//!< @brief 承認デフォルトタイムリミット（日） add 2016

define('VALUE_tmppwd_limit_default' 	, 14);	//!< @brief 仮パスワードタイムリミット（日）

/**
 * 継続申請ガイド
 * pdf のときは webroot/files/ に書くこと
 */
define('VALUE_expapply_guide' 	, 'app/webroot/docs/shared/jpn/guide/index.php');
define('VALUE_expapply_format' 	, 'app/webroot/docs/common/jpn/fts2_man.pdf');

// /files/言語/xxxx.pdf になるので、pdf ファイル名のみセットする（ファイル名は同じにする）
// pdf がないときは↓をコメントアウトする。
//define('VALUE_expapply_format' 	, 'fts2_man.pdf');
//--------------------------------------------
// お問合せ宛先
// TO は複数宛先のときは,で羅列する
//--------------------------------------------
define('INQUIRY_TO','bird@jsy.ne.jp');

//--------------------------------------------
// 期限切れのときの延長申請送信先アドレス（ユーザのみ）	:2011.08.30:変更
// TO は複数宛先のときは,で羅列する。先頭がTO、それ以降はCC
//--------------------------------------------
define('OPTION_Expdate_Relese_Apply_Addrs',INQUIRY_TO);	// お問い合わせと同じ

//--------------------------------------------
//　ロックアウト解除申請送信先アドレス	:2011.08.30:変更
// TO は複数宛先のときは,で羅列する。先頭がTO、それ以降はCC
//--------------------------------------------
define('OPTION_Lockout_Relese_Apply_Addrs',INQUIRY_TO);	// お問い合わせと同じ

/*
 * ishigetani追加分
 * 2015/1/8
 */
// 自動ログアウトの有効化
// define('OPTION_Auto_Logout', true);
// 自動ログアウトまでの時間(分)
define('VALUE_Auto_Logout_Interval', 15);

 
/**
 * プライバシーポリシー
 * 複数ドメイン併記(Stanleyプラグインに持っていこうとしたが
 * 読み込む順番などの関係でうまくいかないのでここで記載
 */
define('PRIVACY_POLICY_jpn','http://www.stanley.co.jp/privacypolicy.html');
define('PRIVACY_POLICY_eng','http://www.stanley.co.jp/e/privacypolicy.html');
define('PRIVACY_POLICY',PRIVACY_POLICY_jpn);	// 問合せ用（必須）

/*********************************************
 *********************************************
 * 特殊な設定 （オプションによってはなくてよい）
 *********************************************
 *********************************************/
//--------------------------------------------
// for PSCAN 2012.08.06　→　stanley プラグイン内に移した
//--------------------------------------------
//define('VALUE_DomainForEmployee','http://stanley-fts01');	// 社内用
//define('VALUE_DomainForVisitor','https://stanley-fts01.cloud.niandc.ne.jp');		// 社外用

// テスト用に今のドメイン↓
//define('VALUE_DomainForEmployee','http://dev-stanley-fts');	// 社内用
define('VALUE_DomainForEmployee','http://dev-stanley-fts.asahi-eg.co.jp');		// 社内用
define('VALUE_DomainForVisitor','https://dev-stanley-fts.asahi-eg.co.jp');		// 社外用
//--------------------------------------------
// ファイルシステムマウント 
//--------------------------------------------
/*本番
	define('WIN_SERVER_IP','10.115.254.35');
	define('WIN_SERVER_USER','administrator');
	define('WIN_SERVER_PASS','TFGapi1226@st');
    define('WIN_SERVER_DEMO_PROP','TFGConv.properties');    // TFG サーバの 設定ファイル

	define('VALUE_TFG_MountBase', '/mnt/tfg/'); // FTS2 server からみたマウントベース
	define('VALUE_TFG_WinBase', "c:/data/"); // TFG server マウントベース
*/
/*tfg はデモ版が、pscan はスタブが動く*/

define('WIN_SERVER_IP','192.168.1.133');    // TFG サーバのIPアドレス
define('WIN_SERVER_USER','administrator');    // TFG サーバの USER
define('WIN_SERVER_PASS','me10@aecnet');    // TFG サーバの PASS

define('WIN_SERVER_DEMO_PROP','TFGConvDEMO.properties');    // TFG サーバの 設定ファイル

define('VALUE_TFG_MountBase', '/mnt/cake2/'); // FTS2 server からみたマウントベース
define('VALUE_TFG_WinBase', 'c:/cake2/'); // TFG server マウントベース
// 監視ジョブ名 conv11 から　conv16 いずれか（他のシステムとかぶらないように）
// それぞれ固有の Env/xxxx.php にて設定する。

define('VALUE_PSCAN_JOB','conv16');    


