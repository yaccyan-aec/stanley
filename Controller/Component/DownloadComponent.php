<?php
App::uses('MyComponent' , 'Controller/Component');
/** file download
* PEAR::HTTP_Download は　app/vendor/Pear 以下のものを使用する。
**/

App::import('Vendor', 'pear_init');
App::import('Vendor', 'HTTP_Download',array('file' => 'Pear'.DS.'HTTP'.DS.'Download.php'));

class DownloadComponent extends MyComponent
{
	var $dir = null;
	var $path = null;
    function __construct() {
//    	parent::__construct();
		// アップロードディレクトリを取得　app/config/app.php で記述
		$this->dir = Configure::read('Upfile.dir');
//		debug($this);
	}

/** HTTP_Download を使用した download(これを使う？)
 * FDM　レジューム使用可
**/
    function download($tmpname,$name,$type) {
		try{
					
	$this->log('download start tmpname['.$tmpname.'] name['.$name.'] type['.$type.']',LOG_DEBUG);
			$dl_path = $this->dir . DS . $tmpname;
	$this->log('======== dl_path['.$dl_path.']',LOG_DEBUG);

			if( !file_exists($dl_path) ) {
	$this->log("download ::===== not download [$dl_path] ==",LOG_DEBUG);
			   return false;
			}
			$new_name = mb_convert_encoding($name,"Shift_JIS","UTF-8");
	$this->log("download ::===== new name [$new_name] ==",LOG_DEBUG);
			$params = array(
				'file'                => $dl_path,
				'contenttype'         => $type,
				'contentdisposition'  => array(HTTP_DOWNLOAD_ATTACHMENT, $new_name),
	//----------- ブラウザで pdf を開くときはこちら↓
	//			'contentdisposition'  => array(HTTP_DOWNLOAD_INLINE, $new_name),
			);

	//$this->log($params,LOG_DEBUG);
			$ret = HTTP_Download::staticSend($params);
	$this->log("HTTP_Download:: ret[".$ret."]",LOG_DEBUG);
			if(PEAR::isError($ret)){
	$this->log("HTTP_Download:: die err[".$ret->getMessage()."]",LOG_DEBUG);
				return false;
			}
			return $ret;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
    }

/** HTTP_Download を使用した 閲覧(for pscan)
 * FDM　レジューム使用可
**/
    function viewfile($tmpname,$name,$type) {
		try{
	$this->log('view start tmpname['.$tmpname.'] name['.$name.'] type['.$type.']',LOG_DEBUG);
			$dl_path = $this->dir . DS . $tmpname;
	$this->log('======== dl_path['.$dl_path.']',LOG_DEBUG);

			if( !file_exists($dl_path) ) {
	$this->log("view ::===== not download [$dl_path] ==",LOG_DEBUG);
			   return false;
			}
			$new_name = mb_convert_encoding($name,"Shift_JIS","UTF-8");
	$this->log("view ::===== new name [$new_name] ==",LOG_DEBUG);
			$params = array(
				'file'                => $dl_path,
				'contenttype'         => $type,
	//			'contentdisposition'  => array(HTTP_DOWNLOAD_ATTACHMENT, $new_name),
	//----------- ブラウザで pdf を開くときはこちら↓
				'contentdisposition'  => array(HTTP_DOWNLOAD_INLINE, $new_name),
			);

	//$this->log($params,LOG_DEBUG);
			$ret = HTTP_Download::staticSend($params);
	$this->log("HTTP_Download:: view ret[".$ret."]",LOG_DEBUG);
			if(PEAR::isError($ret)){
	$this->log("HTTP_Download:: view die err[".$ret->getMessage()."]",LOG_DEBUG);
				return false;
			}
			return $ret;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;

    }

	function is_valid($fid,$user,$ref){
		$rc = true;
		try{
$this->log('is_valid : ダウンロード正当性チェック uploadfile_id['.$fid.']');		
$this->log($fid );	
$this->log(Router::parse($ref));
$this->log($user);		
			$my_id = $user['id'];
			$my_gid = $user['group_id'];
			// Super のときはダウンロードを許可
			$this->loadModel('Role');
			$is_super = $this->Role->isSuper($my_gid);
			if($is_super){
$this->log('Download チェック　：　Super なので　OK');				
				return true;
			}

			$ref_ary = Router::parse($ref);				// パラメータ分析
			$my_pass = Hash::get($ref_ary,'pass.0');	// 元の画面のパラメータのID
			
			$this->loadModel('Uploadfile');
			$uploadfile = $this->Uploadfile->find('first',array(
					'conditions' => array(
						'Uploadfile.id' => $fid,
						'Uploadfile.avs_result <=' => 0,	// スキャンしていないか、OKのものだけ
						// ダウンロードまたは閲覧許可のものだけ
						'Uploadfile.dl_mod' => array(
							VALUE_dl_mod_View, VALUE_dl_mod_OK
						),
					),
					'recursive' => -1,
				));
$this->log($uploadfile);
			if(empty($uploadfile)){
$this->log('Download チェック　：　当該データなし　fid['.$fid.']');				
				return false;
			}
$this->log('dl_mod['.$uploadfile['Uploadfile']['dl_mod'].']');	
			
				
			switch($ref_ary['controller']){
				case 'approvals':
$this->log('承認から来た');
					// 承認権限があるかチェック
					$can_aprv = $this->Role->chkRole($my_gid,array(
						'controller' => 'approvals',
						'action' => 'approval',
					));
					if(!$can_aprv){
$this->log('Download チェック　：　承認権限なし　fid['.$fid.']');				
						$rc = false;
					} else {
						// 当該承認データがあるかチェック
						$this->loadModel('Approval');
						$approval = $this->Approval->find('first',array(
							'conditions' => array(
								'Approval.id' => $my_pass,
								'Approval.content_id' => $uploadfile['Uploadfile']['content_id'],
							),
							'recursive' => -1,
						));
$this->log($approval);
						if(empty($approval)){
$this->log('Download チェック　：　有効な承認情報なし　fid['.$fid.']');				
							$rc = false;
						}
					}
					break;
					
				case 'statuses':
$this->log('受信履歴から来た sid['.$my_pass.']cid['.$uploadfile['Uploadfile']['content_id'].'] uid['.$my_id.']');	
					// 当該受信情報があるかチェック
					$this->loadModel('Status');
					$status = $this->Status->find('first',array(
						'conditions' => array(
							'Status.id' => $my_pass,
							'Status.content_id' => $uploadfile['Uploadfile']['content_id'],
							'Status.user_id' => $my_id,
							),
						'recursive' => -1,
					));
$this->log($status);
					if(empty($status)){
$this->log('Download チェック　：　有効な受信情報なし　fid['.$fid.']');				
						$rc = false;
					}
					break;
				
				case 'contents':
$this->log('送信履歴から来たcid['.$uploadfile['Uploadfile']['content_id'].'] uid['.$my_id.']');	
					// 当該送信情報があるかチェック
					$this->loadModel('Content');
					$content = $this->Content->find('first',array(
						'conditions' => array(
							'Content.id' => $uploadfile['Uploadfile']['content_id'],
							'Content.user_id' => $my_id
							),
						'recursive' => -1,
						
					));
$this->log($content);				
					if(empty($content)) {
$this->log('Download チェック　：　有効な送信情報なし　fid['.$fid.']');				
						$rc = false;
					} 
					break;
				default:
$this->log('Download チェック　：　その他　（　とりあえずOK　） 再送？　fid['.$fid.']');				
				break;
			}
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
$this->log('return ['.$rc.']');				
		return $rc;
	}
}
?>