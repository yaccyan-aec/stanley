<?php
// 送信履歴ファイル情報表示（デバッグモード）
define('DEBUG_ContentIndex',false);			//!< @brief [F] true / false : ID 表示(true はデバッグモード）
/**
 *  新規ファイル送信で使用する(仮)　2016/4/19　
 */
Configure::write('VALUE_Limit_Selection',array(1=>1,2=>2,3=>3,4=>4,5=>5));
// ↓は単位までコンボの中に入れてしまう実装（week,month,year なども可能）
Configure::write('Time_limit_selection', array(
    // 1 => array('day', 1),
	3 => array('day', 3),
	5 => array('day', 5),
	10 => array('day', 10),
	
	));

//define('DEMO',true);	// セキュリティ管理の一括処理ボタンを不活性化するために使用中←削除2017


//入力値の最大文字数（デフォルト値＝100） 入力画面で制御 リテラルでくくらないとダメ
define('MAX_STRLEN','100');

/**
 *  ゲスト系ユーザのデフォルトアカウント有効期限
 *  lastlogin からの有効期限　2016/10/18
 *  設定条件：role による。
 *  users/oneTimePwd && statuses/index/outofdate
 *  を満たすもの
 */
define('OPTION_Default_AccountLife','3 month');

/**************************************************
 * ログインヘッダ
 **************************************************/
define('SHOW_Info',false);	// 'FTS とは'のリンクをだすかどうか
define('SHOW_Faq',true);	// 'FAQ' のリンクをだすかどうか
define('SHOW_New',false);	// '新着情報' のリンクをだすかどうか
/**************************************************
 * 2013.08.20 作成
 * 定数はまとめてここで定義する
 **************************************************/
//-----------------------------------------------------
//  ユーザの有効期限を残り日数を表示 2011.09.01 リリース
// role で制御に変更　2017
// navi / remaindays
//-----------------------------------------------------
//define('OPTION_Countdown_remainder_days',true);	// true / false(コメントアウト) :
//if(defined('OPTION_Countdown_remainder_days') && OPTION_Countdown_remainder_days) {	// 定義されていて true のとき
	// 残り日数表示のタイミング
	// 0（またはコメント）：常に表示
	// 3：３日前から
	// '2 week'：2週間前から
	// '3 month'：3ヶ月前から
	// など
	define('VALUE_Timing_of_Display_Beginning','1 month');
//}

	/**************************************************
	 *  2016.05.24 作成
	 *  送信画面の「確認したいときは・・・のチェックのデフォルト値」
	 *  true 　: チェックあり
	 *  false : チェックなし
	 *************************************************/
 	define('VALUE_CONTENT_ADD_Has_confirm_default', true);		// true: チェックあり　/ false: チェックなし


	define('MY_APP', basename(ROOT));					// このアプリの基本パス
	define('WEB_ROOT',dirname(ROOT));

	//--------------------------------------------
	// メールを特定するためのシリアル番号
	// セパレータの'_'は自動で追加
	//--------------------------------------------
	define('VALUE_MailConfig','default');	//!< @brief メールサーバ設定用

//	define ('MAIL_SEL_PREFIX_ADMIN','A');				//	システムからのおしらせ
	define ('MAIL_SEL_PREFIX_SEND','S');				// 	送信お知らせ
	define ('MAIL_SEL_PREFIX_PASSWORD','P');			//	password 送信
	define ('MAIL_SEL_PREFIX_SEND_AND_PASSWORD','SP');	//	お知らせ＋password 送信
//	define ('MAIL_SEL_SUFFIX_FORMAT',"[Y/m/d H:i:s]");		//	送信日付フォーマット
	define ('MAIL_SEL_SUFFIX_FORMAT',"[r]");			//	送信日付フォーマット
	define ('MAIL_SEL_PREFIX_ZPWD','Z');				//	zip password 送信 2012.07.02


	// Approval.aprv_stat の内容 -----------------------------------------
	define('VALUE_AprvStat_None',0);			// 未
	define('VALUE_AprvStat_OK',1);				// 承認
	define('VALUE_AprvStat_NG',-1);				// 却下

	// Status.status_code の内容 -----------------------------------------
	define('VALUE_StatusStatusCode_On',0);				// 受信トレイ表示
	define('VALUE_StatusStatusCode_Off',-1);			// 受信トレイ非表示

	// AVG スキャン結果コード　→　プラグイン Vscan に移動

	// user.lockout_stat の内容↓　2010.11.8 新設
	// 期限延長申請にも適用　2014.09
	define('VALUE_Flg_None',0);			// ロックアウトなし
	define('VALUE_Flg_Lock',1);			// ロックアウト中
	define('VALUE_Flg_Apply',2);		// ロックアウト解除申請中
	define('VALUE_Flg_Manual',3);		// 管理者による手動ロックアウト　add 2016.11


// TFG・PSCAN・ZIP で使用 2017.1 現在未使用（0 のみ)
	//--------------------------------------------
	// Uploadfiles dl_mod ステータス
	//--------------------------------------------
	define('VALUE_dl_mod_NG', 2);      // dl_mod ステータス : ダウンロード不可
	define('VALUE_dl_mod_View', 1);    // dl_mod ステータス : 閲覧のみ（pscan）
	define('VALUE_dl_mod_OK', 0);      // dl_mod ステータス : ダウンロード許可
	define('VALUE_dl_mod_Hide', -1);   // dl_mod ステータス : 非表示


/*************************************************
 * 以下のものは変更できるが基本的に
 * どのお客様でも同じ仕様のためこちらで定義
 ************************************************/
//--------------------------------------------
// クッキーの有効期限　2010.08.30
// デフォルトは１時間
// イキ：公開する /  コメントアウト：公開しない
// if(defined('COOKIE_EXPIRE')){} で判定する。
// COOKIE_EXPIRE とは関係なく使用する
//--------------------------------------------
	define('COOKIE_EXPIRE','6 hour');			// 6時間
//	define('COOKIE_EXPIRE','1 week');			// 1週間
//	define('COOKIE_EXPIRE', '1 month');		// 1ヶ月（30日）
//	define('COOKIE_EXPIRE','6 month');		// 半年（180日）

//--------------------------------------------
// １ページあたりの最大行数デフォルト
//--------------------------------------------
	define('PAGE_LIMIT_DEFAULT' , 100);
//--------------------------------------------
// 返信タイトル
//--------------------------------------------
	define('REPLY_HEAD','Re:');
//--------------------------------------------
// 任意パスワード最小文字数
// リテラルにしないとバリデーションエラーメッセージがうまく出ない
//--------------------------------------------
	define('MANUAL_PWD_MIN' , '8');
//--------------------------------------------
// 任意パスワード最長文字数　2010.11.8
// リテラルにしないとバリデーションエラーメッセージがうまく出ない
//--------------------------------------------
	define('MANUAL_PWD_MAX' , '32');
//--------------------------------------------
// ランダムパスワード文字数
//--------------------------------------------
	define('AUTOPWD_DEFAULT_LEN' , 16);		// 	ログインパスワード自動生成文字数
	define('AUTOPWD_TMP_DEFAULT_LEN' , 8);	//	仮パスワード自動生成文字数
//--------------------------------------------
// 画像認証
// イキ：ログイン時に画像認証あり
// コメントアウト：ログイン時に画像認証なし
// if(defined('HUMAN_TEST')){} で判定する。
//--------------------------------------------
/* define('HUMAN_TEST',true);		bootstrap.php で指定 */
/* define('HUMAN_TEST_CODE_LENGTH',1);		-- 文字数 bootstrap.php で指定 */
/* define('HUMAN_TEST_IMAGE_TYPE',HUMAN_TEST_IMAGE_TYPE_PNG);	-- 画像タイプ bootstrap.php で指定 */

define('HUMAN_TEST_IMAGE_HEIGHT',50);		// 画像高さ
define('HUMAN_TEST_IMAGE_WIDTH',200);		// 画像幅
define('HUMAN_TEST_IMAGE_TYPE_JPG',1);		// 画像タイプ　JPG
define('HUMAN_TEST_IMAGE_TYPE_PNG',2);		// 画像タイプ　PNG
define('HUMAN_TEST_FONT_SIZE',15);			// 文字サイズ

// 2011.03.08:start move from bootstrap.php
//--------------------------------------------
// アップロードファイル最大サイズ（未使用）
//--------------------------------------------
//	define('UP_MAX' , 20000000);		// 2M -- とりあえず
//	define('UP_MAX' , 200000000);		// 20M
//	define('UP_MAX' , 2000000000);		// 200M
//	define('UP_MAX' , 20000000000);		// 2G
//	2016.11.24 CSVインポートに使用
	define('UP_MAX' , 2000000000);		// 2G
//	define('UP_MAX' , 40000000000);		// 4G
//	define('UP_MAX' , 80000000000);		// 8G

//--------------------------------------------
// Addressbook インポート
// ファイル読み込み時のレコードサイズ
//--------------------------------------------
define('CSV_REC_SIZE', 1500 );

//--------------------------------------------
// セッション寿命 (必須）
// session.cache_expire を指定する　2010.12.16
//--------------------------------------------
//define('SESSION_CACHE_EXPIRE',180);		// 180分(default)
define('SESSION_CACHE_EXPIRE',360);		// 分
//--------------------------------------------
// php のタイムリミットを指定する　2011.05.09 (必須)
//--------------------------------------------
//define('PHP_TIME_LIMIT',0);				// 無期限
//define('PHP_TIME_LIMIT',60);			// 60分(default)
define('PHP_TIME_LIMIT',300);			// 分

//--------------------------------------------
// サーバ内ランダムファイル名に使える文字
//--------------------------------------------
Configure::write('Upfile.seed', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');

define('VALUE_Enctype_Dec','dec');		// ファイル暗号タイプ　＝　なし（デフォルト）
define('VALUE_Enctype_Enc','enc');		// ファイル暗号タイプ　＝　暗号
define('VALUE_Enctype_ERR','---');		// ファイル暗号タイプ　＝　エラー

/*************************************************
 * おぼえがき（不要となった定数等）
 ************************************************/
//--------------------------------------------
// debug モード　2011.06.16
// true：開発版 /  コメントアウト：公開
//--------------------------------------------
define('DEBUG_DisplayId',false);			//!< @brief [F] true / false : ID 表示(true はデバッグモード）
define('DEBUG_DisplayFlashMsg',false);		//!< @brief [F] true / false : footer にフラッシュメッセージを表示
define('DEBUG_DisplayDB',false);			//!< @brief [F] true / false(コメントアウト) : 使用DB名表示

	//--------------------------------------------
	// Content,Queue　ステータス モード
	//	true = debug：変換前のファイルを残す
	//	false = 本番：変換前のファイルは次回のclean で消える
	//--------------------------------------------
	define('VALUE_Status_None', 0);     	// ステータス : 初期状態
	define('VALUE_Status_Waiting', 1);      // ステータス : 待ち
	define('VALUE_Status_Doing', 2);   		// ステータス : 処理中
	define('VALUE_Status_Done', 9);      	// ステータス : 終了
	define('VALUE_Status_Cancel', 8);      	// ステータス : キャンセル
	define('VALUE_Status_Error', -1);      	// ステータス : エラー

	//--------------------------------------------
	// Content　ステータス 追加
	//--------------------------------------------
	define('VALUE_Status_Conv_Waiting', 11);   		// ステータス : 変換待ち
	define('VALUE_Status_Conv_Doing', 12);   		// ステータス : 変換中
	define('VALUE_Status_Conv_Error', -2);   		// ステータス : 変換エラー
	define('VALUE_Status_Aprv_Waiting', 21);   		// ステータス : 承認待ち
	define('VALUE_Status_Aprv_Rjct', 99);   		// ステータス : 却下
	define('VALUE_Status_Aprv_RjctAuto', 98);   	// ステータス : 却下（shell）


//--------------------------------------------
// 言語選択 　2014.08.28
//--------------------------------------------
// 選択肢が増えたらここも増やす

Configure::write('VALUE_Language_Selection',
		array("auto" => " --- ", "jpn" => "jpn","eng" => "eng"));

//--------------------------------------------
// 契約管理 　2014.09.15
//--------------------------------------------
//
define('VALUE_Contracts_Type_Trial','Y');		// お試し
define('VALUE_Contracts_Type_Regular','N');		// 正規

define('VALUE_Contracts_Logo_Size','5KB');	// ロゴファイルサイズ最大値

// model のバリデーションエラーで使用する数値はリテラルでくくらないとダメ
define('VALUE_Contracts_Size_Usernum_Min','1');			// ユーザ数（人） min
define('VALUE_Contracts_Size_Usernum_Max','10000');		// ユーザ数（人） max
define('VALUE_Contracts_Size_Usernum_Default','1000');		// ユーザ数（人） 登録時デフォルト
define('VALUE_Contracts_Size_Disk_Min','1');				// 容量（GB） min
define('VALUE_Contracts_Size_Disk_Max','300');			// 容量（GB） max
define('VALUE_Contracts_Size_TimeLimit_Min','3');			// 有効期限（日）min
define('VALUE_Contracts_Size_TimeLimit_Max','14');		// 有効期限（日） max
define('VALUE_Contracts_Size_ApprovalLimit_Min','3');		// 承認期限（日）min
define('VALUE_Contracts_Size_ApprovalLimit_Max','14');	// 承認期限（日） max

//--------------------------------------------
// メール優先度　2014.09.30
//--------------------------------------------

define('VALUE_Mail_Priority_Urgent',-1);	//!< @brief 優先度：緊急（すぐに送信）
define('VALUE_Mail_Priority_High',1);		//!< @brief 優先度：高
define('VALUE_Mail_Priority_Normal',50);	//!< @brief 優先度：普通
define('VALUE_Mail_Priority_Low',99);		//!< @brief 優先度：低
  // すぐに送信かどうかの閾値　↓より少ないときはすぐに送信する　
// 100　のときはすべてすぐに送信（レスポンス遅）
// 50　　通常はこのへんが妥当かと
// -2 　のときはすべてQueue に登録してバッチで送信（レスポンス速）
define('VALUE_Mail_Priority_Threshold',		100);		//!< @brief 閾値

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
Configure::write('VALUE_REMAIN_DISK_SPACE', '1GB');

// 使用する単位の一覧
// バイト/キロ/メガ/ギガ/テラ/ペタ/エクサ/ゼタ/ヨタ
Configure::write('VALUE_SI_PREFIX', array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' ,'EB', 'ZB', 'YB' ));
// ベースとなる値
Configure::write('VALULE_SI_BASE' , 1024);

// 単位が大きくなると誤差が大きくなるため、ベースを1024にするなら ↓ のセットを使用するのが妥当
Configure::write('VALUE_SI_PREFIX_I', array( 'B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB' ,'EiB', 'ZiB', 'YiB' ));
Configure::write('VALULE_SI_BASE_I' , 1024);
