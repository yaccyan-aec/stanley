<?php
/**
 * リリースバージョン番号 2009.11.9 追加
 *  Smarty 廃止：2010.02.04
 *  cake コアバージョンアップに伴い、ファイル名を変更2014.02
 *  bootstrap と helper 差し替え　2014.05
 */
define("VERSION", "5.8.2");

/**
 * 開発中バージョン番号
 * Configure::read('debug'); が　0のとき↑、それ以外のときは↓を表示します。
 */
define("VERSION_DEV", VERSION ." [cake_version ".Configure::version()."]");
?>
