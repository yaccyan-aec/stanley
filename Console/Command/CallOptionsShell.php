<!-- 動作確認用 -->
<?php
/**
* @file CalltfgShell
* @brief Queue に予約されたデータを順番に変換するバッチ
* 			tfg , zip , pscan
* @package jp.co.asahi-eg.fts2
* @author Asahi Engineering co., ltd.
* @since PHP 5.3 CakePHP(tm) v 1.2 クラスの運用開始年月日
* @version 5.0.0
*/
class CallOptionsShell extends AppShell {
	public $uses = array(	'Queue',
							'Content',
							'Approval',
							'Uploadfile',
							'User',
							'Eventlog',
						);

var $flist;
var $updir;
var $Server;
var $loopmax = 3;
var $pscan_num = 0;

	public $me = null;

	function startup() {
		parent::startup();
		$this->me = $this->getShellId();
		$this->init();
   	}

	/*
	 * 現在時取得
	 */
    function now(){
    	$d = getdate();
		$today = date('Y-m-d H:i:s',$d[0]);
		return($today);
	}

	function main() {
		// 現在時刻を取り出す
		$_now = $this->now();

		$arg_count = count($this->args);
debug($arg_count);
		//----------------------------------------------------------------------------
		// パラメータ有のとき、パラメータに記載された content_id を順番に処理して終了
		//----------------------------------------------------------------------------
		//
		// フォルダのマウントチェック
		//
$this->out($this->Server['ip']);
		$_is_mount = $this->is_mount($this->Server['ip']);

		if($_is_mount){
debug('マウント　OK');
		} else {
			// マウントされてなかったらおしまい
$this->out(__FILE__.':'.__LINE__.':>>>>>>>>>> マウントNG　end. ');
			exit ;
		}

		$this->loadModel('Queue');


		if(CakePlugin::loaded('Pscan')){
			$this->loadModel('Pscan.Pscan');
//			$qdata = $this->Queue->queue_get('PSCAN',VALUE_Status_Waiting,0);
			// 終了待ちがあれば先に処理
			$qdata = $this->Pscan->queue_get_doing();
//$this->log($qdata);
			if(!empty($qdata)){
debug('--- 終了待ち　PSCAN found');
				$qid = $qdata['Queue']['id'];
				if($this->Pscan->is_pscan_finish($qdata)){
					// 終わっていたら取り込む
					$_rtn = $this->Pscan->after_pscan($qdata);
debug('--- 終了待ち　終わった['.$_rtn.']');
					$this->Queue->setStatus($qid,$_rtn);
				} else {
debug('--- 終了待ち　終わっていない');
				}
			} else {
				// 終了待ちがなければ　次の予約を処理する
				$qdata = $this->Pscan->queue_get_waiting();
//$this->log($qdata);
				if(!empty($qdata)){
debug('--- 開始待ち　PSCAN found');
					$qid = $qdata['Queue']['id'];
					$_rtn = $this->Pscan->exec($qdata);
debug('--- rc['.$_rtn.']');
					$this->Queue->setStatus($qid,$_rtn);
				}
			}
		}

		if(CakePlugin::loaded('Tfg')){
debug($this->loopmax);
			for($i = 0; $i < $this->loopmax ; $i++){
				// --- ここはループで回るかも
				$qdata = $this->Queue->queue_get('TFG',VALUE_Status_Conv_Waiting,0);
		$this->log($qdata);
				if(empty($qdata)){
	debug('--- TFG empty');
					return 0;
				}
				$qid = $qdata['Queue']['id'];
				$this->loadModel('Tfg.Tfg');
				$rc = $this->Tfg->exec($qdata);
	debug('--- rc['.$rc.']');
				if($rc){
					// メール
					$this->Content->sendFromQueue($qid);
				}

			}
		}
$this->out(__FILE__.':'.__LINE__.':>>>>>>>>>> end. ');
		return(0);
	}

	// 初期設定
	//   可読性のため後ろに記載
	function init(){
		$this->updir = Configure::read("Upfile.dir");
		$s_ary = Configure::read("VALUE_TFG_Server");
		if(defined('VALUE_TFG_Shell_Loop_Max')){
			$this->loopmax = VALUE_TFG_Shell_Loop_Max;
		}

//debug($s_ary);
		// マウントチェックに必要
		if(empty($s_ary)){
			$this->Server = array(
				// 'ip' => '192.168.1.133',			// TFG サーバの IP
				'ip' => WIN_SERVER_IP,			// TFG サーバの IP
				'user' => WIN_SERVER_USER,					// TFG サーバの USER
				'pass' => WIN_SERVER_PASS,				// TFG サーバの PASS
				'shell' => VALUE_Shell_Base.'tfgexec_test',				// 変換shell
				'jar' => VALUE_TFG_WinBase.'TFGConv.jar',				// 変換jar
				'prop' => VALUE_TFG_WinBase.'TFGConv.properties'		// 設定ファイル
				);
		} else {
			if($s_ary['is_debug']){
				$this->Server = array(
					'ip' => $s_ary['ip'],					// TFG サーバの IP
					'user' => $s_ary['user'],				// TFG サーバの USER
					'pass' => $s_ary['pass'],				// TFG サーバの PASS
					'shell' => VALUE_Shell_Base.$s_ary['debug']['shell'],		// 変換shell
					'jar' => VALUE_TFG_WinBase.$s_ary['debug']['jar'],				// 変換jar
					'prop' => VALUE_TFG_WinBase.$s_ary['debug']['prop']				// 設定ファイル
					);
			} else {
				$this->Server = array(
					'ip' => $s_ary['ip'],					// TFG サーバの IP
					'user' => $s_ary['user'],				// TFG サーバの USER
					'pass' => $s_ary['pass'],				// TFG サーバの PASS
					'shell' => VALUE_Shell_Base.$s_ary['release']['shell'],		// 変換shell
					'jar' => VALUE_TFG_WinBase.$s_ary['release']['jar'],			// 変換jar
					'prop' => VALUE_TFG_WinBase.$s_ary['release']['prop']			// 設定ファイル
					);
			}
		}

	}

	/*
	 * システムコマンド
	 */
	function is_mount($ip){
debug(__FILE__.':'.__LINE__.':--- is_mount start['.PHP_OS.']');
		try{
			if(PHP_OS == 'Linux'){
				// $this->args は 親 shell の $this->args を引きつぐ
				$_command = escapeshellcmd("mount | grep ". $ip);
				$retval;
				$arr;
				exec($_command,$arr,$retval);
debug(__FILE__.':'.__LINE__.':--- command end');
debug($arr);
debug('retval['.$retval.']');
				// マウントされていないときでも $retval に1 が返ってくるため
				// ここでの判定は無理かも
				return(($retval == 1)? true : false);
			} else {	// Windows のときは同一サーバとして常に true
debug(__FILE__.':'.__LINE__.':NOT LINUX');
				return true;
			}
		} catch(Exception $e){
debug(__FILE__.':'.__LINE__.':UNKNOWN ERROR');
debug($e);
			return false;
		}
	}

}
?>
