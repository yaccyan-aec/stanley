<?php
App::uses('ModelBehavior', 'Model');
App::uses('Validation', 'Utility');
class ValidateBehavior extends ModelBehavior {

/**
 * range 数値の範囲チェック
 * @todo パラメータがややこしいので、lower -1, upper +1 して本来の機能に渡す
 * @return   bool  : true = ok / false = ng
 */
	function chkRange(Model $model, $check,  $lower = null, $upper = null){
$this->log('chkRange start ');
		$chk = 0;
		if(is_array($check)){
			foreach($check as $k => $v){
				$chk = $v;
				break;
			}
		} else {
			$chk = $check;
		}
		$low = $lower -1;
		$up = $upper +1;

$this->log('chkRange chk['.$chk.']low['.$low.'] up['.$up.']');
		$rtn = Validation::range($chk,  $low, $up);
		if($rtn){
$this->log('chkRange OK');
		} else {
$this->log('chkRange NG');
		}
		return $rtn;
		
	}


/**
 * isNotUnique　重複チェック
 * @todo 	isUnique の逆のフラグを返す
 * ここは $this->data は見ることが出来るらしい。
 * @param    array $field    　チェック項目 (自動的に入ってくる)
 * @param    array $chkfields　一緒にチェックする項目 array('user_id','contract_id) など
 * @return   bool  : true = 登録されていない / false = 登録されている
 */
	function isNotUnique(Model $model, $fields, $or = true){
$this->log('isNotUnique start');
		$rtn = $model->isUnique($fields, $or);
$this->log('isNotUnique rtn['.$rtn.']');
		return !$rtn;
		
	}

/**
 * isUniqueWith　複数キーの重複チェック
 * @todo 	isUnique の逆のフラグを返す
 * ここは $this->data は見ることが出来るらしい。
 * @param    array $field    　チェック項目 (自動的に入ってくる)
 * @param    array $chkfields　一緒にチェックする項目 array('user_id','contract_id) など
 * @return   bool  : true = 登録されていない / false = 登録されている
 */
	function isUniqueWith(Model $model, $data, $fields, $chk = true){
$this->log('isUniqueWith　複数キーの重複チェック start');
$this->log($data);
$this->log($fields);
		if(!is_array($fields)) {
			$fields = array($fields);
		}
		$fields = array_merge($data,$fields);
$this->log($fields);
		if($chk){
			$rtn = $model->isUnique($fields, false);
$this->log('isUnique call rtn['.$rtn.']');
		} else {
			$rtn = $model->isNotUnique($fields, false);
$this->log('isNotUnique call rtn['.$rtn.']');
		}
$this->log('isUniqueWith　複数キーの重複チェック rtn['.$rtn.']');
		return $rtn;
		
	}
	
	
/**
 * isDuplicate　重複チェック
 * @todo 	user_id と email が両方とも重複するときは登録しない
 * ここは $this->data は見ることが出来るらしい。
 * @param    array $field    　チェック項目 (自動的に入ってくる)
 * @param    array $chkfields　一緒にチェックする項目 array('user_id','contract_id) など
 * @param    bool  $ok :　OKの条件　true （当該条件でみつかったとき） / false （みつからなかったとき）
 * @param    array $options :　追加オプション　array('is_deleted' => 'N') など
 * @return   bool  : true = 登録OK / false = 登録NG
 */
	function isDuplicate(Model $model ,$field,$chkfields = array(),$ok=false, $options = null) {
		try{
			$cond = array();
			if(is_array($chkfields)){
				foreach($chkfields as $k => $v){
					$cond[$v] = $model->data[$model->name][$v];
				}
			}

			if(is_array($options)){
				foreach($options as $k => $v){
					$cond[$k] = $v;
				}
			}
			$cond += $field;
			
			$model->recursive = -1;
			$found = $model->find('all',array( 'conditions' => $cond));
			$rc = ($ok == $found) ? true : false;
$this->log($cond);
$this->log($found);
$this->log('isDuplicate : chk ['.$rc.']');
			return $rc;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. $e->__toString());
			return false;
		}

	}

 /** 
 * getValidationErrors
 * @todo 	validationErrors を整形
 * @param    string $prefix    前につける文字列
 * @param    string $suffix    後ろにつける文字列
 * @return   string	$message
 */
   function getValidationErrors(Model $model ,$prefix = null, $suffix = null){
 		try{
			$message = __($prefix);
			$errors = $model->validationErrors;				
			if(is_array($errors)){
				$message .= '<br>';
				foreach($errors as $k => $v){
					$message .= __($k) . ' : ';
					foreach($v as $msg){
						$message .= __($msg) . '<br>';
					}
				}
			}
			$message .= __($suffix);
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $message;
	}	
	
	
}
