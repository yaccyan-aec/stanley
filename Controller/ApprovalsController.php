<?php
App::uses('AppController', 'Controller');
/**
 * Approvals Controller
 *
 * @property Approvals $Approval
 * @property PaginatorComponent $Paginator
 */

define('OPE_APPROVAL_INDEX',1);
define('OPE_APPROVAL_IDXALL',2);
define('OPE_APPROVAL_HISTLST',3);
define('OPE_APPROVAL_HISTLSTALL',4);

class ApprovalsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array(	'SearchPagination.SearchPagination',
								'Search.Prg',
								'Paginator'
								);
//	public $components = array('Paginator');

	public function beforeFilter() {
		parent::beforeFilter();
//		var_dump($this->referer());
	}

/**
 * function index		承認待ち一覧 (部署)
 *
 * @author okamoto
 * @todo 内部メソッド _dispIndex をコールする
 * @access public
 *
 */
	function index() {
		$this->_dispIndex( OPE_APPROVAL_INDEX );
	}
	function index_all() {
		$this->_dispIndex( OPE_APPROVAL_IDXALL );
	}
	function historylist() {
		$this->_dispIndex( OPE_APPROVAL_HISTLST );
	}
	function historylist_all() {
		$this->_dispIndex( OPE_APPROVAL_HISTLSTALL );
	}

/**
 * private function _dispIndex
 *
 * @author okamoto
 * @todo パラメタで渡されたFunctionIDに従い、承認テーブルのレコードをselectし一覧画面を表示する
 *       画面表示の際、一覧はindex.ctpに、履歴一覧はhistorylist.ctpにrenderする
 * @param int $act : FunctionID (本ソースTOPにてdefineで定義してます)
 * @access private
 *
 * 定刻起動バッチにより、期限切れ処理を行うので、有効期限の日つけによる自動判定はなしにする　2016.5.9
 */
	private function _dispIndex( $act ) {
//debug(__FILE__.':'.__LINE__."::Approvals index [".$act."]start!");
		$cond = array();
//debug($cond);
		$aprvMembers = array();
		if(CakePlugin::loaded('Sections')){
			$this->loadModel('Sections.Section');
			$mySecLst = $this->Section->getLowLevel($this->me['id']);
//debug($mySecLst);
			// リーダーになれる権限の人
			$gids = $this->Role->getGroupList(array(
				'controller' => 'sections',
				'action' => 'can_leader'));
//debug($gids);

			$aprvMembers = @$this->Section->getMyAprvUserId($mySecLst,$gids);
			$aprvMembers[] = $this->me['id'];	// 自分が抜けてるかもしれないので入れておく
//debug(count($aprvMembers));

		}

		$this->Prg->commonProcess();
//debug($this->passedArgs);
		$ary = $this->passedArgs;
		if(empty($this->passedArgs)){
//debug('---0');
			$ary = $this->Session->read($this->view);
		}
//debug($ary);
		if(empty($this->passedArgs)){
			if(empty($ary)){
//debug('---1');
				// 初期設定
				$this->passedArgs = array(
					'sort' => 'Content.created',
					'direction' => 'desc',
					'view' => $this->view,
					);

			} else {
//debug('---2 ');
				$this->passedArgs = $ary;
			}
		} else {
//debug('---3 save');
			$this->Session->write($this->view,$this->passedArgs);
		}
		$this->passedArgs['search'] = 'on';
		$this->passedArgs['view'] = $this->view;

		$cond[] = $this->Approval->parseCriteria($this->passedArgs);
		// ユーザのキーワード検索を追加
		// ネストが深いので、id さきにIDリストにする
		$userlist = array();
		$contlist = array();

		$keyword = @Hash::get($this->passedArgs,'keyword');

		if(strlen(trim($keyword)) > 0){
			// キーワードがないときはやらない
			$this->loadModel('Content');
			$conditions = $this->Content->searchKeyword($this->passedArgs);
//debug($conditions);
			// 該当しそうなcontent を絞り込んでおく
			$contlist = $this->Content->find('list',array('conditions' => $conditions));
//debug($contlist);
			if(!empty($contlist)){
				$cond[0]['OR']['Approval.content_id'] = array_keys($contlist);
			}

			// 該当しそうなユーザを絞り込んでおく
			$userlist = $this->User->findUserListByKeyword($this->passedArgs);
			if(!empty($userlist)){
				$cond[0]['OR']['Approval.aprv_req_user_id'] = array_keys($userlist);
				$cond[0]['OR']['Approval.aprv_user_id'] = array_keys($userlist);
			}
		}

		// contain を使用して、必要な情報と深さを絞り込む
		$contain = array(
			'Content' => array(
//				'id','title','is_deleted','created','address_count',
//				'is_expdate','user_id','cc','bcc','status_code',
//				'uploadfile_count','uploadfile_totalsize',
				'User' => array(
					'id','email','name','division',
					'UserExtension' => array(
						'name_jpn','name_eng',
					),
				),
				'Status' => array(
					'id','email','name','user_id','content_id',
					'User' => array(
						'name',
						'UserExtension' => array(
							'name_jpn','name_eng',
						),
					),
				),
			),
			'AprvReqUser' => array(
				'id','email','name','division',
				'UserExtension' => array(
					'name_jpn','name_eng',
				),
			),
			'AprvUser' => array(
				'id','email','name','division',
				'UserExtension' => array(
							'name_jpn','name_eng',
				),
			),

		);

//		recursive => false で、contain に合わせた深さで取得してくれる
		$this->Paginator->settings = array(
											'conditions' => $cond,
											'contain' => $contain,
											'recursive' => false,
											'limit' => PAGE_LIMIT_DEFAULT,
											'order' => array('Approval.modified' => 'desc'));
        $lst = array();
        // ページ数が変わって　not found になったときの対策
		try{
            $lst = $this->Paginator->paginate();
        } catch (Exception $e){
            // ここに来たら当該ページがないということなので、
            // 1 ページにする
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
            $this->request->params['named']['page'] = 1;
            $lst = $this->Paginator->paginate();
        }
//debug($lst);
		$this->set('approvals', $lst );
		// renderの設定
		switch( $act ) {
			case OPE_APPROVAL_INDEX:
			case OPE_APPROVAL_IDXALL:			// 承認待ち一覧 (全体)
				$this->render('index');
				break;
			case OPE_APPROVAL_HISTLST:
			case OPE_APPROVAL_HISTLSTALL:		// 承認履歴一覧 (全体)
				$this->render('historylist');
				break;
			default:
$this->log(__FILE__.':'.__LINE__.":Approval default! unknown",LOG_DEBUG);
				break;
		}
	}

/**
 * approval method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function approval($id = null) {
		try{
			if (!$this->Approval->exists($id)) {
				throw new NotFoundException(__('Invalid %s .',__('Approval')));
			}
			if(!$this->Approval->is_valid($id)){
				// 正当性チェックでNGなら一覧に戻る
$this->log(__FILE__ .':'. __LINE__ .':当該データは見られません');
				$this->redirect($this->referer());
			}
			if ($this->request->is(array('post', 'put'))) {
				if ($this->Approval->save($this->request->data)) {
					$this->Session->setFlash(__('The approvals has been saved.'));
					return $this->redirect($this->referer());
				} else {
					$this->Session->setFlash(__('The approvals could not be saved. Please, try again.'));
				}
			} else {
				$data = $this->Approval->findForView($id);
				if(empty($data)){
					return $this->redirect($this->referer());
				}
				$this->set('approval',$data);
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
			return $this->redirect($this->referer());
		}
	}

/**
 * historyview method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	function historyview($id = null) {
		try{
			if (!$this->Approval->exists($id)) {
				throw new NotFoundException(__('Invalid %s .',__('Approval')));
			}
			if(!$this->Approval->is_valid($id)){
				// 正当性チェックでNGなら一覧に戻る
$this->log(__FILE__ .':'. __LINE__ .':当該データは見られません');
				$this->redirect($this->referer());
			}
			$data = $this->Approval->findForView($id);
			if(empty($data)){
				return $this->redirect($this->referer());
			}
			$this->set('approval',$data);
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
			return $this->redirect($this->referer());
		}

	}

/**
 * aprv_ok method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	function aprv_ok(){
		try{
$this->log('----------------aprv_ok',LOG_DEBUG);
			if ($this->request->is(array('post', 'put'))) {
//$this->log($this->data,LOG_DEBUG);
				$url = $this->data['Approval']['url'];
				$rc = $this->Approval->aprv_ok($this->data,$this->me);
//$this->log($rc,LOG_DEBUG);
				if($rc){
					$logid = $this->writeLog(
						array(
							'type' => 'Approval',
							'content_id' => $rc['Approval']['content_id'],
							'event_action' => '承認',
							'result' => '成功',
						));
					// 送信ステータス：承認（メール送信待ち）
					$cid = $rc['Approval']['content_id'];
					$this->loadModel('Content');
					$this->Content->setStatus($cid,VALUE_Status_Waiting);
					// 承認メール送信
					$this->loadModel('Mailqueue');
					$rc = $this->Mailqueue->putQueue('aprv_o',$cid, $logid);
					if($rc){
						// 通常の送信メール
						$rc = $this->Mailqueue->putQueue('upload',$cid, $logid);
					}
				}
//$this->log('aprv_ok url[');
//$this->log($url);
//$this->log(']aprv_ok url');
				return $this->redirect($url);
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $this->redirect(array('action' => 'index'));
	}

/**
 * aprv_ng method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	function aprv_ng(){
		try{
$this->log('----------------aprv_ng',LOG_DEBUG);
//$this->log($this->data,LOG_DEBUG);
			if ($this->request->is(array('post', 'put'))) {
				$url = $this->data['Approval']['url'];
				$rc = $this->Approval->aprv_ng($this->data,$this->me);
				if($rc){
					$logid = $this->writeLog(
						array(
							'type' => 'Approval',
							'content_id' => $rc['Approval']['content_id'],
							'event_action' => '却下',
							'result' => '成功',
						));
					// 依頼メール送信（未実装）
					$cid = $rc['Approval']['content_id'];
					$this->loadModel('Mailqueue');
					$rc = $this->Mailqueue->putQueue('aprv_x',$cid, $logid);
					// 送信ステータス：却下
					$this->loadModel('Content');
					$this->Content->setStatus($cid,VALUE_Status_Aprv_Rjct);
				}
				return $this->redirect($url);
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $this->redirect(array('action' => 'index'));
	}

/**
 * reject method (まとめて却下扱い）
 *　確認画面は出さない
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function reject($id = null) {
		try{
            $this->log('----------------reject['.$id.']',LOG_DEBUG);
            //$this->log($this->data,LOG_DEBUG);
			//$ids = array_filter($this->data['approval_id']);
			$ids = $this->params['data']['Approval_id'];

            // $idsには4回同じデータが入る場合がある（jquery.tablefixのため）
            // 重複データは削除する
            $uniq_ids = array_unique($ids);

			$cnt = 0;
			$this->loadModel('Content');
			foreach($uniq_ids as $k => $v){
				$data = array('Approval' => array('id' => $v));
//									'message' => __d($this->theme,'Rejected.'))); // 却下メッセージがあればここに書く
				$msg = 'Rejected.'; // 却下メッセージがあればここに書く
				$rc = $this->Approval->aprv_ng($data,$this->me,$msg);
				// 送信ステータス：却下
				$this->Content->setStatus($rc['Approval']['content_id'],VALUE_Status_Aprv_Rjct);


				if($rc){
					$logid = $this->writeLog(
						array(
							'type' => 'Approval',
							'content_id' => $rc['Approval']['content_id'],
							'event_action' => '却下',
							'remark' => '一括',
							'result' => '成功',
						));
					// 依頼メール送信
					$cid = $rc['Approval']['content_id'];
					$this->loadModel('Mailqueue');
					$rc = $this->Mailqueue->putQueue('aprv_x',$cid, $logid);
				} else {
					$logid = $this->writeLog(
						array(
							'type' => 'Approval',
							'content_id' => $rc['Approval']['content_id'],
							'event_action' => '却下',
							'remark' => '一括',
							'result' => '失敗',
						));
					$this->log('aprv_ng finish NG['.$v.']',LOG_DEBUG);
					// 失敗したらメールを送らない
				}
				$cnt++;
			}
			$this->Session->setFlash(__('number of records : %d ',$cnt),'Flash/success');

		} catch (Exception $e){
            $this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return $this->redirect(array('action' => 'index'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function hide($id = null) {
$this->log('Approval hide id['.$id.'] 削除アイコンでここに来ます',LOG_DEBUG);
debug($this->view);
debug($this->referer());
		try{
			$count = $this->Approval->hide($id);
			$this->Session->setFlash(__('The %d items has been deleted.',$count),'Flash/success');
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
			$this->Session->setFlash(__('The items could not be deleted.'),'Flash/error');
		}
		return $this->redirect($this->referer());
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
$this->log('Approval delete id['.$id.'] ここにはこないかも',LOG_DEBUG);
		$this->Approval->id = $id;
		if (!$this->Approval->exists()) {
			throw new NotFoundException(__('Invalid %s .',__('Approval')));
		}
//$this->request->onlyAllow('post', 'delete');
//		if ($this->Status->delete()) {
		$this->Approval->delete();  // softDelete なので常にfalse
		if ($this->Approval->existsAndNotDeleted($id)) {
			$this->Session->setFlash(__('The %s could not be deleted. Please, try again.',__('Approval')),'Flash/error');
		} else {
			$this->Session->setFlash(__('The %s has been deleted.',__('Approval')),'Flash/success');
		}
		return $this->redirect(array('action' => 'index'));
	}

/**
 * ctrl method
 *　一括処理の飛び先とパラメータ設定
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function ctrl($id = null) {
//$this->log(__FILE__.':'.__LINE__.': ctrl start');
		return parent::ctrl('approval_id',$id);
	}

/**
 * delconf 削除確認（複数選択）
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	function delconf() {
		if (!empty( $this->params['pass'] )) {
			$approvals = $this->getConfList();

			$this->set(compact('approvals'));
		}
	}

	//	削除（複数選択）hide
	function delall() {
		$ids = $this->params['data']['Approval_id'];
		$_s_cnt = 0;
		$_f_cnt = 0;
		foreach($ids as $id){
			$this->Approval->hide($id);  // softDelete なので常にfalse
			if ($this->Approval->existsAndNotDeleted($id)) {
				$_f_cnt++;
			} else {
				$_s_cnt++;
			}
		}
		if ($_f_cnt == 0) {
			$this->Session->setFlash(__('The %d items has been deleted.',$_s_cnt),'Flash/success');
		} else {
			$this->Session->setFlash(__('%d items has been deleted. (Out of %d cases)',$_s_cnt, ($_s_cnt + $_f_cnt)),'Flash/error');
		}

		return $this->redirect(array('action' => 'historylist'));
	}

/**
 * getConfList 一括確認画面に表示するリストを取得する
 *
 * @return $approvals
 */
	function getConfList() {
		$approvals = array();

		if (!empty( $this->params['pass'] )) {
			$parm['conditions'] = array(
				"Approval.id" => $this->params['pass'],
				//"Approval.user_id" => $this->me['id']
				);

			$contain = array(
				'Content' => array(
					'id','title','is_deleted','created','address_count',
					'is_expdate','user_id','cc','bcc',
					'uploadfile_count','uploadfile_totalsize',
					'User' => array(
						'id','email','name','division',
						'UserExtension' => array(
							'name_jpn','name_eng',
						),
					),
					'Status' => array(
						'id','email','name','user_id','content_id',
						'User' => array(
							'name',
							'UserExtension' => array(
								'name_jpn','name_eng',
							),
						),
					),
				),
				'AprvReqUser' => array(
					'id','email','name','division',
					'UserExtension' => array(
						'name_jpn','name_eng',
					),
				),
				'AprvUser' => array(
					'id','email','name','division',
					'UserExtension' => array(
								'name_jpn','name_eng',
					),
				),

			);
			$parm['contain'] = $contain;

			$this->Approval->recursive = 1;
			$approvals = $this->Approval->find('all', $parm);
		}

		return $approvals;
	}

/**
 * delconf 削除確認（複数選択）
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	function rejectconf() {
		if (!empty( $this->params['pass'] )) {
			$approvals = $this->getConfList();

			$this->set(compact('approvals'));
		}
	}
}
