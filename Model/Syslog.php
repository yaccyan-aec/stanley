<?php
App::uses('AppModel', 'Model');
/**
 * Syslog Model
 *
 * @property User $User
 * @property Contract $Contract
 * @property Content $Content
 * @property Status $Status
 * @property Uploadfile $Uploadfile
 */
class Syslog extends AppModel {


	//The Associations below have been created with all possible keys, those that are not needed can be removed
	/**
	* $order find時のデフォルトソート順
	*/
	var $order = array("Syslog.created" => "desc");
	public $actsAs = array('Common');

	/** 明示的に書くならパラメータを受け渡さないとテストのとき困る
	function __construct($id = false, $table = null, $ds = null){
		parent::__construct($id,$table,$ds);
	}
	*/

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Contract' => array(
			'className' => 'Contract',
			'foreignKey' => 'contract_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Content' => array(
			'className' => 'Content',
			'foreignKey' => 'content_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Status' => array(
			'className' => 'Status',
			'foreignKey' => 'status_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Uploadfile' => array(
			'className' => 'Uploadfile',
			'foreignKey' => 'uploadfile_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	/**
	* $myAgent ユーザ情報
	*/
	var $myAgent;
	/**
	* function _getRemote
	* リモートサーバを調べる
	* 時間がかかるため、セッションに保存し、ないときだけ
	* 改めて取得する
	*
	* @return boolean $remote セッションの書き込み可否
	*/
	function _getRemote(){
		$remote = CakeSession::read('remote');
		if(empty($remote)){
			$remote = array();
			$remote['addr'] = $this->myAgent['server']['REMOTE_ADDR'];
			$_remote['addr'] = long2ip($remote['addr']);
			$remote['host'] = gethostbyaddr($_remote['addr']);
			$remote = CakeSession::write('remote',$remote);
		} else if($remote['addr'] != $this->myAgent['server']['REMOTE_ADDR']){
			$remote['host']= gethostbyaddr($remote['addr']);
			$remote = CakeSession::write('remote',$remote);
		}
		return $remote;
	}


	/**
	* function cleanup
	* cleanup shell から呼ばれるので
	* ここにないとダメ
	*
	*/
	function cleanup(){
		$new = array(	'created' => null,
						'event_action'  => 'クリーンアップ',
						'result'  => '完了',
		);
debug($new);
		$this->User = ClassRegistry::init('User');
		$this->User->recursive = -1;
		$dmyuser = $this->User->findById(1);
debug($dmyuser);
		$this->insertLog($new, $dmyuser['User']);
		return;
	}

	/**
	* function insertLog
	* writeLog から呼ばれる
	*
	* @param array $args 登録内容
	* @param array $me ユーザ情報
	* 
	*/
	function insertLog($args, $me = null){
		// サーバを調べる
/* いま見ているDB dbconfig を調べる(for debug)
	$db = ConnectionManager::getDataSource($this->useDbConfig);
	debug($db->config['database']);
*/
		$remote = $this->_getRemote();

		// パラメータ設定
		$new = array();
		foreach($args as $key => $val){
			if(!is_array($val)){
				$new[$key] = $val;
			}
		}
		// 共通
		$new['created'] = null;
		$new['referer'] = $this->myAgent['server']['HTTP_REFERER'];
		$new['useragent'] = $this->myAgent['server']['HTTP_USER_AGENT'];
		$new['remoteaddr'] = $remote['addr'];

		$new['remotehost'] = $remote['host'];
		$new['uri'] = $this->myAgent['server']['REQUEST_URI'];
		$new['lang'] = $this->getLang();		// 言語モード
//		$new['lang'] = Configure::read('Config.language');		// 言語モード

		// ここで uri を元に必要な情報を書き出すプロセスを振り分けるとよさそう
/**	これで日付が変わるごとに別ファイルに出る。	
$date = new DateTime();
$this->log($date,'syslog_'.$date->format('Ymd'));		
*/
		$_user = $me;
		if($_user == null ){
			if (empty($args['user'])) { $args['user'] = null;}
			$_user = (empty($args['user'][0])) ? $args['user'] : $args['user'][0];
		}

		if(!empty($_user)){
			$new['login_id']	= $_user['email'];
			$new['user_id']		= $_user['id'];
			$new['user_name']	= $_user['name'];
			$new['user_division']	= $_user['division'];
			$new['user_group']	= $_user['group_id'];
			if(empty($new['contract_id'])){
				$new['contract_id']	= $_user['contract_id'];
			}
		}

		if(!empty($args['content'])){
			$_content = $args['content']['Content'];
			$new['content_id'] = $_content['id'];
			$new['content_title'] = $_content['title'];

			$_owner   = $args['content']['Owner'];
			$new['contract_id'] = $_owner['contract_id'];

			if(!empty($args['content']['Status'])){
				$_status  = $args['content']['Status'];
			} elseif (!empty($args['content']['Content']['Status'])){
				$_status  = $args['content']['Content']['Status'];
			}
				if(isset($_status)){
				$new['statuses'] = serialize($_status);
			}

			if(!empty($args['content']['Uploadfile'])){
				$_upload  = $args['content']['Uploadfile'];
			} elseif (!empty($args['content']['Content']['Uploadfile']))	{
				$_upload  = $args['content']['Content']['Uploadfile'];
			}
			if(isset($_upload)){
				$new['uploadfiles'] = serialize($_upload);
			}
		} else {
			// $_upload  = $args['uploadfiles'];
			if(isset($args['uploadfiles'])){
				$_upload  = $args['uploadfiles'];
				$new['uploadfiles'] = serialize($_upload);
			}

			// $_status  = $args['statuses'];
			if(isset($args['statuses'])){
				$_status  = $args['statuses'];
				$new['statuses'] = serialize($_status);
			}
		}

		$_target = ""; if( !empty($args['target_user']) )	$_target = $args['target_user'];
		if(!is_array($_target)){
			$new['target_user'] = $_target;
		} else {
			$new['target_user'] = serialize($_target);

		}

		$_etc = ""; if( !empty($args['etc']) )	$_etc = $args['etc'];
		if(!is_array($_etc)){
			$new['etc'] = $_etc;
		} else {
			$new['etc'] = serialize($_etc);
		}
		$new['level'] = VERSION;

		$_sv = $this->create($new);
		$rc = $this->save($_sv);

		return $rc ;
	}

	function mkcsv($p) {
debug('mkcsv');
debug($p['data']);
		$term = $this->getTerm($p['data']['Syslog'][0]['created'],$p['data']['Syslog'][1]['created']);
debug($term);
		$this->recursive = -1;
		$cond = array( 'conditions' => array(
			"$this->name.created >=" => $term['start'] ,
			"$this->name.created < " => $term['end'],
		) );

		$syslogsSource = $this->find('all', $cond);
//debug($syslogsSource);

		return $syslogsSource;
// ビヘイビアを呼ぶとき
//		$rc = $this->cmpdate("20140301","20140201");
	}


}
