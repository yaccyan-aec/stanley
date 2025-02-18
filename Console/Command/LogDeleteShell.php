<?php
/**
 * 毎日一回好きな時間帯に実行
 *
 * ６日前のログを削除する
 */

App::uses('File', 'Utility');
App::uses('Folder', 'Utility');

class LogDeleteShell extends Shell {

    public function main() {
        $Folder = new Folder(LOGS. $this->__beforeWeek());
        $files = $Folder->read();
        foreach($files[1] as $file) {
            $File = new File(LOGS. $this->__beforeWeek(). DS. $file);
            $File->delete();
        }
    }

    private function __beforeWeek() {
        $date = new DateTime();
        $date->sub(new DateInterval('P6D')); // 6日前
        return $date->format('w');
    }
}
