<?php
App::uses('AppModel', 'Model');
/**
 * Role Model
 *
 * @property Group $Group
 */
class Role extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		'Group' => array(
			'className' => 'Group',
			'joinTable' => 'groups_roles',
			'foreignKey' => 'role_id',
			'associationForeignKey' => 'group_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => '',
		)
	);
	
	/** ======================================================================================
	 * ある権限をもつグループ群 2012.04.23
	 * ヘルパーにも類似の処理を追加
	 * @param  : $cond = array( '項目名' => '値',
	 * 				：
	 * 				：複合条件があれば記述
	 * );
	 *
	 * @return : $in_ary IN 演算子　
	 * 			'group_id' => $in_ary で使用する
	 * 対応するグループの id => name の array を返す。
	 * ======================================================================================
	 */
	function search($params=array()) {
		$params = Hash::merge(array(
		'Role.is_enabled' => 'Y',
		), $params);
		$found = $this->find('all',array(	'conditions' => $params ));
		return $found;
	}


	/** ======================================================================================
	 * 権限とrole の確認 2012.04.03
	 * ヘルパーにも類似の処理を追加
	 * @param 	$id	： グループID
	 * 			$one：パラメータ（省略時は $this->params をみる）
	 * ======================================================================================
	 */
	function chkRole($gid, $one = null){
		try{
//$this->log(__METHOD__."[".__LINE__."] gid[".$gid."]");
			$_gid = $gid;
			if(empty($gid)) return false;
			if(is_numeric($gid)){
			} else {
//$this->log('id じゃないのでnameで調べる');
				$this->loadModel('Group');
				$_gid = $this->Group->getGID($gid);
			}
			/**
			 * パラメータが null ならデフォルト値を入れる
			 */
			$_p = $one;
			if($one == null){
//$this->log('パラメータ null params はとれなさそう');
				return false;
			}
			/**
			 * 条件設定
			 */
			$_cond = array();
			if(!empty($_p['controller']))	{	$_cond['controller'] = $_p['controller']; }
			if(!empty($_p['action']))		{	$_cond['_action'] = $_p['action'];}
			if(!empty($_p['named']['from'])){	$_cond['named'] = $_p['named']['from'];}
			$this->recursive = 1;

			$ary = $this->search($_cond);
			$_grp  = Hash::combine($ary,'{n}.Group.{n}.id','{n}.Group.{n}.name');
			if(isset($_grp[$_gid])){
				return true;
			}
			return false;

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
			return false;
		}
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

	function getGroupList($one = null,$flg = true){
		/**
		 * パラメータが null ならデフォルト値を入れる
		 */
		if($one == null){
			return array();
		}

		/**
		 * 条件設定
		 */
		$_cond = array();
		if(!empty($one['controller']))	{	$_cond['controller'] = $one['controller']; }
		if(!empty($one['action']))		{	$_cond['_action'] = $one['action'];}
		if(!empty($one['named']['from'])){	$_cond['named'] = $one['named']['from'];}
		$this->recursive = 1;
		$lang = $this->getLang();

		$ary = $this->search($_cond);
		$_grp = array();
		if (!empty($ary)) {
			foreach($ary[0]['Group'] as $k => $v){
				if($v['is_enabled'] == 'Y'){
					// 言語が決まらないときは name を使用
					if(isset($v[$lang])){
						$_grp[$v['id']] = $v[$lang];
					} else {
						$_grp[$v['id']] = $v['name'];
					}
				}
			}
		}
		if(!$flg){	// 上記条件ではないもの
			$allgroup_list = $this->Group->find('list',array('conditions' => array('is_enabled' => 'Y')));
			$groups = array_diff_key ($allgroup_list,$_grp);
			return($groups);
		}
		return($_grp);
	}

	
	/** ======================================================================================
	 * key に対応する value を求める 2012.06.25
	 * ヘルパーにも類似の処理を追加
	 * @param 	$id	： グループID
	 * 			$key： キーワード
	 * @return	$value : 対応する value(無いときは null)
	 * ======================================================================================
	 */
	function getValue($gid, $key = null){
$this->log(__METHOD__."[".__LINE__."]start id[".$gid."] key[".$key."]",LOG_DEBUG);
			try{
				if($key == null){
$this->log(__METHOD__."[".__LINE__."]key == null",LOG_DEBUG);
					return null;
				}

			/**
			 * 条件設定
			 */

			$this->bindModel(array('hasOne' => array('GroupsRole')));
			$ary = $this->find('first',
				array(	'fields' => array(	'Role.v' ),
	      				'conditions'=>array(	'Role.is_enabled' => 'Y',
	      										'Role.k' => $key,
	      										'GroupsRole.group_id' => $gid)));
			$val = null;
			if(isset($ary['Role']))	{
				$val = $this->safe_unserialize($ary['Role']['v']);
			}

			return $val;

		} catch (Exception $e){
$this->log(__METHOD__."[".__LINE__."] try err[".$e."]",LOG_DEBUG);
			return null;
		}
	}


	/* ======================================================================================
	 * DBからfrom リスト作成 2012.06.14
	 * 権限と controller,action から、該当する from の一覧を取り出す
	 * -- 調整中(使ってないかも)
	 * ======================================================================================
	 */
	// DBからグループリスト作成 2011.08.22
	function getFromArray($gid, $one = null, $flg = true){
		if($one == null) return array();

		$this->recursive = 2;
		$roleParm = array('Role.is_enabled' => 'Y');

		if(is_array($one)){
			if(!empty($one['controller']))	{	$roleParm['Role.controller'] = $one['controller']; }
			if(!empty($one['action']))		{	$roleParm['Role._action'] = $one['action'];}
		}
		$roles = $this->find('list',array('conditions'=>$roleParm));

			// パラメータがあったら当該のグループ
			$_one_list = $this->getGroupList($one);

			if($flg){	// 上記条件に合うもの
				$groups = array_intersect_key ($roles,$_one_list);
			} else {	// 上記条件ではないもの
				$groups = array_diff_key ($roles,$_one_list);
			}
		return $groups;
	}

	/* ======================================================================================
	 * スーパー権限か確認
	 * @return bool true / false
	 * ======================================================================================
	 */
	// スーパー権限か確認 2014.04.07
	function isSuper($gid){
		if($gid <= 0) return false;
		$_is_super = $this->chkRole($gid,
				array(	'controller' => 'users',
						'action' => 'notDelete'));

		return $_is_super;
	}

	/* ======================================================================================
	 * ナビ権限取得
	 * @param  int group_id
	 * @return array[] 
	 * ======================================================================================
	 */
	// ナビ権限取得 2014.04.07
	function getNavi($gid){
		if($gid <= 0) return array();
		$this->recursive = -1;
		// 使えるナビの種類を取得
		$roles = $this->find('list',
				array(
				'fields' => array('_action', 'id'),
				'conditions'=> 	array ('controller' => 'navi',
										'is_enabled' => 'Y')));

		// この権限で使えるナビを取得
		$this->bindModel(array('hasOne' => array('GroupsRole')));
		$this->recursive = 0;
		$_ary = $this->search(array(
						'GroupsRole.group_id' => $gid,
						'controller' => 'navi'));
		// 戻り値のリストの初期設定
		$rtn = array_fill_keys(array_keys($roles),false);

		// 使えるナビを true にする
		foreach($_ary as $value){
			$rtn[$value['Role']['_action']] = true;
		}
		return $rtn;
	}

	/** ======================================================================================
	 * 自分と同等か、より偉いgid を求める 2016.04.26
	 * ヘルパーにも類似の処理を追加
	 * @param 	$id	： グループID
	 * 			$one： 条件
	 * @return	$value : 対応する value(無いときは null)
	 * ======================================================================================
	 */
	// 自分のボス権限取得 2016.04.26
	function getBossGroups($gid, $one = array(),$operator = ' >='){
		$bossList = array();
		try{
			// グループIDがエラーだったら終了
			if(!$this->Group->exists($gid)) return array();
			$myGroup = $this->Group->find('first',
				array(	'conditions' => array('id' => $gid),
						'recursive' => -1));
			// 立場の重み			
			$myOrder = $myGroup['Group']['sortorder'];
			/**
			 * 条件設定
			 */
			 
			$_cond = array();
			if(!empty($one['controller']))	{	$_cond['controller'] = $one['controller']; }
			if(!empty($one['action']))		{	$_cond['_action'] = $one['action'];}
			if(!empty($one['named']['from'])){	$_cond['named'] = $one['named']['from'];}
			$_cond['is_enabled'] = 'Y';
			
			// 必要な情報だけに絞り込む
			$contain = array(
					'Group' => array(
						'id','sortorder'
					)
				);

			// 当該の条件にあてはまる権限を取得
			$roles = $this->find('all',
					array(
						'conditions'=> $_cond,
						'contain' => $contain,
						'recursive' => false
					));
			// リスト化
			$glist = Hash::combine($roles,'{n}.Group.{n}.sortorder','{n}.Group.{n}.id');
			// 入力権限より偉いものだけ
			$bossList = $this->Group->find('list',
				array(	'conditions' => array('id' => $glist,
											'sortorder '.$operator => $myOrder),
						'fields' => array('sortorder','id'),					
						'recursive' => -1
					));
					
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $bossList;
	}
	
}
