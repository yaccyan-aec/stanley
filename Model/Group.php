<?php
App::uses('AppModel', 'Model');
/**
 * Group Model
 *
 * @property Role $Role
 */
class Group extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';


/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		'Role' => array(
			'className' => 'Role',
			'joinTable' => 'groups_roles',
			'foreignKey' => 'group_id',
			'associationForeignKey' => 'role_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
			'deleteQuery' => '',
			'insertQuery' => ''
		)
	);


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
	function getGroupArray($one = null,$flg = true ){
		try{
			$_p = $one;
			if($one == null){
			// これとれないかも
//				$_p = $this->_controller->params;
				return array();
			}
			$_fname = $this->makeFieldName('.name','.');
			$groupParm['fields'] = array($this->name.$_fname);
			$groupParm['conditions'] = array('Group.is_enabled' => 'Y');
			$groupParm['order'] = array('Group.sortorder');
//$debug($groupParm);
			
			// 登録されているグループ
			$this->recursive = -1;

			$groups = $this->find('list',$groupParm);
			if($one != null){
				// パラメータがあったら当該のグループ
				$this->Role = ClassRegistry::init('Role');
				$_one_list = $this->Role->getGroupList($_p);
				if($flg){	// 上記条件に合うもの
					$groups = array_intersect_key ($groups,$_one_list);
				} else {	// 上記条件ではないもの
					$groups = array_diff_key ($groups,$_one_list);
				}
			}

			return $groups;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
			return array();
		}
	}

/**
 * is_super
 * @todo スーパーユーザかどうか
 * @param    array $me : ログインユーザ    
 * @return   bool : true / false
 */
	function is_super($gid = null){
		try{
			$this->loadModel('Role');
			return($this->Role->chkRole($gid,
				array('controller' => 'navi',
						'action' => 'all')));

		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	
/**
 * getGroupList
 * @todo ユーザ編集に使用するグループリスト取得（管理者の場合は非表示権限を出さない）
 * @param    array $me : ログインユーザ    
 * @return   array #newdata : グループリスト
 */
	function getGroupList($me = null,$is_shared = false){
		try{
			$this->recursive = -1;
			$conditions = array();
			$list = array();
			if($is_shared){
//debug('--1');
				// shared 版は、ゲストは出さない
				$list = $this->getGroupArray(array('controller' => 'contents', 'action' => 'add', 'named' => array('from' => 'new')));
			} else {
//debug('--2');
				// shared でなければゲストも出す
				$conditions = array('Group.is_enabled' => 'Y');
				$list = $this->find('list',
							array('fields' => array('id',$this->getLang()),
									'order' => 'Group.sortorder asc',
									'conditions' => $conditions,
							));
			}
//debug($list);			
			if($this->is_super($me['group_id'])){
							
//debug('super');						
			} else {
				$list = Hash::remove($list,1);			
//debug('admin');			
			}
			return $list;
						
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return array();
	}
/**
 * getGID
 * @todo ユーザ編集に使用するグループリスト取得（管理者の場合は非表示権限を出さない）
 * @param    mix $word : name , name_jpn, name_eng, abbreviation のどれか    
 * @return   int $group_id : グループID
 */
	function getGID($word = null){
		try{
			$this->recursive = -1;
			if(is_numeric($word)) return $word;

			$group = $this->findByName($word);
//			debug($group);
			if(isset($group[$this->name]['id'])) return $group[$this->name]['id'];

			$group = $this->findByAbbreviation($word);
//			debug($group);
			if(isset($group[$this->name]['id'])) return $group[$this->name]['id'];
			
			$group = $this->findByJpn($word);
//			debug($group);
			if(isset($group[$this->name]['id'])) return $group[$this->name]['id'];

			$group = $this->findByEng($word);
//			debug($group);
			if(isset($group[$this->name]['id'])) return $group[$this->name]['id'];
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return 0;
	}
	
}
