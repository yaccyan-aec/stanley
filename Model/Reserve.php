<?php
App::uses('AppModel', 'Model');
/**
 * Reserve Model
 *
 * @property Content $Content
 * @property Syslog $Syslog
 */
class Reserve extends AppModel {

/**
 * sanitizeItems
 * 		sanitize したい項目を定義すると、appModel で自動的にやってくれる。
 * @var array : フィールド名 => html (true = タグを削除 / false = タグをエスケープ)
 *             or array( 'html' => true ,       // true / false / 'info' = 一部タブ許容
 *                       'serialize' => true ,  // true / false
 *                       'encode' => 'base64'   // 'base64' / 'none' 
 *                     )
 *
 */
	var $sanitizeItems = array(
								'title' => array('html' => false,
												'serialize' => false,
												'encode' => 'none',
												),
								'rsv_data' => array('html' => false,
												'serialize' => true,
												'encode' => 'none',
												));

/**
 * Use behavior
 *
 * 削除フラグ（tinyint) => 削除日(datetime) のフィールド名カスタマイズ
 * デフォルトは 'deleted' => 'deleted_date'
 */
//	var $actsAs = array('SoftDelete' => array(
//			'is_deleted' => 'deleted',
//		)); 
/**
 * Use database config
 *
 * @var string
 */
//	public $useDbConfig = 'default';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'user_id' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				'last' => false,
				'message' => 'The blank is not goodness.',
			),
			'numeric' => array(
				'rule' => array('numeric'),
				'last' => false,
				'message' => 'This item should be a number.',
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

/**
 * beforeValidate　validate前処理
 * @todo 	　db save 前に行う共通関数
 * @param    array	$this->data    
 * @param    array 	$this->sanitizeItems
 * @return   bool 
 */
	function beforeValidate($options = array()) { 
		try{
//$this->log(__FILE__ .':'. __LINE__ .':beforeValidate1');		
//$this->log($this->data[$this->name]);
			if(isset($this->data[$this->name]['rsv_data'])){
//$this->log(__FILE__ .':'. __LINE__ .':beforeValidate2');		
				$data = $this->data[$this->name]['rsv_data'];
				if(isset($data['Content'])){
					if(isset($data['Content']['title'])){
						$this->data[$this->name]['title'] = $data['Content']['title'];
					}
				}
				$this->loadModel('Uploadfile');
				if(Hash::check($data,'Uploadfile')){
					$upload = $this->Uploadfile->FileExistsSetFlg($data['Uploadfile']);
					$this->data[$this->name]['rsv_data']['Uploadfile'] = $upload;
				}
				if(Hash::check($data,'Content.Uploadfile')){
					$upload = $this->Uploadfile->FileExistsSetFlg($data['Content']['Uploadfile']);
					$this->data[$this->name]['rsv_data']['Content']['Uploadfile'] = $upload;
				}
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		parent::beforeValidate($options);
	}

/**
 * reserve
 * @todo セーブ
 * @param    mix $data :     
 * @return   int $id :
 */
	public function savedata($uid,$data = null,$rid = null){
		try{
			$this->recursive = -1;
			if($data == null) return false;
			$savedata = array();
			if($rid == null){
				$savedata = $this->create();
			} else {
				$savedata = $this->findById($rid);
				$savedata[$this->name]['modified'] = null;
			}
			$savedata[$this->name]['user_id'] = $uid;
			$savedata[$this->name]['rsv_data'] = $data;
			$rc = $this->save($savedata);
			if($rc){
				return ($rid == null) ? $this->getLastInsertID() : $rid;
			} 
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;

	}

/**
 * softdelete
 * @todo 削除するときだけ SoftDelete ビヘイビアを使用する
 * デバッグモードのときは消さない
 * @param string $id : 削除するID
 * @return bool : 結果
 */
	public function softdelete($id = null, $cascade = true) {
		try{
			if ($this->exists($id)) {
				parent::save(array(
					'id' => $id,
					'is_deleted' => true,
					'deleted' => $this->now(),
				));
				return true;
			}
			return false;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}
/**********************************************************
 *  以下のメソッドは再送処理でしか使用しないので、プラグインに移動
 *********************************************************/
/**
 * dataCopy
 * @todo data が使用済だったらコピーして登録する。
 * たぶんこっちはコピーしていない
 * @param int $rid
 * @return array $new_data : 結果
 */
//	public function dataCopy($rid = null) {
//$this->log('plugin じゃない方　datacopy start['.$rid.']　ここにきますか？');			
//		$rdata = array();
//		try{
//			$this->recursive = -1;
//			$rdata = $this->findById($rid);
//$this->log('datacopy start['.$rid.']');			
//			if($rdata[$this->name]['is_deleted']){
//$this->log('コピーしました');			
//// 使用済なのでコピー
//				$newdata = $this->create();
//				$newdata[$this->name] = $rdata[$this->name];
//				$newdata[$this->name]['id'] = null;
//				$newdata[$this->name]['created'] = null;
//				$newdata[$this->name]['modiried'] = null;
//				$newdata[$this->name]['deleted'] = null;
//				$newdata[$this->name]['is_deleted'] = false;
//				$newdata[$this->name]['rsv_data']['Content']['title'] .= __d('reserves','[Resending]');
//				$new = $this->save($newdata);
//				$rtndata = $this->findById($new[$this->name]['id']);
//				return $rtndata;
//			}
//$this->log('コピーしません');			
//		} catch (Exception $e){
//$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
//		}
//		return $rdata;
//	}
	
/**
 * FileExistsCheck
 * @todo data に記載されているファイルがまだ存在するかチェックする
 * デバッグモードのときは消さない
 * @param array $rdata
 * @return array $new_data : 結果
 */
//	public function FileExistsCheck($rdata = array()) {
//		try{
//			$this->recursive = -1;
//			$this->loadModel('Uploadfile');
//			if(Hash::check($rdata,$this->name.'.rsv_data.Uploadfile')){
//				$upload = $this->Uploadfile->FileExistsSetFlg($rdata[$this->name]['rsv_data']['Uploadfile']);
//				$rdata[$this->name]['rsv_data']['Uploadfile'] = $upload;
//			}
//			if(Hash::check($rdata,$this->name.'.rsv_data.Content.Uploadfile')){
//				$upload = $this->Uploadfile->FileExistsSetFlg($rdata[$this->name]['rsv_data']['Content']['Uploadfile']);
//				$rdata[$this->name]['rsv_data']['Content']['Uploadfile'] = $upload;
//			}
//			$new = $this->save($rdata);
//			$rtndata = $this->findById($new[$this->name]['id']);
//			return $rtndata;
//		} catch (Exception $e){
//$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
//		}
//		return $rdata;
//	}
	

}
