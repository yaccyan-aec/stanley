<?php
class AddMailServer extends CakeMigration {

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
				'user_extensions' => array(
					'server_flg' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'メールサーバーフラグ171024追加', 'after' => 'tmppassword_id'),
					'server_password' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'サーバーパスワード171024追加', 'charset' => 'utf8', 'after' => 'server_flg'),
					'server_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'サーバー名171024追加', 'charset' => 'utf8', 'after' => 'server_password'),
					'port' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'ポート171024追加', 'charset' => 'utf8', 'after' => 'server_name'),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'user_extensions' => array('server_flg', 'server_password', 'server_name', 'port',),
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
