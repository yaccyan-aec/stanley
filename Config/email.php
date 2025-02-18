<?php
/**
 * This is email configuration file.
 *
 * Use it to configure email transports of Cake.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 *
 * Email configuration class.
 * You can specify multiple configurations for production, development and testing.
 *
 * transport => The name of a supported transport; valid options are as follows:
 *		Mail 		- Send using PHP mail function
 *		Smtp		- Send using SMTP
 *		Debug		- Do not send the email, just return the result
 *
 * You can add custom transports (or override existing transports) by adding the
 * appropriate file to app/Network/Email. Transports should be named 'YourTransport.php',
 * where 'Your' is the name of the transport.
 *
 * from =>
 * The origin email. See CakeEmail::from() about the valid values
 *
 */
 /**
  * メールサーバの本番は、ここで個別に設定しておき、Env/xxxx.php のなかで
  * define('VALUE_MailConfig','default');	//!< @brief メールサーバ設定用 config 
  * のように対応付ける方法と、ここに指定する設定内容をまえもって定義する方法がある。
  * Env/ 内のファイルにハッシュを定義または、Configure::write してここで切り替えるのは
  * できない。
  * 'from' はメール送信時に設定する。
  */
class EmailConfig {
	
	public $default = array(
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
		'password' => VALUE_Mail_Password,		//
		'client' => VALUE_Mail_Client,
		'log' => VALUE_Mail_Log,
		'charset' => VALUE_Mail_Charset,
		'headerCharset' => VALUE_Mail_HeaderCharset,
//		'returnPath' => VALUE_Mail_FromKey,
		);
}
