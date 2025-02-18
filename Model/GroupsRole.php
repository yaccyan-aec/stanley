<?php
class GroupsRole extends AppModel {
	public $name = 'GroupsRole';

	public $belongsTo = array(
		'Group' => array(
			'className' => 'Group',
			'foreignKey' => 'group_id',
		),
		
		'Role' => array(
			'className' => 'Role',
			'foreignKey' => 'role_id',
		),
	);
}
