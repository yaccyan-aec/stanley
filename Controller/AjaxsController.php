<?php
/**
 * ajax を使用した検索（テスト中）
 *
 **/
class AjaxsController extends AppController {

//	var $name = 'AjaxSearch';
	var $helpers = array('Html','Js');

	var $uses = array(	'Addressbooks.Addressbook',
						'Addressbooks.Addressgroup',
						'Addressbooks.AddressbooksAddressgroup',
						'User',
	);

	var $components = array('RequestHandler',
							'SearchPagination.SearchPagination',
							'Search.Prg',
							'Paginator'
							);
	var $paginateParm;

/*********************************************************************
 * ajax test 2012.09
 *
 *********************************************************************/
	function beforeFilter() {
       $this->autoLayout = false;
		parent::beforeFilter();
		if($this->request->is('ajax')){
		/* １ページあたりの行数を設定 */
//$this->log("AJAX です1",LOG_DEBUG);
		} else {
			// ここは将来エラーにする
$this->log("ajax じゃない!1",LOG_DEBUG);
		}
	}

/**********************************************
 *  ajax search
 *********************************************/
	function add_search_form($p = null){}

/**
 *  検索のときは　$this->params に
 *  Array(
　*		[Ajaxs] => Array(
　*	            [keyword] => 【　ここ　】
　*	            [addressgroup] => 前もって選択されていればここも
　*	            [set_list] =>
　*	    )
　*	)
 */

 /**
  *  グループのときは
  *  $this->params に
  *[data] => Array
  *		(		ここは、keyword 設定があっても入ってこない
  *		)
  *
  * $this->params->query => Array
  *		(
  *			[5] => 【　グループID　】
  *		)
  */

	function add_search($param = null){
 		try{
			if (!$this->request->is('ajax') && empty($sort_order)){
$this->log('################ NOT ajax');
				$this->Session->delete('keyword');
				$this->redirect( '/add_search_form');
//				return;
			}
			// ----------------------------------------------------------
			// 自分に送れないときは、リストから自分のアドレスを削除
			// ----------------------------------------------------------
			$_to_self = $this->Role->chkRole($this->me['group_id'],array('controller' => 'send',
														'action' => 'self'));

 			// ----------------------------------------------------------
			// フリーアドレスを許可していないときは、ユーザと紐づかないアドレスや削除ユーザと
            // 紐づいたアドレスを削除
			// ----------------------------------------------------------
            $_address_free = $this->Role->chkRole($this->me['group_id'],array('controller' => 'contents ', 'action' => 'address_free'));
			// 検索用キーワードがあるとき
			$keyword = '';
			if(isset($this->params['data']['Ajaxs']['keyword'])){
				$keyword = $this->params['data']['Ajaxs']['keyword'];
				$this->passedArgs['keyword'] = $keyword;
			} elseif(!empty($this->passedArgs['keyword'])) {
				$keyword = $this->passedArgs['keyword'];
			} elseif (!empty($this->Session->read('keyword'))){
                // グループだけを変えたとき、キーワードはセッションにある
 				$keyword = $this->Session->read('keyword');
            }
			if(!empty($keyword)){
				$this->Session->write('keyword',$keyword);
			}
			$this->passedArgs['keyword'] = $keyword;

			$addressgroup_p = array();
			$addressgroup_s = array();
			// グループ指定があるとき
			$groupid = '';

			if(!empty($this->params->query)){
				$groupid = key($this->params->query);
			} elseif (isset($this->params['data']['Ajaxs']['addressgroup'])){
				$groupid = $this->params['data']['Ajaxs']['addressgroup'];
			} elseif(!empty($this->passedArgs['groupid'])) {
				$groupid = $this->passedArgs['groupid'];
			} elseif(!empty($this->request->data['a_list'])) {
				$groupid = $this->request->data['a_list'];
			} else {
				// 何も指定してないときは全体
                $groupid = '';
			}
			if($groupid == 'sep' || $groupid == 'all'){
                $groupid = '';
            }
			$this->passedArgs['groupid'] = $groupid;

			if($groupid == ''){
			// グループ指定がないとき(sep)は、自分の個人グループと
			// 所属する共通グループを出す
				// このメンバーがアクセスできるすべてのグループ一覧を持ってくる
				if(ADDRESSBOOKS_PERSONAL_FLG){
					// 個人アドレス帳が使用できるとき
					$addressgroup_p = $this->Addressgroup->generateTreeList($this->me);
				}
				if(ADDRESSBOOKS_SHARED_FLG){
					// 共通アドレス帳が使用できるとき
					$addressgroup_s = $this->Addressgroup->generateTreeList($this->me,'Y');
				}
				$groupid = array();
				foreach($addressgroup_p as $k => $v){
					$groupid[] = $k;
				}
				foreach($addressgroup_s as $k => $v){
					$groupid[] = $k;
				}
			}
			// 絞り込み用パラメータを持ちまわる
			$cond = $this->Addressbook->searchKeyword($this->passedArgs);
			$search_conditions = array();
			if(empty($cond)){
				$search_conditions = array('AddressbooksAddressgroup.addressgroup_id' => $groupid);
			} else {
				$search_conditions = array('AND' => array(
										array('AddressbooksAddressgroup.addressgroup_id' => $groupid),
										$cond));
			}
//debug($search_conditions);
			if(!$_to_self){
$this->log('ajax 自分には送れない 3['.$this->me['email'].']',LOG_DEBUG);
				$search_conditions[] = array('Addressbook.email NOT' => $this->me['email']);
			}
			if(!$_address_free){
$this->log('ajax 削除ユーザや存在しないユーザのアドレスには送ってはいけない',LOG_DEBUG);
                // user_id が　0 (ユーザがない)
                // 削除されたユーザIDを持っている
				$search_conditions[] = array('Addressbook.user_id NOT' => 0);
                $this->loadModel('User');
                $dellist = $this->User->find('list',array(
                    'conditions' => array('is_deleted' => true)
                    ));
				$search_conditions[] = array('Addressbook.user_id NOT' => array_keys($dellist));
            }
//debug($search_conditions);

			// 並び順の設定
			if (!empty($this->request->data['Content']['sort_order'])) {
                $sort = array('order' => $this->request->data['Content']['sort_order'],
    						'direction' => $this->request->data['Content']['sort_direction'],
    						);
                $this->Session->write('Addressgroup.sort', $sort);
            } else {
                $sort =$this->Session->read('Addressgroup.sort');
            }

			if (empty($sort['order'])){
				$sort['order'] ='Addressgroup.is_shared';
			};
			if (empty($sort['direction'])){
				$sort['direction'] = 'DESC';
			};


			$this->Paginator->settings = array(
							'conditions' => $search_conditions,
							'limit' => PAGE_LIMIT_DEFAULT,
							'group' => 'AddressbooksAddressgroup.addressbook_id',
							'contain' => array('Addressbook',
												'Addressgroup'
												),
							'recursive' => false,
							// 個人アドレス帳が先に来るように
							//'order' => 'Addressgroup.is_shared'.' ASC',
							'order' => $sort['order'] .' '.$sort['direction'],
			);
			$data = $this->Paginator->paginate('AddressbooksAddressgroup');

			$addresses = array();

			// ﾘｽﾄ用に整形
			// [S] [P] を変更するときは、Plugin/Addressbooks/Model/Addressbook.php
			// getAddList　関数中身と連動させること。
			// デバッグモード
			if(defined('DEBUG_send') && DEBUG_send){
				$format1 = '%s.%d: %s - %s] %s'; // name
				foreach($data as $k => $v){
					$name = $this->Addressbook->_getName($v);
					$div = $this->Addressbook->_getDivision($v);
					$email = Hash::get($v,'Addressbook.email');
					$id = Hash::get($v,'Addressbook.id');
					$type = (Hash::get($v,'Addressgroup.is_shared') == 'Y') ? 'S' : 'P';
//debug($type);
					// format を使うとなぜか　type がうまく入らないので地道に組み立てる↓
					$str = '['.$type.'.'.$id.': '.$div.' - '.$name.'] '.$email;
					$addresses[$id] = $str;
//						$addresses[$id] = sprintf($format1,$type,$id,$div,$name,$email);

				}
			} else {
			// 本番
				$format1 = '%s';
				$format2 = '[%s]　　%s　(%s)';
				foreach($data as $k => $v){

					$name = $this->Addressbook->_getName($v);
					$division = $this->Addressbook->_getDivision($v);
					$email = Hash::get($v,'Addressbook.email');
					$id = Hash::get($v,'Addressbook.id');

					if($name == $email){
						$addresses[$id] = sprintf($format1,$email);
					} else {
						$addresses[$id] = sprintf($format2,$division,$name,$email);
					}

				}
			}

			$this->set('addresses',$addresses);
			$this->set('sort',$sort);
			Configure::write('debug',0);

			//テスト
			$this->render('/Elements/Content/Search/Addressgroup_index', 'ajax');
			//$this->render('/Contents/add');
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
	}

	/**********************
	 *  リセットボタン
	 **********************/
	function add_search_reset($param = null){
 		try{
			$this->autoRender = false;
			$this->Session->delete('keyword');
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
//		$this->redirect(array('action' => 'add_search'));
// redirect だとパラメータが全部初期化されるので group 選択を残すために変更2017/06/16
        $this->add_search();
	}
/*********************************************************************
 * ajax TEST 2016.2
 *
 *********************************************************************/
	function js_submit_form($p = null){}

    function js_submit($param = null){
        // Ajax or not
$this->log('js_submit');
//$this->log($param);
        if (!$this->request->is('ajax')){
            $this->redirect( '/js_submit_form');
        }
        // save OK
        $this->render( '/Elements/Ajaxs/ajaxupdated','ajax');
    }
/*********************************************************************
 * ajax old 2012.09
 *
 *********************************************************************/
/*
	function add_reset(){
		$this->layout = 'ajax';
		$this->log("ajax disp get!",LOG_DEBUG);
		$this->set('data','aaa');
           $this->render('/Elements/Content/Search/Addressgroup_index', 'ajax');
	}

*/


}
?>
