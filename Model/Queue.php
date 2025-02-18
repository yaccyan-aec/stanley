<?php
App::uses('AppModel', 'Model');
/**
 * Queue Model
 *
 * @property User $User
 * @property Contract $Contract
 * @property Content $Content
 * @property Status $Status
 * @property Uploadfile $Uploadfile
 */
class Queue extends AppModel {


	var $name = 'Queue';
	var $actsAs = array(
		'Search.Searchable',
		'Containable',
		'SoftDelete' => array(
			'is_deleted' => 'deleted',
		));

	/** 明示的に書くならパラメータを受け渡さないとテストのとき困る
	function __construct($id = false, $table = null, $ds = null){
		parent::__construct($id,$table,$ds);
	}
	*/

	var $validate = array(
		'content_id' => array('numeric'),
	);
	var $order = array(	"Queue.modified" => "asc");
/**
 * belongsTo associations
 *
 * @var array
 */
	var $belongsTo = array(
		'Content' => array(
			'className' => 'Content',
			'foreignKey' => 'content_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),

		'Approval' => array(
			'className' => 'Approval',
			'foreignKey' => 'approval_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)

	);
	
	
/**
 * putQueue associations
 * 
 * @params $status : ステータス
 * @params $type : 'TFG' / 'PSCAN'
 * @params $cid : content_id
 * @params $aid : approval_id (NULL = 承認なし)
 * @var array
 */
	public function putQueue($status, $type, $cid){
		$lastid = 0;
		try{
			$this->recursive = -1;
			$newdata = $this->create();
			$newdata[$this->name]['type'] = $type;
			$newdata[$this->name]['status_code'] = $status;
			$newdata[$this->name]['content_id'] = $cid;
			$this->loadModel('Content');
			$this->Content->recursive = 0;
			$this->Content->bindModel(array(
				'belongsTo' => array(
					'Reserve' => array(
						'className' => 'Reserve',
						'foreignKey' => 'reserve_id',
						'conditions' => '',
						'fields' => '',
						'order' => ''
					
				))
			));
			$cdata = $this->Content->findById($cid);
$this->log('#### put queue['.$cid.']');
//$this->log($cdata);	
			// 承認を求めているとき
			if(Hash::check($cdata,'Reserve.rsv_data.Approval.aprv_req_user_id')){
				$newdata[$this->name]['approval_id'] = Hash::get($cdata,'Reserve.rsv_data.Approval.aprv_req_user_id');
			}
			
			if($this->save($newdata)){
				$lastid = $this->getLastInsertID();
			}
//$this->log('putqueue rc['.$rc.']');			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $lastid;
	}
 
 /**
 * queue_get_from_cid 
 * content_id から該当 queue を求める
 * @params $cid : content_id
 * @var array
 */
	function queue_get_from_cid($cid = null){
		try{
			$this->recursive = -1;
			if($cid == null){
				return null;
			} else {
				$_cond = array( 'conditions' => 
									array(	'Queue.content_id' => $cid,
									)
								);
				$_count = $this->find('count',$_cond);
				if($_count > 0){
					$qdata = $this->find('first',$_cond);
				} else { // 待ちキューが無いときは null を返す
					$qdata = null;
				}
				return $qdata;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return null;
	}

 /**
 * queue_get
 * content_id から該当 queue を求める
 * @params $type : 'TFG' / 'PSCAN'
 * @params $stt : ステータス
 * @var array
 */
	function queue_get($type,$stt,$recursive = -1){
		try{
$this->log('queue_get type['.$type.']');			
			$this->recursive = $recursive;
			$_cond = array( 'conditions' => 
								array(	'Queue.status_code' => $stt,
										'Queue.type' => $type,
								)
							);
//debug($_cond);			
			$_count = $this->find('count',$_cond);
$this->log('count['.$_count.']');			
			if($_count > 0){
				$qdata = $this->find('first',$_cond);
			} else { // 待ちキューが無いときは null を返す
				$qdata = null;
			}
			return $qdata;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return null;
	}
	
	/**
	 * setStatus
	 * @todo 	status を設定する
	 * @param   int  $id
	 * @param   int  $code : status
	 * @return  bool $rtn : 結果
	 */
	function setStatus($id = null, $code = 0){
		try{
$this->log('queue setStatus start qid['.$id.'] code['.$code.']');
			if($id == null) return false;
			parent::save(array(
						'id' => $id,
						'status_code' => $code,
						'modified' => null,
						));
			return true;			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 * getRetryCount
	 * @todo 	status を設定する
	 * @param   int  $id
	 * @param   int  $code : status
	 * @return  bool $rtn : 結果
	 */
	function getRetryCount($id = null){
		try{
$this->log('queue setStatus start qid['.$id.'] ');
			if($this->exists($id)){
				$data = $this->find('first', array( 
					'conditions' => array(	'Queue.id' => $id ),
					'recursive' => -1
					));
				return($data['Queue']['retry']);	
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return 0;
	}

	/**
	 * setRetryCount
	 * @todo 	retry を設定する
	 * @param   int  $id
	 * @param   int  $code : status
	 * @return  bool $rtn : 結果
	 */
	function setRetryCount($id = null, $count = 0){
		try{
$this->log('queue setRetryCount start qid['.$id.'] count['.$count.']');
			if($id == null) return false;
			parent::save(array(
						'id' => $id,
						'retry' => $count,
						'modified' => null,
						));
			return true;			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}

	/**
	 * getElapsedTime
	 * @todo 	変換中ステータスの経過時間を調べる
	 * @param   int  $id
	 * @return  time $rtn : 経過時間
	 */
	function getElapsedTime($id = null){
		try{
$this->log('queue getElapsedTime start qid['.$id.'] ');
			$data = $this->find('first',array(
				'conditions' => array('id' => $id),
				'recursive' => -1,
			));
			
			$from = strtotime($data['Queue']['modified']);
			$to = strtotime($this->now());
			$diff = $to - $from;
$this->log($diff);			
$this->log('from['.$from.'] to['.$to.'] diff['.$diff.']');			
			return $diff;			
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return -1;
	}	
}
