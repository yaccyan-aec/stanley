<?php
/**
 * ディスクの残容量をログに吐き出す
 *
 * cleanup と　autofiledel の前後に起動する
 * 
 * 呼び出し方
 * php cake.php CheckDiskSpaceShell [パラメーター]
 * 
 *  @param string 'before' or 'after'
 */
App::uses('AppShell','Console/Command');
App::uses('ComponentCollection', 'Controller'); 
App::uses('CommonComponent', 'Controller/Component'); 

class CheckDiskSpaceShell extends AppShell {
    
    public $uses = array('User');
    
    /**
    * CommonComponent.phpの呼び出し
    */
    public function startup() {
        $collection = new ComponentCollection(); 
        $this->Common = new CommonComponent($collection); 
        parent::startup();
    }

    public function main() {
        $additional_text = '';
        if (isset($this->args[0])){
            if ($this->args[0] == 'before') {
                $additional_text = '(前)';
            }elseif($this->args[0] == 'after') {
                $additional_text = '(後)';
            }
        }
        $free_space = $this->Common->getDiskFree(Configure::read('Upfile.dir'),true);
        $this->writeLog(
            array(
                'event_action' => 'ディスク残容量'.$additional_text,
                'remark' => 'Shell',
                'result' => $free_space ,
            ));

    }

}
