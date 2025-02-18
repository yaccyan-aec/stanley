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
	 * DB����O���[�v���X�g�쐬 2011.08.22
	 * 2012.03.22 app_controller �Ɉړ�
	 * 2012.04.03 DB��ǂނ悤�ɕύX
	 * 2012.04.12 DB����\�L����荞�ނ悤�ɕύX
	 * 2012.05.09 �p�����[�^��ǉ�
	 * $one = array() : null ���܂܂łǂ��� / array() �����ɂ��������X�g
	 * $flg = bool    : true = $one �̏����ɂ��������� / false : $one �̏����ȊO�̂���
	 * �w���p�[�ɂ�����܂����B
	 * ======================================================================================
	 */
	// DB����O���[�v���X�g�쐬 2011.08.22
	function getGroupArray($one = null,$flg = true ){
		try{
			$_p = $one;
			if($one == null){
			// ����Ƃ�Ȃ�����
//				$_p = $this->_controller->params;
				return array();
			}
			$_fname = $this->makeFieldName('.name','.');
			$groupParm['fields'] = array($this->name.$_fname);
			$groupParm['conditions'] = array('Group.is_enabled' => 'Y');
			$groupParm['order'] = array('Group.sortorder');
//$debug($groupParm);
			
			// �o�^����Ă���O���[�v
			$this->recursive = -1;

			$groups = $this->find('list',$groupParm);
			if($one != null){
				// �p�����[�^���������瓖�Y�̃O���[�v
				$this->Role = ClassRegistry::init('Role');
				$_one_list = $this->Role->getGroupList($_p);
				if($flg){	// ��L�����ɍ�������
					$groups = array_intersect_key ($groups,$_one_list);
				} else {	// ��L�����ł͂Ȃ�����
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
 * @todo �X�[�p�[���[�U���ǂ���
 * @param    array $me : ���O�C�����[�U    
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
 * @todo ���[�U�ҏW�Ɏg�p����O���[�v���X�g�擾�i�Ǘ��҂̏ꍇ�͔�\���������o���Ȃ��j
 * @param    array $me : ���O�C�����[�U    
 * @return   array #newdata : �O���[�v���X�g
 */
	function getGroupList($me = null,$is_shared = false){
		try{
			$this->recursive = -1;
			$conditions = array();
			$list = array();
			if($is_shared){
//debug('--1');
				// shared �ł́A�Q�X�g�͏o���Ȃ�
				$list = $this->getGroupArray(array('controller' => 'contents', 'action' => 'add', 'named' => array('from' => 'new')));
			} else {
//debug('--2');
				// shared �łȂ���΃Q�X�g���o��
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
 * @todo ���[�U�ҏW�Ɏg�p����O���[�v���X�g�擾�i�Ǘ��҂̏ꍇ�͔�\���������o���Ȃ��j
 * @param    mix $word : name , name_jpn, name_eng, abbreviation �̂ǂꂩ    
 * @return   int $group_id : �O���[�vID
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
