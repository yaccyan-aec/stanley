<?php
/**
 * AppShell file
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
 * @since         CakePHP(tm) v 2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Shell', 'Console');

/**
 * Application Shell
 *
 * Add your application-wide methods in the class below, your shells
 * will inherit them.
 *
 * @package       app.Console.Command
 */
class AppShell extends Shell {
	/**
	 * getShellId
	 * @todo 	shell 起動のときのID（管理者）
	 * 
	 */
	
	public function getShellId(){
		try{
			$me = $this->User->find('first',
				array(	'conditions' => array ('group_id' => 1),
						'recursive' => -1));
			return $me['User'];			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
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
				$auth = $this->getShellId();
            }
			$this->loadModel('Eventlog');		
			if($this->Eventlog->insertLog($args,$auth)){
//$this->log("===== writeLog --- ok",LOG_DEBUG);
				return($this->Eventlog->getLastInsertID());
			} else {
//$this->log("===== writeLog --- err",LOG_DEBUG);
				return 0;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return 0;
	}
	/**
	* function writeDiskSpace
    * @brief syslogにディスク残容量の書き込みを行う
    * Eventlog のユーザ情報に注意！（SUPER権限のgroup_id =1 だと管理者がログを見れないので、新しい管理者権限のユーザを作成する。）
    * @param  mixed  $args  パラメータの内容を syslog テーブルに登録
    * @retval void
    */
    function writeDiskSpace($args){
        try{
            // SUPER権限のgroup_id =1 だと管理者がログを見れないので、新しい管理者権限のユーザを作成する。
            $auth = ['id' => '',
                'name' => 'ログ管理者',
                'email' => 'log@asahi-eg.co.jp',
                'group_id' => 2,
                'contract_id' => 1,
                'division' => ''
            ];
            $this->loadModel('Eventlog');		
            if($this->Eventlog->insertLog($args,$auth)){
                return($this->Eventlog->getLastInsertID());
            } else {
                return 0;
            }
        } catch (Exception $e){
            $this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
        }
        return 0;
    }
}