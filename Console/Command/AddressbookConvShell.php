<?php
/**
 * アドレス帳を新しい方式に変換する
 *
 * web 上処理と分離する
 */
App::uses('AppShell','Console/Command');

class AddressbookConvShell extends AppShell {
	public $uses = array(	'User',
							'Addressbook',
							'Addressgroup',
							'Role',
							'Contract',
							'AddressbooksAddressgroup'
//							'Eventlog',
						);
	
    public function main() {
		$msg = ">>>>>>>>>> The batch processing is starting.  [".date("F d Y H:i:s", time() )."]";
		$this->out($msg,1,Shell::NORMAL);
//		$dirpass =  $this->Uploadfile->getTargetPath();
//		$this->out("upload dir :".$dirpass,1,Shell::NORMAL );
		$this->_proc();
		$msg = ">>>>>>>>>> The batch processing is　ending.  [".date("F d Y H:i:s", time() )."]";
		$this->out($msg,1,Shell::NORMAL);
		// ログ書き込み
		$me = $this->getShellId();
    }

	/**
	 * convAddressbook
	 * @todo 	Addressbook テーブルの書き換え
	 * 
	 */
	public function convAddressbook(){
		try{
$this->log('convAddressbook start');
			$this->Addressbook->recursive = -1;
			$ary = $this->Addressbook->find('all');
			foreach($ary as $k => $v){
				// user_id の値を owner_id にコピー
				if($ary[$k]['Addressbook']['owner_id'] == null){
					$owner_id = $ary[$k]['Addressbook']['user_id'];
					$ary[$k]['Addressbook']['owner_id'] = $owner_id;

				}
				// email に対応するユーザがいれば　user_id にコピー
				$user_id = $this->User->getIDfromEmail($v['Addressbook']['email']);				
				$ary[$k]['Addressbook']['user_id'] = ($user_id == null) ? 0 : $user_id ;
				
$this->log('['.$k.']番目を処理しました。 ');
			}
			// DB にセーブ
			$rc = $this->Addressbook->saveAll($ary);
			
			// ここからあとは確認用
$this->log('------------------');
$this->log($rc);
$this->log('------------------');

			$ary2 = $this->Addressbook->find('all');
$this->log($ary2);
			
			$this->log('convAddressbook end');
debug($ary);

			return true;			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}
	
	/**
	 * convAddressgroup
	 * @todo 	Addressgroup テーブルの書き換え
	 * 
	 */
	public function convAddressgroup(){
		try{
$this->log('convAddressgroup start');
			$this->User->recursive = -1;

			// アソシエーションははずしておく
			$this->User->unbindModel(array('hasOne' => array('UserExtension'),
											'belongsTo' => array('Group','Contract')));

			$this->Addressgroup->recursive = -1;
			$role_list = $this->Role->getGroupList(array('controller' => 'addressbooks',
													'action' => 'edit'));
//$this->log($role_list);
			$user_list = $this->User->find('all',array('fields' => array('id','email','group_id','name','addressgroup_id'),
														'conditions' => array('group_id' => array_keys($role_list))));

			foreach($user_list as $k => $v){
//$this->log($v);
				$_flg = true;
				if(is_null($v['User']['addressgroup_id'])){
				} elseif($this->Addressgroup->exists($v['User']['addressgroup_id'])){
					$_flg = false;
				}
				
				if($_flg){

$this->log('-- NOT skip['.$v['User']['addressgroup_id'].']');
					$root = $this->Addressgroup->find('first',
										array('conditions' => 
												array('parent_id' => NULL,
													'user_id' => $v['User']['id'])));
//$this->log($root);													
					if(isset($root['Addressgroup'])){
$this->log('みつかった　のでIDを探す');													
						// 見つかった
						$user_list[$k]['User']['addressgroup_id'] = $root['Addressgroup']['id'];
					} else {
						// 見つからない
$this->log('みつからない　ので作ります');													
						$new_group = $this->Addressgroup->create(array('is_shared' => 'N',
																		'user_id' => $v['User']['id'],
																		'name' => $v['User']['name'] . '_ROOT'));
//$this->log($new_group);													
						$this->Addressgroup->save($new_group);
						$new_group_id = $this->Addressgroup->getLastInsertID();
//$this->log($new_group_id);													
						$user_list[$k]['User']['addressgroup_id'] = $new_group_id;

					}
					$rc = $this->User->save($user_list[$k]);
					if($rc){
$this->log('save OK');
					} else {
$this->log('save NG');
					}

				} else {
$this->log('リンクができてるので触らない');													
				}
					
			}

			// ここからあとは確認用
			$ary2 = $this->Addressgroup->find('all');
//$this->log($ary2);

			$ary3 = $this->User->find('all',array('fields' => array('id','email','group_id','name','addressgroup_id'),
														'conditions' => array('group_id' => array_keys($role_list))));
//$this->log($ary3);

			$this->log('convAddressgroup end');

			return true;			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 * linkAddressAddressgroup
	 * @todo 	Addressbook テーブルの書き換え
	 * 
	 */
	public function linkAddressAddressgroup(){
		try{
$this->log('linkAddressAddressgroup start');
			$this->Addressbook->recursive = -1;
			$this->AddressbooksAddressgroup->recursive = -1;
			$ary = $this->Addressbook->find('all');
			$user_list = $this->User->find('list',array('fields' => array('id','addressgroup_id')));
$this->log($user_list);
			foreach($ary as $k => $v){
				// owner_id の値を user_id にコピー
				$user_id = $v['Addressbook']['owner_id'];
				$addressbook_id = $v['Addressbook']['id'];

				$count = $this->AddressbooksAddressgroup->find('count',
										array('conditions' => array('addressbook_id' => $addressbook_id)));
$this->log('count['.$count.']');
				if($count == 0){
					// どこにもつながっていない。
					$addressgroup_id = $user_list[$user_id];
					if($addressgroup_id == null){
						// 当該ユーザはアドレス帳を持たないので削除
$this->log('addressbook_id['.$addressbook_id.']　削除します');
						$this->Addressbook->delete($addressbook_id);
					} else {
					
$this->log('addressgroup_id['.$addressgroup_id.']');
					// DB にセーブ
					$new = $this->AddressbooksAddressgroup->create(array(
							'addressgroup_id' => $addressgroup_id,
							'addressbook_id' => $addressbook_id));
					$this->AddressbooksAddressgroup->save($new);
					}
				}
			}
			// ここからあとは確認用
			$ary1 = $this->Addressbook->find('all');
$this->log($ary1);

			$ary2 = $this->AddressbooksAddressgroup->find('all');
$this->log($ary2);
			$this->log('linkAddressAddressgroup end');

			return true;			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}
	/**
	 * linkContractAddressgroup
	 * @todo 	Contract　-　Addressgroup 関連付け
	 * 
	 */
	public function linkContractAddressgroup(){
		try{
$this->log('linkAddressAddressgroup start');
			$this->Addressgroup->recursive = -1;
			$this->Contract->recursive = -1;
			$contract = $this->Contract->find('all');
//$this->log($contract);			
			foreach($contract as $k => $v){
				if($v['Contract']['addressgroup_id'] == NULL){
$this->log($contract[$k]['Contract']['name']);
					$root = $this->Addressgroup->find('first',array(
						'conditions' => array( 'is_shared' => 'Y',
												'parent_id' => NULL,
												'contract_id' => $v['Contract']['id'])));
$this->log($root);
					if(isset($root['Addressgroup'])){
$this->log('みつかった　のでIDを探す');													
						// 見つかった
						$contract[$k]['Contract']['addressgroup_id'] = $root['Addressgroup']['id'];
					} else {
						// 見つからない
$this->log('みつからない　ので作ります');													
						$new_group = $this->Addressgroup->create(array('is_shared' => 'Y',
																		'contract_id' => $v['Contract']['id'],
																		'name' => $v['Contract']['name'] . '_ROOT'));
$this->log($new_group);													
						$this->Addressgroup->save($new_group);
						$new_group_id = $this->Addressgroup->getLastInsertID();
$this->log($new_group_id);													
						$contract[$k]['Contract']['addressgroup_id'] = $new_group_id;

					}
					$rc = $this->Contract->save($contract[$k]);
					if($rc){
$this->log('save OK');
					} else {
$this->log('save NG');
					}
												
				}
			}
			// ここからあとは確認用
			$ary3 = $this->Contract->find('all',array('fields' => array('id','name','addressgroup_id')));
$this->log($ary3);

			$this->log('linkAddressAddressgroup end');
			return true;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}
	/**
	 * _proc
	 * @todo 	メインプロセス
	 *
	 */
	public function _proc(){
		try{
			$rc = $this->convAddressbook();
			if(!$rc){
				$this->out('failed convAddressbook',1,Shell::VERBOSE);
			}
			$rc = $this->convAddressgroup();
			if(!$rc){
				$this->out('failed convAddressgroup',1,Shell::VERBOSE);
			}
			$rc = $this->linkAddressAddressgroup();
			if(!$rc){
				$this->out('failed linkAddressAddressgroup',1,Shell::VERBOSE);
			}
			$rc = $this->linkContractAddressgroup();
			if(!$rc){
				$this->out('failed linkContractAddressgroup',1,Shell::VERBOSE);
			}
		} catch(Exception $e){
$this->out(__FILE__ .':'. __LINE__ .': '. $e->getMessage(),1,Shell::QUIET);
		}
	}
	
	
}
