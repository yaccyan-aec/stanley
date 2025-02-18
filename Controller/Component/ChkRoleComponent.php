<?php
App::uses('MyComponent' , 'Controller/Component');
class ChkRoleComponent extends MyComponent {
/**
 * 権限にチェック用コンポーネント
 *   Role コンポーネントにしたかったが、model 名とかぶるため、ChkRole に変更
 * @name ChkRoleComponent
 * @todo 権限にチェック用コンポーネント
 *
 * @notice
 *
 *         送信案内メールとは、
 *         新規ファイル送信画面で[この内容で送信]ボタン押下後、
 *         宛先に設定したメールアドレスユーザに送信されるメールのことです。
 *
 *         このコンポーネントは、
 *         ContentコントローラにあったRole のメソッドを移植したものです。
 *        --------------------------------------------------------------------------
 *         1)コール元のコントローラで
 *           appコントローラに定義してある下記変数に値を設定していること
 *             params
 *             _mylang
 *        --------------------------------------------------------------------------
 *
 * @access public
 * @author ohishi
 * @copyright Copyright (c) 2012-, Asahi Engineering Co,. Ltd.
 * @since 2012.06 -
 *
 */
	var $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;	// controllerの変数を使いたいので。
	}
	
	
	/** ======================================================================================
	 * 権限とrole の確認 2012.04.03
	 * ヘルパーにも類似の処理を追加
	 * @param 	$id	： グループID
	 * 			$one：パラメータ（省略時は $this->params をみる）
	 * ======================================================================================
	 */
	function _chkRole($gid, $one = null){
			try{
			/**
			 * パラメータが null ならデフォルト値を入れる
			 */
			$_p = $one;
			if($one == null){
//$this->log(__METHOD__."[".__LINE__."]param set default",LOG_DEBUG);
				$_p = $this->_controller->params;
			}

			/**
			 * 条件設定
			 */
			$_cond = array();
			if(!empty($_p['controller']))	{	$_cond['controller'] = $_p['controller']; }
			if(!empty($_p['action']))		{	$_cond['_action'] = $_p['action'];}
			if(!empty($_p['named']['from'])){	$_cond['named'] = $_p['named']['from'];}

			$this->loadModel('Role');

			$this->Role->recursive = 1;

			$ary = $this->Role->search($_cond);

//			$_grp  = set::combine($ary,'/Group/./id','/Group/./name');
			$_grp  = Hash::combine($ary,'{n}.Group.{n}.id','{n}.Group.{n}.name');

			if(isset($_grp[$gid])){
				return true;
			}
			return false;

		} catch (Exception $e){
$this->log(__METHOD__."[".__LINE__."] try err[".$e."]",LOG_DEBUG);
			return false;
		}
	}

	function chkRole($gid, $one = null){
		$this->loadModel('Role');
		return $this->Role->chkRole($gid,$one);
	}
	/** ======================================================================================
	 * ある権限をもつグループ群 2012.04.23
	 * ヘルパーにも類似の処理を追加
	 * @param  : $cond = array( '項目名' => '値',
	 * 				：
	 * 				：複合条件があれば記述
	 * );
	 * @param  : $flg  = true: 条件に合うもの（デフォルト）　/　false: 条件に合わないもの
	 *
	 * @return : $in_ary IN 演算子　
	 * 			'group_id' => $in_ary で使用する
	 * 対応するグループの id => name の array を返す。
	 * ======================================================================================
	 */

	function _getGroupList($one = null,$flg = true){
		/**
		 * パラメータが null ならデフォルト値を入れる
		 */
		if($one == null){
			return array();
//			$_p = $this->_controller->params;
		}

		/**
		 * 条件設定
		 */
		$_cond = array();
		if(!empty($one['controller']))	{	$_cond['controller'] = $one['controller']; }
		if(!empty($one['action']))		{	$_cond['_action'] = $one['action'];}
		if(!empty($one['named']['from'])){	$_cond['named'] = $one['named']['from'];}

		$this->loadModel('Role');
		$this->Role->recursive = 1;

		$ary = $this->Role->search($_cond);
//		$_grp  = set::combine($ary,'/Group/./id','/Group/./name');
		$_grp  = Hash::combine($ary,'{n}.Group.{n}.id','{n}.Group.{n}.name');

		if(!$flg){	// 上記条件ではないもの
			$allgroup_list = $this->Group->find('list',array('conditions' => array('is_enabled' => 'Y')));
			$groups = array_diff_key ($allgroup_list,$_grp);
			return($groups);
		}
		return($_grp);
	}
	
	function getGroupList($one = null,$flg = true){
		$this->loadModel('Role');
		return $this->Role->getGroupList($one,$flg);
	}

	/* ======================================================================================
	 * DBからグループリスト作成 2011.08.22
	 * 2012.03.22 app_controller に移動
	 * 2012.04.03 DBを読むように変更
	 * 2012.04.12 DBから表記も取り込むように変更
	 * 2012.05.09 パラメータを追加
	 * $one = array() : null いままでどおり / array() 条件にあったリスト
	 * $flg = bool    : true = $one の条件にあったもの / false : $one の条件以外のもの
	 * ヘルパーにもいれました。
	 * ======================================================================================
	 */
	// DBからグループリスト作成 2011.08.22
	function _getGroupArray($one = null,$flg = true ){
//$this->log(__METHOD__."[".__LINE__."]start",LOG_DEBUG);
//$this->log($one,LOG_DEBUG);
		$_p = $one;
		if($one == null){
			$_p = $this->_controller->params;
		}
		$_mylang = $this->_controller->getLang();
//		$_mylang = Configure::read('Config.language');
		$groupParm['fields'] = array('Group.'.$_mylang);
		$groupParm['conditions'] = array('Group.is_enabled' => 'Y');
		$groupParm['order'] = array('Group.sortorder');

		// 登録されているグループ
		$this->loadModel('Group');
		$this->Group->recursive = -1;

		$groups = $this->Group->find('list',$groupParm);
		if($one != null){
			// パラメータがあったら当該のグループ
			$_one_list = $this->_getGroupList($_p);
//			$this->log($_one_list,LOG_DEBUG);
			if($flg){	// 上記条件に合うもの
				$groups = array_intersect_key ($groups,$_one_list);
			} else {	// 上記条件ではないもの
				$groups = array_diff_key ($groups,$_one_list);
			}
		}
//$this->log($groups,LOG_DEBUG);

		return $groups;
	}

	function getGroupArray($one = null , $flg = true ){
$this->log('--- getGroupArray',LOG_DEBUG);
		$this->loadModel('Group');
$this->log($this->Group);
		return $this->Group->getGroupArray($one, $flg);	
	}
	/** ======================================================================================
	 * key に対応する value を求める 2012.06.25
	 * ヘルパーにも類似の処理を追加
	 * @param 	$id	： グループID
	 * 			$key： キーワード
	 * @return	$value : 対応する value(無いときは null)
	 * ======================================================================================
	 */
	function _getValue($gid, $key = null){
$this->log(__METHOD__."[".__LINE__."]start id[".$gid."] key[".$key."]",LOG_DEBUG);
			try{
				if($key == null){
$this->log(__METHOD__."[".__LINE__."]key == null",LOG_DEBUG);
					return null;
				}

			/**
			 * 条件設定
			 */
			$this->loadModel('Role');

			$this->Role->bindModel(array('hasOne' => array('GroupsRole')));
			$ary = $this->Role->find('first',
				array(	'fields' => array(	'Role.value' ),
	      				'conditions'=>array(	'Role.is_enabled' => 'Y',
	      										'Role.k' => $key,
	      										'GroupsRole.group_id' => $gid)));

//$this->log($ary,LOG_DEBUG);

			if(isset($ary['Role']))	{
				$val = $this->safe_unserialize($ary['Role']['value']);
			}

			return $val;

		} catch (Exception $e){
$this->log(__METHOD__."[".__LINE__."] try err[".$e."]",LOG_DEBUG);
			return null;
		}
	}
	
	function getValue($gid, $key = null){
		$this->loadModel('Role');
		return $this->Role->getValue($gid, $key);
	}


}

