<?php
/**
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       jp.co.asahi-eg.fts
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
Cache::config('default', array('engine' => 'File'));
Configure::write('Dispatcher.filters', array(
	'AssetDispatcher',
	'CacheDispatcher'
));

/**
 * Configures default file logging options
 */
App::uses('CakeLog', 'Log');
/**
 * log はローテーション用に曜日ごとに別のファイルに書き込む
**/

$date = new DateTime();
CakeLog::config('debug', array(
	'engine' => 'File',
	'types' => array('notice', 'info', 'debug'),
	'file' => $date->format('w'). DS. 'debug'.$date->format('-m-d'),
	'mask' => 0666
));
CakeLog::config('error', array(
	'engine' => 'File',
	'types' => array('warning', 'error', 'critical', 'alert', 'emergency'),
	'file' => $date->format('w'). DS. 'error'.$date->format('-m-d') ,
	'mask' => 0666
));


//print_r($_SERVER);
//www 以下のフォルダ名と Env/ の設定フォルダ名を合わせる
config(	'Env/version',
		'Env/constdef' );

$my_system = MY_APP;
config(	'Env/'.MY_APP);

// メールに表記する　URI エンコード指定　true : エンコードあり / false : エンコードなし
if (!defined('MAIL_URI_ENCODE')) {
  define('MAIL_URI_ENCODE', true);  //URI エンコード指定
}


// 設定の切り替えがおかしいときは↓を活かしてチェック
// ↓これを出すと、画像認証が出ません。
//print_r('my_system['.$my_system.']');
//print_r(ROOT);
//print_r($_SERVER);
// プラグインまわりでエラーになるときは、config を呼ぶ前にdefine する必要があるようです。
// 普段はここにくるはずです。
define('MySystem' , $my_system);

//入力値の最大文字数（デフォルト値＝100） 入力画面で制御
//define('MAX_STRLEN',100);			// Env/constdef.php に移動しました2016/09/15

/*****************************************************
 * プラグインの設定
 *****************************************************/

CakePlugin::load('Maintenance');	// メンテナンスモード　2015/12/01
Configure::write('Maintenance.enable', false); // [T] メンテナンスモード / [F] 通常モード
//define('LOGIN_PERMISSION',true);			//!< @brief [T] ログイン可否　メンテナンスモードを入れたので削除

/* アップグレード用 */
CakePlugin::load('VersionUp',array('bootstrap'=>true ));		//!< @brief データ変換バッチ

CakePlugin::load('BoostCake');		//!< @brief デザイン用(Pagenatorで使用)
CakePlugin::load('Search');			//!< @brief 検索プラグイン
CakePlugin::load('SearchPagination');	//!< @brief 検索ページネーションSearch プラグインと連携予定
CakePlugin::load('Transition');		//!< @brief ページ遷移を管理

CakePlugin::load('Inquiries',array('bootstrap'=>true ));		//!< @brief 問い合わせ*
CakePlugin::load('Histories',array('bootstrap'=>true ));		//!< @brief 更新履歴*
CakePlugin::load('Syslogs',array('bootstrap'=>true ));			//!< @brief ログ出力*
CakePlugin::load('Reserves',array('bootstrap'=>true ));			//!< @brief 送信リザーブ管理*

CakePlugin::load('Addressbooks',array('bootstrap'=>true ));		//!< @brief アドレス帳管理（辻さん作成中）
CakePlugin::load('Migrations');		//!< @brief DB管理（マイグレーション）


Configure::write('debug', 2);				//!< @brief debug Level
if(Configure::read('debug') > 0){
	// デバッグモードのときだけ
	CakePlugin::load('DebugKit');		//!< @brief デバッグキット
}

//--------------------------------------------
// デフォルトテーマ（とりあえず日本語）2010.02.23
//--------------------------------------------
Configure::write('Config.language', VALUE_System_Default_Lang);	//!< @brief  デフォルト言語


$_theme = strtolower(Configure::read('Config.theme'));
// 小文字にする
Configure::write('Config.theme',$_theme);

switch($_theme){
	case 'nifco':
		// nifco 用ロケール
		CakePlugin::load('Nifco',array('bootstrap'=>true));				//!< @brief 「メールで承認」をあとで分離するかも
		CakePlugin::load('Errmails',array('bootstrap'=>true ));			//!< @brief 不達メール管理*
		CakePlugin::load('Sections',array('bootstrap'=>true ));			//!< @brief 部門管理　(ひな型)
		CakePlugin::load('Templates',array('bootstrap'=>true ));		//!< @brief テンプレート*
	break;
	case 'molex':
		// molex 用ロケール
		CakePlugin::load('Molex',array('bootstrap'=>true));
		CakePlugin::load('Errmails',array('bootstrap'=>true ));			//!< @brief 不達メール管理*
        // 暫定的に追加 ユーザ管理エクスポートで必要
        //CakePlugin::load('Sections',array('bootstrap'=>true ));			//!< @brief 部門管理　(承認)
	break;
	case 'stanley':
		// stanley 用ロケール
		CakePlugin::load('Stanley',array('bootstrap'=>true));
		CakePlugin::load('AutoStore',array('bootstrap'=>true)); 		//!< @brief ストレージ
//		CakePlugin::load('Errmails',array('bootstrap'=>true ));			//!< @brief 不達メール管理*
		CakePlugin::load('Sections',array('bootstrap'=>true ));			//!< @brief 部門管理　(承認)
		CakePlugin::load('Vscan',array('bootstrap'=>true ));			//!< @brief ウイルススキャン
		CakePlugin::load('Encrypt',array('bootstrap'=>true ));			//!< @brief パスワードZIP
		CakePlugin::load('Tfg',array('bootstrap'=>true ));				//!< @brief Tfg
		CakePlugin::load('Pscan',array('bootstrap'=>true ));			//!< @brief パスワードZIP
		CakePlugin::load('Templates',array('bootstrap'=>true ));		//!< @brief テンプレート*
	break;

//2016/12/22noumoto追加　Shared用のロケールを追加したいため
	case 'shared':
		// shared 用ロケール
        CakePlugin::load('Shared',array('bootstrap'=>true));
		CakePlugin::load('Errmails',array('bootstrap'=>true ));			//!< @brief 不達メール管理*
		CakePlugin::load('Templates',array('bootstrap'=>true ));		//!< @brief テンプレート*
	break;

	default:
		// その他（必要に応じて追加）
	break;
}
