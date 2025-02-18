<?php
class Db20170131 extends CakeMigration {

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
				'addressgroups' => array(
					'contract_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => '共通アドレス帳 2012.01'),
				),
				'contents' => array(
					'opt_avs' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => 'アンチウィルスオプション', 'charset' => 'utf8'),
					'opt_encryption' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => '暗号化オプション', 'charset' => 'utf8'),
					'opt_tfg' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => 'TFG 暗号化', 'charset' => 'utf8'),
					'tfg_type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => 'アップロード時TFGタイプ', 'charset' => 'utf8'),
					'opt_pscan' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'pscan オプション', 'charset' => 'utf8'),
				),
				'uploadresults' => array(
					'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
					'contract_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
				),
				'users' => array(
					'lockout_expdate' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'add 2010/12/04'),
				),
			),
//			'create_field' => array(
//				'approvals' => array(
//					'token' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8', 'after' => 'etc'),
//				),
//				'uploadresults' => array(
//					'group_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'after' => 'user_id'),
//				),
//			),
		),
		'down' => array(
			'alter_field' => array(
				'addressgroups' => array(
					'contract_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '共通アドレス帳 2012.01'),
				),
				'contents' => array(
					'opt_avs' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'アンチウィルスオプション', 'charset' => 'utf8'),
					'opt_encryption' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '暗号化オプション', 'charset' => 'utf8'),
					'opt_tfg' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'TFG 暗号化', 'charset' => 'utf8'),
					'tfg_type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'アップロード時TFGタイプ', 'charset' => 'utf8'),
					'opt_pscan' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'pscan オプション', 'charset' => 'utf8'),
				),
				'uploadresults' => array(
					'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
					'contract_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
				),
				'users' => array(
					'lockout_expdate' => array('type' => 'date', 'null' => true, 'default' => null, 'comment' => 'add 2010/12/04'),
				),
			),
//			'drop_field' => array(
//				'approvals' => array('token',),
//				'uploadresults' => array('group_id',),
//			),
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
