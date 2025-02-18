<?php 
class AppSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $addressbooks = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'is_shared' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 3, 'collate' => 'utf8_general_ci', 'comment' => '共有フラグ（2016/6/20）', 'charset' => 'utf8'),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'name_yomi' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'division' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'div_yomi' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'name_jpn' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'name_eng' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'div_jpn' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'div_eng' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'email' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'lang' => array('type' => 'string', 'null' => true, 'default' => 'jpn', 'length' => 5, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'sortorder' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'owner_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '最後に編集したユーザID'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'etc' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_addressbooks_users1' => array('column' => 'user_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $addressbooks_addressgroups = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'addressbook_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'addressgroup_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $addressgroups = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'contract_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => '共通アドレス帳 2012.01'),
		'is_shared' => array('type' => 'string', 'null' => true, 'default' => 'N', 'length' => 3, 'collate' => 'utf8_general_ci', 'comment' => 'Y or N', 'charset' => 'utf8'),
		'parent_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'for tree'),
		'lft' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'for tree'),
		'rght' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'for tree'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 512, 'collate' => 'utf8_general_ci', 'comment' => 'for tree', 'charset' => 'utf8'),
		'name_jpn' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'name_eng' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'name_yomi' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_general_ci', 'comment' => '-> name_jpn に変更予定 2014', 'charset' => 'utf8'),
		'sortorder' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'grp_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'unsigned' => false, 'comment' => 'タイプ'),
		'text_pattern' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '一致パターン', 'charset' => 'utf8'),
		'is_enabled' => array('type' => 'string', 'null' => false, 'default' => 'Y', 'length' => 3, 'collate' => 'utf8_general_ci', 'comment' => '有効フラグ', 'charset' => 'utf8'),
		'is_root' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 3, 'collate' => 'utf8_general_ci', 'comment' => 'root フラグ　2016.01 追加', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'etc' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_addressgroups_users1' => array('column' => 'user_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $approvals = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => 'シリアル番号'),
		'sno' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'comment' => 'シリアル番号', 'charset' => 'utf8'),
		'content_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'コンテンツID'),
		'contract_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '契約ID'),
		'aprv_req_user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '承認依頼者ID（依頼メール宛先）'),
		'aprv_date' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '承認日時'),
		'aprv_user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '承認者ID（実際に承認/却下したユーザ）'),
		'expdate' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '承認期限'),
		'aprv_stat' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 3, 'unsigned' => false, 'comment' => '承認結果'),
		'is_expdate' => array('type' => 'string', 'null' => false, 'default' => 'N', 'length' => 3, 'collate' => 'utf8_general_ci', 'comment' => '期限切れフラグ', 'charset' => 'utf8'),
		'is_deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '削除フラグ 変更2016.4.21'),
		'can_aprv_self' => array('type' => 'string', 'null' => false, 'default' => 'N', 'length' => 3, 'collate' => 'utf8_general_ci', 'comment' => '自己承認可否', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '登録日'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '更新日'),
		'deleted' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '削除日'),
		'message' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'メッセージ', 'charset' => 'utf8'),
		'request_comment' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '承認依頼のコメント', 'charset' => 'utf8'),
		'etc' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '未使用', 'charset' => 'utf8'),
		'token' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM', 'comment' => '承認テーブル名称変更')
	);

	public $contents = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'owner_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'title' => array('type' => 'string', 'null' => false, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'lang' => array('type' => 'string', 'null' => true, 'default' => 'auto', 'length' => 5, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'message' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'expdate' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_expdate' => array('type' => 'string', 'null' => false, 'default' => 'N', 'length' => 3, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'deleted' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'softdeleteBehavior 使用のため変更2014.09'),
		'uploadfile_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'uploadfile_totalsize' => array('type' => 'biginteger', 'null' => false, 'default' => '0', 'length' => 16, 'unsigned' => true),
		'address_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => true),
		'address_conf_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => true),
		'address_conf_rate' => array('type' => 'decimal', 'null' => false, 'default' => '0.000', 'length' => '6,3', 'unsigned' => false),
		'reserve_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'add 2011.06.28'),
		'time_limit' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5, 'unsigned' => false, 'comment' => '有効日数'),
		'approval_limit' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5, 'unsigned' => false, 'comment' => '承認期限 2014.09.15 つづりミス変更2015.11.04'),
		'opt_avs' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => 'アンチウィルスオプション', 'charset' => 'utf8'),
		'opt_encryption' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => '暗号化オプション', 'charset' => 'utf8'),
		'opt_tfg' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => 'TFG 暗号化', 'charset' => 'utf8'),
		'tfg_type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => 'アップロード時TFGタイプ', 'charset' => 'utf8'),
		'opt_pscan' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => 'pscan オプション', 'charset' => 'utf8'),
		'status_code' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'unsigned' => false, 'comment' => '送信ステータス'),
		'checkdate' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'チェック用'),
		'conv_option' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '変換オプション', 'charset' => 'utf8'),
		'bcc' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'bcc 宛先（複数可）', 'charset' => 'utf8'),
		'cc' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'cc 宛先（複数可）', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_contents_users' => array('column' => 'user_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $contracts = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '名称', 'charset' => 'utf8'),
		'name_jpn' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '名称　日本語　add 2014/09/13', 'charset' => 'utf8'),
		'name_eng' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '名称　英語　add 2014/09/13', 'charset' => 'utf8'),
		'logo' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'ロゴ', 'charset' => 'utf8'),
		'uri' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'URI', 'charset' => 'utf8'),
		'theme' => array('type' => 'string', 'null' => true, 'default' => 'default', 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => 'デザインテーマ　add 2014/09/13', 'charset' => 'utf8'),
		'usernum' => array('type' => 'integer', 'null' => false, 'default' => '1', 'unsigned' => false, 'comment' => '人数'),
		'user_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => '現在登録人数 add 2014/09/16'),
		'size' => array('type' => 'integer', 'null' => false, 'default' => '1', 'unsigned' => false, 'comment' => '容量（G）'),
		'expdate' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '有効期限'),
		'time_limit' => array('type' => 'integer', 'null' => false, 'default' => '7', 'length' => 5, 'unsigned' => false, 'comment' => '有効日数デフォルト'),
		'approval_limit' => array('type' => 'integer', 'null' => false, 'default' => '7', 'length' => 5, 'unsigned' => false, 'comment' => '承認期限デフォルト　2014/09/15 つづりミス修正2015/11/04'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'updated' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'ユーザに反映した日 add 2014/09/13'),
		'deleted' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'SoftDeleteBehavior 使用のため　tinyint に変更 2014/09.18'),
		'is_trial' => array('type' => 'string', 'null' => true, 'default' => 'Y', 'length' => 3, 'collate' => 'utf8_general_ci', 'comment' => 'お試し', 'charset' => 'utf8'),
		'sortorder' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'addressgroup_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '契約が持つデフォルトの共通アドレスグループへのリンク'),
		'etc' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $datalogs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => 'auto'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'ログイン中ユーザ'),
		'eventlog_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '対応イベントログID'),
		'content_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '送信ID'),
		'uploadfile_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'ファイルID'),
		'status_code' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 5, 'unsigned' => false, 'comment' => 'アップロードステータス2014.12.18名称変更'),
		'is_copy' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 3, 'unsigned' => false, 'comment' => '0 以上のときはアップロードしてない（2014.12.25追加）'),
		'filename' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'ファイル名', 'charset' => 'utf8'),
		'filetype' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'ファイルタイプ', 'charset' => 'utf8'),
		'filesize' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'ファイルサイズ'),
		'filepath' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'サーバ内のファイル名', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '日時（2015）'),
		'etc' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'id' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM', 'comment' => 'ファイルログ（新）')
	);

	public $errmails = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'sno' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'fname' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'prefix' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'mail_type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'content_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'C'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'W'),
		'contract_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => 'Content->Owner.contract_id'),
		'status_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => 'S'),
		'send' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'deleted' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_checked' => array('type' => 'string', 'null' => false, 'default' => 'N', 'length' => 3, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'is_deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '変更 2016.04.21'),
		'notified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '通知メール送信日付'),
		'is_notified' => array('type' => 'string', 'null' => false, 'default' => 'N', 'length' => 3, 'collate' => 'utf8_general_ci', 'comment' => '通知したかどうか', 'charset' => 'utf8'),
		'loginuri' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => 'add 2011.09', 'charset' => 'utf8'),
		'mail_data' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM', 'comment' => 'エラーメール分析用')
	);

	public $eventlogs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => 'auto'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '日時'),
		'login_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'ログインID', 'charset' => 'utf8'),
		'domain' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'comment' => 'ドメイン', 'charset' => 'utf8'),
		'event_action' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'アクション', 'charset' => 'utf8'),
		'result' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '結果（項目名remark から変更2014.12.25）', 'charset' => 'utf8'),
		'remark' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1024, 'collate' => 'utf8_general_ci', 'comment' => '備考（アクションの追加説明2014.12.25追加）', 'charset' => 'utf8'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'ユーザID'),
		'group_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '権限'),
		'contract_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '契約ID'),
		'content_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '送信ID'),
		'lang' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 5, 'collate' => 'utf8_general_ci', 'comment' => '言語', 'charset' => 'utf8'),
		'referer' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'referer', 'charset' => 'utf8'),
		'useragent' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 2048, 'collate' => 'utf8_general_ci', 'comment' => '使用ブラウザ', 'charset' => 'utf8'),
		'session_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'セッションID（新）', 'charset' => 'utf8'),
		'remoteaddr' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1024, 'collate' => 'utf8_general_ci', 'comment' => 'リモートアドレス', 'charset' => 'utf8'),
		'remotehost' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'リモートホスト', 'charset' => 'utf8'),
		'uri' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'URI', 'charset' => 'utf8'),
		'user_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'ユーザ名', 'charset' => 'utf8'),
		'user_division' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '部門名', 'charset' => 'utf8'),
		'group_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'グループ名', 'charset' => 'utf8'),
		'status_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'ステータスID（アップロードファイルサイズから変更2014.12.22）'),
		'uploadfile_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'ファイルID（ダウンロードファイルサイズから変更2014.12.22）'),
		'version' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'fts バージョン', 'charset' => 'utf8'),
		'target_user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'ターゲットユーザ'),
		'event_data' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '備考', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM', 'comment' => 'イベントログ（新）')
	);

	public $groups = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'jpn' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'comment' => '日本語表記', 'charset' => 'utf8'),
		'eng' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'comment' => '英語表記', 'charset' => 'utf8'),
		'sortorder' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'コンボにするときの並び順'),
		'is_enabled' => array('type' => 'string', 'null' => false, 'default' => 'Y', 'length' => 3, 'collate' => 'utf8_general_ci', 'comment' => '有効/無効フラグ', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => '0000-00-00 00:00:00'),
		'abbreviation' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'collate' => 'utf8_general_ci', 'comment' => '略称', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $groups_roles = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'group_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'role_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM', 'comment' => 'group と role を結ぶリンクテーブル')
	);

	public $histories = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'キーワード', 'charset' => 'utf8'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '記載ユーザ'),
		'title_jpn' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'title_eng' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'details_jpn' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'details_eng' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'version' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'バージョン', 'charset' => 'utf8'),
		'revision' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'collate' => 'utf8_general_ci', 'comment' => 'リビジョン　alpha / beta / stable', 'charset' => 'utf8'),
		'uri' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'release_date' => array('type' => 'date', 'null' => true, 'default' => null, 'comment' => 'リリース日'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'deleted' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_deleted' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
		'redmine' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'redmine チケット番号', 'charset' => 'utf8'),
		'memo' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $information = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'unsigned' => false, 'comment' => 'edit したユーザのID'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'title_jpn' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1024, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'title_eng' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1024, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'data_jpn' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'data_eng' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'putdate' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'outdate' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_put' => array('type' => 'string', 'null' => false, 'default' => 'Y', 'length' => 3, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'importance' => array('type' => 'integer', 'null' => false, 'default' => '1', 'length' => 5, 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $inquiries = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'email' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '必須', 'charset' => 'utf8'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'ユーザが特定できたら入れる'),
		'company' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_general_ci', 'comment' => '必須', 'charset' => 'utf8'),
		'division' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_general_ci', 'comment' => '必須', 'charset' => 'utf8'),
		'phone' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'msg' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '必須', 'charset' => 'utf8'),
		'useragent' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '使用ブラウザ', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'version' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'FTS2　バージョン', 'charset' => 'utf8'),
		'errmail_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '不達メールID　（不達メール問い合わせで使用）'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $maillogs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'sno' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'result' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '結果（2014.12.25追加）', 'charset' => 'utf8'),
		'mail_host' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'メールサーバ（2017.07.04）', 'charset' => 'utf8'),
		'mail_from' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => 'from address', 'charset' => 'utf8'),
		'domain_from' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'comment' => '送信者ドメイン', 'charset' => 'utf8'),
		'mail_to' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => 'to address', 'charset' => 'utf8'),
		'domain_to' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'comment' => '受信者　ドメイン', 'charset' => 'utf8'),
		'eventlog_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'eventlog ID　（新）'),
		'lang' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 5, 'collate' => 'utf8_general_ci', 'comment' => 'メール言語（2012.12.22追加）', 'charset' => 'utf8'),
		'mail_charset' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'collate' => 'utf8_general_ci', 'comment' => 'add 2011.07', 'charset' => 'utf8'),
		'encode' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 32, 'collate' => 'utf8_general_ci', 'comment' => 'add 2011.07', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'user_id_from' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index', 'comment' => '送信者user_id(user_id から変更2015.01)'),
		'user_id_to' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '受信者user_id(2015.01)'),
		'content_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'status_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'is_shell' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 3, 'collate' => 'utf8_general_ci', 'comment' => 'Y バッチで呼ばれた（2014.12.24追加）', 'charset' => 'utf8'),
		'mail_data' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_logs_users1' => array('column' => 'user_id_from', 'unique' => 0),
			'fk_logs_contents1' => array('column' => 'content_id', 'unique' => 0),
			'fk_logs_statuses1' => array('column' => 'status_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $mailqueues = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'sno' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => 'シリアル番号（時間抜き）', 'charset' => 'utf8'),
		'priority' => array('type' => 'integer', 'null' => false, 'default' => '50', 'length' => 5, 'unsigned' => false, 'comment' => '重要度'),
		'template' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'テンプレート名', 'charset' => 'utf8'),
		'mail_from' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '送信アドレス', 'charset' => 'utf8'),
		'mail_to' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '受信アドレス', 'charset' => 'utf8'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => 'jpn', 'length' => 5, 'collate' => 'utf8_general_ci', 'comment' => 'メール言語', 'charset' => 'utf8'),
		'eventlog_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => 'ログID（送信後のログ記載に使用）'),
		'status_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => '宛先StatusのID'),
		'mail_charset' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'comment' => 'charset', 'charset' => 'utf8'),
		'mail_type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => 'text / html', 'charset' => 'utf8'),
		'status_code' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5, 'unsigned' => false, 'comment' => 'ステータス'),
		'result' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => '送信結果', 'charset' => 'utf8'),
		'retry' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5, 'unsigned' => false, 'comment' => 'リトライ回数（使わないかも）'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null, 'comment' => '登録日'),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null, 'comment' => '変更日　2014.08 追加'),
		'deleted' => array('type' => 'datetime', 'null' => false, 'default' => null, 'comment' => '削除日'),
		'is_deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '削除フラグ　tinyint に変更2014.09.29'),
		'mail_data' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'パラメータハッシュ（シリアライズ）', 'charset' => 'utf8'),
		'etc' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '備考', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $queues = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => '変換の種類', 'charset' => 'utf8'),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'approval_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'recoginize_id から変更'),
		'data_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'this->data をいれたreserve_id（いらないかも？）'),
		'status_code' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 3, 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'deleted' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'SoftDelete 使用のため'),
		'retry' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5, 'unsigned' => false, 'comment' => 'リトライカウンタ'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $reserves = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'title' => array('type' => 'string', 'null' => true, 'collate' => 'utf8_general_ci', 'comment' => ' add 2014.06', 'charset' => 'utf8'),
		'rsv_data' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'add 2014.06'),
		'deleted' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'add 2014.09'),
		'is_deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'softdeleteBehavioe 使用のため tinyint に変更　2014.09'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $roles = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'controller' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'_action' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'named' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'k' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'add 2012.04.23', 'charset' => 'utf8'),
		'v' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'add 2012.04.23', 'charset' => 'utf8'),
		'is_enabled' => array('type' => 'string', 'null' => false, 'default' => 'Y', 'length' => 3, 'collate' => 'utf8_general_ci', 'comment' => '有効/無効フラグ', 'charset' => 'utf8'),
		'remark' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '説明', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $schema_migrations = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'class' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $sections = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'contract_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'parent_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index', 'comment' => 'for tree'),
		'lft' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index', 'comment' => 'for tree'),
		'rght' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index', 'comment' => 'for tree'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'for tree', 'charset' => 'utf8'),
		'name_jpn' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'name_eng' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'leader_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '部門長user_id 追加：2016/4/28'),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'deleted' => array('type' => 'datetime', 'null' => false, 'default' => null, 'comment' => '追加2015/12/7'),
		'is_deleted' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '追加2015/12/7'),
		'etc' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'parent_id' => array('column' => 'parent_id', 'unique' => 0),
			'lft' => array('column' => 'lft', 'unique' => 0),
			'fght' => array('column' => 'rght', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $sections_addressgroups = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'section_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'addressgroup_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $sections_users = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'section_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $statuses = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'addressbook_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'downdate' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'email' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'status_code' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 3, 'unsigned' => false, 'comment' => 'ステータスコード '),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'deleted' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_deleted' => array('type' => 'boolean', 'null' => true, 'default' => '0', 'comment' => 'softdeleteBehavior 使用のため変更　2014.09'),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'remark' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_statuses_contents1' => array('column' => 'content_id', 'unique' => 0),
			'fk_statuses_addressbooks1' => array('column' => 'addressbook_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $templates = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'is_shared' => array('type' => 'string', 'null' => false, 'default' => '0', 'length' => 3, 'collate' => 'utf8_general_ci', 'comment' => '個人用/共用の種別', 'charset' => 'utf8'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '個人用のときに使用する'),
		'contract_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '共通のときに使用する'),
		'section_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '共通のときに使用する 2016/2/1'),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 1024, 'collate' => 'utf8_general_ci', 'comment' => 'title から変更2014.09', 'charset' => 'utf8'),
		'template_data' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $tmppasswords = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'email' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'pwd' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'expdate' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'add 2014.10.07'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $uploadfiles = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'fname' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'ファイル名本体（for pscan）', 'charset' => 'utf8'),
		'fext' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'ファイル拡張子（for pscan）', 'charset' => 'utf8'),
		'path' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'mime_type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'size' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'deleted' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_deleted' => array('type' => 'boolean', 'null' => true, 'default' => '0', 'comment' => 'softdeleteBehavior 使用のため変更　2014.09'),
		'dl_mod' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5, 'unsigned' => false, 'comment' => 'ダウンロードに関するフラグ'),
		'dl_cnt' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5, 'unsigned' => false, 'comment' => 'ダウンロードカウンタ'),
		'error' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 5, 'unsigned' => false, 'comment' => 'add 2011.08'),
		'avs_result' => array('type' => 'integer', 'null' => false, 'default' => '-1', 'length' => 5, 'unsigned' => false, 'comment' => 'アンチウィルススキャン結果'),
		'enc_type' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '項目名変更2015.10', 'charset' => 'utf8'),
		'enc_result' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5, 'unsigned' => false, 'comment' => '変換結果：項目名変更　2015.10'),
		'enc_path' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'エンコードファイル名', 'charset' => 'utf8'),
		'enc_size' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => 'エンコードファイルのサイズ'),
		'dec_path' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'デコードファイル名', 'charset' => 'utf8'),
		'dec_size' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => 'デコードファイルのサイズ'),
		'zpass' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '暗号zip pwd　(2014.07)', 'charset' => 'utf8'),
		'work_data' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '内部で使用', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_uploadfiles_contents1' => array('column' => 'content_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $uploadresults = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'group_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'contract_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => 'add 2016/09/29'),
		'size' => array('type' => 'biginteger', 'null' => false, 'default' => '0', 'length' => 16, 'unsigned' => false),
		'oversize' => array('type' => 'biginteger', 'null' => false, 'default' => '0', 'length' => 16, 'unsigned' => false),
		'is_over' => array('type' => 'string', 'null' => false, 'default' => 'N', 'length' => 3, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $user_extensions = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'name_jpn' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'name 日本語', 'charset' => 'utf8'),
		'name_eng' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'name 英語', 'charset' => 'utf8'),
		'div_jpn' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'division 日本語', 'charset' => 'utf8'),
		'div_eng' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'division 英語', 'charset' => 'utf8'),
		'custom_01' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '社員ID', 'charset' => 'utf8'),
		'custom_02' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => '部署名', 'charset' => 'utf8'),
		'text_01' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '追加テキスト　2013.08', 'charset' => 'utf8'),
		'expdate_apply_flg' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5, 'unsigned' => false, 'comment' => '申請フラグ 2014.09'),
		'tmppassword_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '仮パスワードID 2014.09'),
		'server_flg' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'メールサーバーフラグ171024追加'),
		'server_password' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'サーバーパスワード171024追加', 'charset' => 'utf8'),
		'server_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'サーバー名171024追加', 'charset' => 'utf8'),
		'port' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'comment' => 'ポート171024追加', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $users = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'name_yomi' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_general_ci', 'comment' => 'add 2010.8.26', 'charset' => 'utf8'),
		'division' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'div_yomi' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 512, 'collate' => 'utf8_general_ci', 'comment' => 'add 2010.8.26', 'charset' => 'utf8'),
		'pwd' => array('type' => 'string', 'null' => false, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'group_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'email' => array('type' => 'string', 'null' => false, 'length' => 256, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => 'jpn', 'length' => 5, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'contract_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'key' => 'index'),
		'addressgroup_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'アドレスグループ root 2015.9.29'),
		'expdate' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'lastlogin' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'deleted' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'is_deleted' => array('type' => 'boolean', 'null' => false, 'default' => null, 'comment' => 'SoftDeleteBehavior 使用のため　tinyint に変更 2014/09.18'),
		'is_chgpwd' => array('type' => 'string', 'null' => false, 'default' => 'N', 'length' => 3, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'securityoption_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'add 2010/12/04'),
		'lockout_stat' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5, 'unsigned' => false, 'comment' => 'add 2010/12/04'),
		'lockout_expdate' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'add 2010/12/04'),
		'is_chgpwd_demand' => array('type' => 'string', 'null' => false, 'default' => 'N', 'length' => 1, 'collate' => 'utf8_general_ci', 'comment' => 'add 2010/12/29', 'charset' => 'utf8'),
		'pwd_expdate' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'pwd_fail_count' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 5, 'unsigned' => false, 'comment' => 'add 2010/12/04'),
		'pwd_work' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'remark' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'etc' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_users_contracts1' => array('column' => 'contract_id', 'unique' => 0),
			'fk_users_authorities1' => array('column' => 'group_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

}
