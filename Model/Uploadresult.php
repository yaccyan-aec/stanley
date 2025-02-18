<?php
App::uses('AppModel', 'Model');
/**
 * Uploadresult Model
 *
 */
class Uploadresult extends AppModel {
	
/**
 * saveResult 契約の容量よりアップロード量がオーバーしてるか調べる
 * @todo 	　パラメータにより、初期値があったら設定する
 * @param    array	$this->request->params  
 * @return   int	オーバーしたときは uploadresult_id
 *					オーバーしてないときは　0
 */
	function saveResult($data = array()){
$this->log('Uploadresult saveResult start',LOG_DEBUG);	
//$this->log('Uploadresult saveResult ['.print_r($data,true).']',LOG_DEBUG);	
		$lastid = 0;	
		try{
			// 送信者とオーナーを求める
			$user_id = Hash::get($data,'Content.user_id');
			$owner_id = Hash::get($data,'Content.owner_id');
			
			// 権限を求める
			$this->loadModel('User');
			$this->User->recursive = -1;
			$user = $this->User->findById($user_id);
			$group_id = Hash::get($user,'User.group_id');

			$owner = $this->User->findById($owner_id);
			
			// 契約を求める（ゲストの返信だったら、オーナーの契約）
			$contract_id = 0;
			if($user_id == $owner_id){
				$contract_id = Hash::get($user,'User.contract_id');
			} else {
				$contract_id = Hash::get($owner,'User.contract_id');
			}
			// 契約の現状を調べる
			$this->loadModel('Contract');
			$this->Contract->recursive = -1;
			$contract = $this->Contract->findById($contract_id);
			$size = -1;
			if(Hash::check($contract,'Contract.size')){
				$_maxsize = Hash::get($contract,'Contract.size');
				$size = $_maxsize * pow( 10 ,9);	// ギガになおす
			}
$this->log('Uploadresult 契約　size ['.$size.']',LOG_DEBUG);		
			// 現在使っている容量を計算する
			$now_size = $this->Contract->getAmount($contract_id);
$this->log('Uploadresult now_size ['.$now_size.']',LOG_DEBUG);		
			
			// --------------- これから実装↓
			// 今回の登録で増えるファイルサイズを計算する
			$add_size = 0;
			foreach($data['Uploadfile'] as $k => $v){
				// [tmp_name] 項目があるものだけが新規追加ファイル
				if(Hash::check($v,'Uploadfile.tmp_name')){
					$add_size += Hash::get($v,'Uploadfile.size');
				}
			}
$this->log('Uploadresult add_size ['.$add_size.']',LOG_DEBUG);		
			
			// 容量をオーバーしていたらアラートメール（あとで）
			$total_size = $now_size + $add_size;
$this->log('Uploadresult total_size ['.$total_size.']',LOG_DEBUG);		
			if(($add_size > 0) && ($size < $total_size)){
				$sabun = $total_size - $size;
$this->log('Uploadresult オーバーしました ['.$sabun.']',LOG_DEBUG);
				$oversize = 0;
				if($now_size >= $size){
					// 最初からオーバーしていたら、アップロード分は
					// まるまるオーバーとなる
					$oversize = $add_size;
				} else {
					//　今回でオーバーしたら、超過分
					$oversize = $sabun;
				}
				$savedata = $this->create(array(
					'user_id' => $data['Content']['user_id'],
					'group_id' => $group_id,
					'contract_id' => $contract_id,
					'size' => $add_size,
					'oversize' => $oversize,
					'is_over' => 'Y',
					));
				$this->save($savedata);
				$lastid = $this->getLastInsertID();
				// Uploadresult に書きこむ（オーバーしたときだけにしようかな）
			}
			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $lastid;
	}
	

}
