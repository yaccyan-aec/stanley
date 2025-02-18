<?php
/**
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       jp.co.asahi-eg.fts
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
/**
 * 'datasource' => 'MysqlLog' に変更（デバッグ用にSQL をファイルに書き出すスクリプトを追加）2013.10.15
 * system により自動的に接続するDBを切り替える
 * 追加するときは、bootstrap に新たな　my_system を追加し、このファイルのcase も追加する。
 */

class DATABASE_CONFIG {
	public $default = array(
		'datasource' 	=> VALUE_default_datasource,
		'persistent' 	=> VALUE_default_persistent,
		'host' 			=> VALUE_default_host,
		'port' 			=> VALUE_default_port,
		'login'			=> VALUE_default_login,
		'password' 		=> VALUE_default_password,
		'database' 		=> VALUE_default_database,
		'prefix' 		=> VALUE_default_prefix,
		'encoding' 		=> VALUE_default_encoding ,
	);
	
	public $test = array(
		'datasource' 	=> VALUE_default_datasource,
		'persistent' 	=> VALUE_default_persistent,
		'host' 			=> VALUE_default_host,
		'port' 			=> VALUE_default_port,
		'login'			=> VALUE_default_login,
		'password' 		=> VALUE_default_password,
		'database' 		=> VALUE_default_database,
		'prefix' 		=> 'test_',
		'encoding' 		=> VALUE_default_encoding ,
	);

/**
 *	ver4.x のテーブルからデータを移行するための定義
 *  
 **/
	public $v4 = array(
		'datasource' 	=> VALUE_default_datasource,
		'persistent' 	=> VALUE_default_persistent,
		'host' 			=> VALUE_default_host,
		'port' 			=> VALUE_default_port,
		'login' 		=> VALUE_default_login,
		'password' 		=> VALUE_default_password,
		'database' 		=> VALUE_old_database,
		'prefix' 		=> VALUE_old_prefix,
		'encoding' 		=> VALUE_default_encoding,
	);

	public $v5 = array(
		'datasource' 	=> VALUE_default_datasource,
		'persistent' 	=> VALUE_default_persistent,
		'host' 			=> VALUE_default_host,
		'port' 			=> VALUE_default_port,
		'login'			=> VALUE_default_login,
		'password' 		=> VALUE_default_password,
		'database' 		=> VALUE_new_database,
		'prefix' 		=> VALUE_default_prefix,
		'encoding' 		=> VALUE_default_encoding ,
	);

    public function __construct()
    {
		$_mysystem = '';
		if(defined('MySystem')){
			$_mysystem = MySystem;
		} else {
			$_mysystem = 'default';
		}
	}

}

