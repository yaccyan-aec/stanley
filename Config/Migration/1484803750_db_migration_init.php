<?php
class DbMigrationInit extends CakeMigration {

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
		),
		'down' => array(
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
    if ($direction === 'up') {
      $dbuser = VALUE_default_login;    //ユーザ名
      $dbpass = VALUE_default_password; //パスワード
      $db = VALUE_default_database;     //DB名
      $file = APP. 'Config'. DS. 'Schema'. DS. 'fts_init.sql'; //初期データSQL

      $cmd = 'mysql --user='.$dbuser.' --password='.$dbpass. ' '. $db. ' < "'.$file.'"';
      exec($cmd ,$out ,$retval);
    }

		return true;
	}
}
