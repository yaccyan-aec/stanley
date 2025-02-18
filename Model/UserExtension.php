<?php
App::uses('AppModel', 'Model');
/**
 * UserExtension Model
 *
 */
class UserExtension extends AppModel {
	
	var $actsAs = array(
	'Containable',
	'Search.Searchable'
	);

/**
 * sanitizeItems
 * 		sanitize したい項目を定義すると、appModel で自動的にやってくれる。
 * @var array : フィールド名 => html (true = タグを削除 / false = タグをエスケープ)
 *             or array( 'html' => true ,       // true / false / 'info' = 一部タブ許容
 *                       'serialize' => false ,  // true / false
 *                       'encode' => 'base64'   // 'base64' / 'none' 
 *                     )
 *
 */
	var $sanitizeItems = array(
								'name_jpn' => array('html' => true, ),
								'name_eng' => array('html' => true, ),
								'div_jpn' => array('html' => true, ),
								'div_eng' => array('html' => true, ),
								'custom_01' => array('html' => true, ),
								'custom_02' => array('html' => true, ),
								'text_01' => array('html' => false, ),
								);	
/**
 * Validation rules
 *
 * @var array
 */
 
	public $validate = array(
	);	
	
	var $validationSets = array(
		'default' => array(
			'custom_01' => array(
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					/**
					 *  theme のpo で　array でくくったものは、theme の po を使用する
					 */
					'message' => array('%s characters too long.',array('custom_01'))
				),
			),
			'custom_02' => array(
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					'message' => array('%s characters too long.',array('custom_02'))
				),
			),			
			'name_jpn' => array(
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					'message' => array('%s characters too long.','Name_Jpn')
				),
			),
			'name_eng' => array(
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					'message' => array('%s characters too long.','Name_Eng')
				),
			),
			'div_jpn' => array(
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					'message' => array('%s characters too long.','div_jpn')
				),
			),
			'div_eng' => array(
				'maxStringLength' => array(
					'rule' => array('maxStringLength', MAX_STRLEN),
					'message' => array('%s characters too long.','div_eng')
				),
			),
		)
	);

	
	/**
	 * clearTmppasswordId
	 * 仮パスワードテーブル削除　
	 *
	 * @param  string	$email  	ログインID
	 * @return bool 
	 */
	function clearTmppasswordId($tmppassword_id = null){
		try{
//debug($tmppassword_id);	
			$this->recursive = -1;
			$data = $this->find('all',
						array('conditions' =>
							array('tmppassword_id' => $tmppassword_id)));
//debug($data);	
			if($data){
				$rc = true;
				foreach($data as $k => $v){
					$rc = parent::save(array('id' => $v[$this->name]['id'],
										 'tmppassword_id' => null,
									));
					if(!$rc) break;				
				}					
				return $rc;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 * setExpEnd
	 * 期限切れフラグ設定　
	 *
	 * @param  mix	$id  	ID or email
	 * @return bool 
	 */
	function setExpEnd($id = null,$flg = VALUE_Flg_None){
		try{
			$data = array();
			$this->loadModel('User');
			$this->User->recursive = 0;
			if(is_numeric($id)){
				$data = $this->User->findById($id);
			} else {
				$data = $this->User->findByEmail($id);
			}
			if($data){
				$this->recursive = -1;
				if($flg == VALUE_Flg_Lock){
					if($data[$this->name]['expdate_apply_flg'] > $flg){
$this->log('setExpEnd --- skip');
						return true; // skip
					}
				}
				$rc = parent::save(array('id' => $data['User']['id'],
								 'expdate_apply_flg' => $flg,
							));
				return $rc;				
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}


	
}
