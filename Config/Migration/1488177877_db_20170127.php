<?php
class Db20170127 extends CakeMigration {

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
			'alter_field' => array(
				'contents' => array(
					'opt_pscan' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => 'pscan オプション', 'charset' => 'utf8'),
				),
				'queues' => array(
					'data_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'this->data をいれたreserve_id（いらないかも？）'),
				),
				'uploadresults' => array(
					'contract_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => 'add 2016/09/29'),
				),
			),
			'drop_field' => array(
				'queues' => array('recognize_id',),
			),
		),
		'down' => array(
			'alter_field' => array(
				'contents' => array(
					'opt_pscan' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'pscan オプション', 'charset' => 'utf8'),
				),
				'queues' => array(
					'data_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'this->data をいれたreserve_id'),
				),
				'uploadresults' => array(
					'contract_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
				),
			),
			'create_field' => array(
				'queues' => array(
					'recognize_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '将来削除予定'),
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
