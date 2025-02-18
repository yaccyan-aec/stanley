<?php
class Db20170306 extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = '';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_field' => array(
				'queues' => array(
					'type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => '変換の種類', 'charset' => 'utf8', 'after' => 'id'),
				),
			),
			'alter_field' => array(
				'queues' => array(
					'is_deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'SoftDelete 使用のため'),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'queues' => array('type',),
			),
			'alter_field' => array(
				'queues' => array(
					'is_deleted' => array('type' => 'string', 'null' => false, 'default' => 'N', 'length' => 3, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
				),
			),
		),
	);

/**
 * Before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 */
	public function after($direction) {
		return true;
	}
}
