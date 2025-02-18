<?php
/**
 * 削除されたユーザが共通アドレス帳に登録されている場合共通アドレス帳から削除する
 *　(ただし一回削除されていても再登録されたユーザがいる場合は削除しない)
 *
 */
class AddressDeleteShell extends Shell {

    public $uses = array('User');

    public function main() {
        // 削除されたユーザ一覧
        $delusers = $this->User->find('list',array(
                'conditions' => array('is_deleted' => true),
                'fields' => array('id','email'),
        ));

        foreach($delusers as $id => $email){
            $this->out($email);
            //削除フラグの立っているemailだが、同じemailで生きているユーザ登録がないか確認する
            $exist = $this->User->find('all', array(
                'conditions' => array('email'=> $email,'User.is_deleted' => false)
            ));
            $delAddRtn = 0;
            if (!$exist){
                // 共通アドレス帳から削除 個人のアドレス帳からは消さない
                $delAddRtn = $this->User->deleteAddress($email);
            }
            if ($delAddRtn == 1){
                $this->out('共通アドレス帳から削除しました');
            }elseif($delAddRtn == 0){
                $this->out('削除しませんでした');
            }

        }
    }
}
