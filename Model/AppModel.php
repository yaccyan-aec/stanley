<?php
/**
 * Application model for CakePHP.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
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
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Shell', 'Console');
App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {
/**
 * ActsAs
 *
 * @var array
 */
	var $actsAs = array('Common',
						'Multivalidatable',
						'Sanitize',
						'Validate',
						'Containable',
						);


/**
 * sanitizeItems
 * 		sanitize したい項目を定義すると、appModel で自動的にやってくれる。
 * @var array : フィールド名 => html (true = タグを削除 / false = タグをエスケープ)
 *             or array( 'html' => true ,       // true / false / 'info' = 一部タブ許容 
 *  											// info は使わない方向で・・・・2016
 *                       'serialize' => true ,  // true / false
 *                       'encode' => 'base64'   // 'base64' / 'none' 
 *                     )
 *
 */
	var $sanitizeItems = array('name' => true);

	
	function now(){
		$d = getdate();
		$today = date('Y-m-d H:i:s',$d[0]);
		return($today);
	}

	function today(){
		$d = getdate();
		$today = date('Y-m-d',$d[0]);
		return($today);
	}
	
	/**
	 * 起動元が shell かどうかを判定
	 */
	function is_shell(){
		if(php_sapi_name() === 'cli'){
			return true;
		} else {
			return false;
		}
	}

	
	/**
	 * 起動元が shell のときだけコンソール出力
	 */
	public function out($message = null, $newlines = 1, $level = 1) {
		try{
			if(php_sapi_name() === 'cli'){
				$currentLevel = Shell::NORMAL;
				if (!empty($this->params['verbose'])) {
					$currentLevel = 2;
				}
				if (!empty($this->params['quiet'])) {
					$currentLevel = 0;
				}
				if ($level <= $currentLevel) {
					print_r($message);
					print("\n");
					return true;
				}
				return false;
			} else {
				return $this->log($message);
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}
	/** ======================================================================================
	 * loadModel メソッド
	 * ヘルパーにも類似の処理を追加
	 * @param 	$modelName	： ロードしたいModel
	 * 				プラグインの場合は、$this->class にオブジェクトが作られる
	 * 第2パラメータ　flg = true : クラスがあっても強制変更　/ false :　存在すればそのまま戻る
	 * ======================================================================================
	 */
	function loadModel($modelName,$flg = false) {
		list($plugin, $class) = pluginSplit($modelName);
		try{
			if (!$flg && !empty($this->{$class})) {
			// すでに存在すればそのままreturn
				return;
			} else {
//$this->log(__FILE__ .':'. __LINE__ .': remove['.$class.']強制変更['.$modelName.']');
				
				//ClassRegistry::removeObject(Inflector::underscore($class));
				$this->{$class} =  ClassRegistry::init($modelName);
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return;
	}
	
	/** ======================================================================================
	 * loadComponent メソッド
	 * @param 	$componentClass	： ロードしたいcomponent
	 * 				プラグインの場合は、$this->class にオブジェクトが作られる
	 * ======================================================================================
	 */
	public function loadComponent($componentClass, $settings = array()) {
		if (!isset($this->{$componentClass})) {
			if (!isset($this->Components)) {
				$this->Components = new ComponentCollection();
			}
			App::uses($componentClass, 'Controller/Component');
			$this->{$componentClass} = $this->Components->load($componentClass, $settings);
		}
	}
/**
 * beforeValidate　validate前処理
 * @todo 	　db save 前に行う共通関数
 * @param    array	$this->data    
 * @param    array 	$this->sanitizeItems
 * @return   bool 
 */
function getOptions ($option = null) {
	/**
	* デフォルト値：
	*　'html' = true			: html タグ削除　実施
	*　'serialize' = false	: シリアライズ　なし
	*　'encode' = 'none'		: エンコード　なし
	*/
	$opt_ary = array(
					'html' => true,
					'serialize' => false,
					'encode' => 'none'
					);
	if(empty($option)){
	return $opt_ary;
	}
	if(is_array($option)){
		foreach($option as $k => $v){
			if(is_null($v)) continue;
			$opt_ary[$k] = $v;
		}
	} else {
		$opt_ary['html'] = $option;
	}
	return $opt_ary;
}

/**
* テーブル内容消去
*/
function truncate( $tableName = null )
{
  if (is_null($tableName)) {
    $tableName = $this->table;
  }
 
  if (!$tableName || is_null($tableName)) {
    return false;
  }
 
  return $this->getDataSource()->truncate($tableName);
}
/**
 * beforeValidate　validate前処理
 * @todo 	　db save 前に行う共通関数
 * @param    array	$this->data    
 * @param    array 	$this->sanitizeItems
 * @return   bool 
 */
//	function beforeValidate($options = array()) {
	function beforeValidate() {
//debug('beforeValidate start ['.$this->name.']');
		foreach ($this->sanitizeItems as $field => $html){
			if(isset($this->data[$this->name][$field])){
				$tmp = $this->data[$this->name][$field];
				$options = $this->getOptions($html);
				if(is_array($tmp)){
					// array だったらhtml は無視シリアライズのみ適用
					if($options['serialize']){
						$this->data[$this->name][$field] = serialize($this->data[$this->name][$field]);
					}
				} else {
					if($options['html'] === 'info'){
						/**
						* お知らせなど、一部タグを許容するとき　今後使わない予定（たぶん）2016
						*/
						$this->data[$this->name][$field] = $this->sanitizeInfoString($tmp,true);
					} else{
						$this->data[$this->name][$field] = $this->sanitizeString($tmp,$options['html']);
					}
					if($options['serialize']){
						// array じゃなかったらやらない
						if(is_array($this->data[$this->name][$field])){
							// 何も入っていなければやらない
							if(strlen(trim($this->data[$this->name][$field])) > 0){
								$this->data[$this->name][$field] = serialize($this->data[$this->name][$field]);
							}
						}
					}
				}
				switch($options['encode']){
					case 'base64':
						$this->data[$this->name][$field] = base64_encode($this->data[$this->name][$field]);
						break;
					default:
						break;
				}
			}
		}
//debug($this->data);
		return true;
	}
/**
 * afterFind find 後処理
 * @todo 	find 後に行う共通関数
 * @param   array $results    
 * @param   array $this->sanitizeItems
 * @return   array $results  
 */
	function afterFind($results, $primary = false) { 
//debug($results);
		foreach ($results as $idx => $rec) {
			if(is_numeric($idx)){
				foreach ($this->sanitizeItems as $field => $html){
					if(isset($rec[$this->name][$field])){
						$options = $this->getOptions($html);
						switch($options['encode']){
							case 'base64':
//debug('base64 とおります['.$idx.']['.$field.']');
								$results[$idx][$this->name][$field] = base64_decode($results[$idx][$this->name][$field]);
								break;
							default:
								break;
						}

						// シリアライズの復元
						$_bak = $results[$idx][$this->name][$field];
						try{
						$_tmp = $results[$idx][$this->name][$field];
							$_ary = $this->safe_unserialize($_tmp);
							if(is_array($_ary)){
								// array だったらサニタイズ復元をしない
								$results[$idx][$this->name][$field] = $_ary;
							} elseif(strlen($_ary) > 0) {
								// 長さがあればサニタイズ復元
								$_sani = $this->reverseSanitize($_ary);
								$results[$idx][$this->name][$field] = $_sani;
							} else {
								// なにもしない
							}
						} catch (Exception $e){
$this->log('===== unserialize ERR['.$e->__toString().']',LOG_DEBUG);
							$results[$idx][$this->name][$field] = $_bak;
						}
					}
				}
			}
		}
		return $results;
	}

	/**
	* unserialize
	* シリアライズされていなかったらそのまま返す
	*/

	function safe_unserialize($serialized) {
		try{
			$_test = @unserialize($serialized);
			// array なら実行結果を返す
			if(is_array($_test)) return $_test;
			// string でも長さがあれば返す
			if(strlen($_test) > 0) return $_test;
			return($serialized);
		} catch (Exception $e){
$this->log('safe_unserialize err['.$e->__toString().']',LOG_DEBUG);
		return($serialized);
		}

	}

/* appModel　差し替えてみる */
	//
	// Fromcreated検索条件
	// @param  array  $data 
	// @return array
	//

	public function fromCreatedSearch($data = array()) {
		$conditions = array();
		$from_created = trim($data['from_created']);
		// 値が設定されていたら条件をセット
		if(!empty($from_created)){
			$conditions[$this->filterArgs['from_created']['field']. ' >='] = $this->setEndDate($from_created, true);
		}
		return $conditions;
	}	
	
	//
	// ToCreated検索条件
	// @param  array  $data 
	// @return array
	//
	public function toCreatedSearch($data = array()) {
		$conditions = array();
		$from_created = trim($data['from_created']);
		$to_created = trim($data['to_created']);
		if(empty($from_created)){
			// 開始が設定されていなければ、終了日だけセット
			$conditions[$this->filterArgs['to_created']['field']. ' <='] = $this->setEndDate($to_created, false);
		} else {
			// 両方入っていたら、値の正当性をチェック
			if($this->cmpdate($from_created,$to_created) > 0){
				// 逆になっていたら、from と to を入れ替えて条件をセット
				$conditions[$this->filterArgs['from_created']['field']. ' >='] = $this->setEndDate($to_created, true);
 				$conditions[$this->filterArgs['to_created']['field']. ' <='] = $this->setEndDate($from_created, false);
			} else {
				// 正しければ、 from はセットされているはずなので to だけセット
//				$conditions[$this->filterArgs['from_created']['field']. ' >='] = $this->setEndDate($from_created, true);
				$conditions[$this->filterArgs['to_created']['field']. ' <='] = $this->setEndDate($to_created, false);
			}
		}
		return $conditions;
	}	
	

	/** (使ってないかも)
	 * FromModified検索条件
	 * @param  array  $data 
	 * @return array
	 */
	public function fromModifiedSearch($data = array()) {
		$conditions = array();
		$from_modified = trim($data['from_modified']);

		if (!empty($from_modified)) {
			$conditions[$this->filterArgs['from_modified']['field']. ' >='] = $this->setEndDate($from_modified, true);
		}
		
		return $conditions;
	}

	/** (使ってないかも)
	 * ToModified検索条件
	 * @param  array  $data 
	 * @return array
	 */
	public function toModifiedSearch($data = array()) {
		$conditions = array();
		$from_modified = trim($data['from_modified']);
		$to_modified = trim($data['to_modified']);
		if(empty($from_modified)){
			// 開始が設定されていなければ、終了日だけセット
			$conditions[$this->filterArgs['to_modified']['field']. ' <='] = $this->setEndDate($to_modified, false);
		} else {
			// 両方入っていたら、値の正当性をチェック
			if($this->cmpdate($from_modified,$to_modified) > 0){
				// 逆になっていたら、from と to を入れ替えて条件をセット
				$conditions[$this->filterArgs['from_modified']['field']. ' >='] = $this->setEndDate($to_modified, true);
 				$conditions[$this->filterArgs['to_modified']['field']. ' <='] = $this->setEndDate($from_modified, false);
			} else {
				// 正しければ、 from はセットされているはずなので to だけセット
//				$conditions[$this->filterArgs['from_modified']['field']. ' >='] = $this->setEndDate($from_modified, true);
				$conditions[$this->filterArgs['to_modified']['field']. ' <='] = $this->setEndDate($to_modified, false);
			}
		}
		return $conditions;
	}	


	/**
	 * Dayの最終時間付加
	 * @param string $day
	 * @param boolean $flg true:開始時刻 false:終了時刻 
	 * @return  string 
	 */
	public function setEndDate($day = null, $flg = true) {
		if (empty($day)) return $day;

		if ($flg) {
			return $day. ' 00:00:00';
		}
		return $day. ' 23:59:59';
	}
}
