<?php
App::uses('MyComponent' , 'Controller/Component');
/**
 * 共通コンポーネント
 *
 * @package  FTS2
 * @author   Asahi Engineering co.,ltd.
 * @since    PHP 5.3 CakePHP(tm) v 1.2
 * @version  5.0.0
 */

class CommonComponent extends MyComponent {

	/**
	 * @var integer   デフォルトで使用するファイルの有効期限
	 */
	var $limit = 8;

    function __construct(ComponentCollection $collection, $settings = array()) {
    	parent::__construct($collection, $settings);
		// システムの有効期限を取得　app/config/app.php で記述
    	$this->limit = Configure::read('Time_limit_days');
    }

	var $_controller = null;

	public function initialize(Controller $controller) {
		$this->_controller = $controller;	// controllerの変数を使いたいので。
	}

	/**
	 * getexpday
	 * 現在から $limit 日後の日付を求める
	 * @param	int		$limit　　日数（デフォルトは８）
	 * @return	stirng	日付
	 *
	 */
	function getexpday($limit = null){
		$_limit = $this->limit;
		// オプションがあったら優先させる
		if($limit != null) $_limit = $limit;
		$exp_day = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + $_limit, date("y")));
		return($exp_day);
	}

	/**
	 * chkDate
	 * 期限切れチェック（日付単位の比較）
	 * @param	string	$one : チェックしたい日付（null のときはチェックしない）
	 * 					       '0000-00-00' だったときも null と同じ扱い
	 * @param	string	$two : 基準の日付（null のときは現在日付）
	 *
	 * @return	int	 	1 -- 期限前			$one > $two
	 * 					0 -- 等しい			$one = $two
	 * 					-1 -- 期限切れ		$one < $two
	 */
	function chkDate($one , $two = null){
		if($one == null) return 0;
		$_one = $one;
		$_two = ($two == null) ? $this->now() : $two;

		$_one_ary = date_parse($_one);
		$_two_ary = date_parse($_two);

		$_one_fmt = sprintf('%04d%02d%02d',$_one_ary['year'],$_one_ary['month'],$_one_ary['day']);
		$_two_fmt = sprintf('%04d%02d%02d',$_two_ary['year'],$_two_ary['month'],$_two_ary['day']);
		// まれに　expdate が　'0000-00-00' だったときには null のときと同じ扱いに
		if($_one_fmt == '00000000') return(0);

		if($_one_fmt > $_two_fmt) return(1);
		if($_one_fmt == $_two_fmt) return(0);
		return(-1);
	}

	/**
	 * fmtdate
	 * 日付のフォーマット
	 * @param	mixed	$one  フォーマットしたい日付（デフォルトは現在時刻）
	 * @return	string	yyyy-mm-dd 形式にフォーマットされた日付
	 *
	 */
	function fmtdate($one = null){
		$_one = ($one == null) ? $this->now() : $one;
		$_one_ary = date_parse($_one);
		$_one_fmt = sprintf('%04d-%02d-%02d',$_one_ary['year'],$_one_ary['month'],$_one_ary['day']);
		return($_one_fmt);
	}

	/**
	 * now
	 * 現在時刻取得
	 * @return string          YYYY-mm-dd HH:ii:ss 形式
	 */
	function now(){
		$d = getdate();
		$today = date('Y-m-d H:i:s',$d[0]);
		return($today);
	}


	/* -----------------------------------------------------
	 * 配列の value がすべて null かどうかを調べる
	 * 　　　とりあえず１次元のみ
	 *
	 * param  : IN   : $ary         (array型)
	 *
	 * return : true = すべてnull / false = 値がある
	 * -----------------------------------------------------
	 */
	/**
	 * is_all_null
	 * 配列（１次元）の value がすべて null かどうかを調べる
	 *
	 * @param  array     $ary      調べたい配列
	 *
	 * @return boolean   true = すべてnull
	 *					 false = 値がある
	 */
	function is_all_null($ary = null){
		if($ary == null) return true;
		$_count = 0;
		foreach($ary as $k => $v){
//$this->log(__METHOD__."[".__LINE__."] key[".$k."]val[".$v."]");
			if(!empty($v)){
//$this->log(__METHOD__."[".__LINE__."]NOT empty. ");
				return false;
			}
		}
		return true;
	}


/**
* function _save_cookie
* @brief クッキーにセーブ　2010.12.29
* user の　ユーザ情報のすべてを入れるとセパレータなどを含むおそれがあるため
* ID,PWD だけをクッキーにセーブ
* @param  void
* @retval boolean true : 成功　/ false : 失敗
*/
	function saveCookie(){

		if(empty($this->_controller->me)){
			/**
			* セーブするデータがない
			*/
			return false;
		}
		/**
		* ログイン中のIDとパスワードをセーブ
		*/
		$_now_cookie = array(	'i' => $this->_controller->me['id'],
								'p' => $this->_controller->me['pwd']
							);

		$this->_controller->Cookie->write('fts',$_now_cookie,true, COOKIE_EXPIRE);

		/**
		* 言語もセーブするとき
		*/
		$this->_controller->Cookie->write('fts_lang',$this->_controller->_mylang,false, COOKIE_EXPIRE);
		return true;
	}

/**
* func diskSpaceCheck
* @brief 既定のパスの空き容量が足りているかチェック
* @param string $path : 調べたいパス
* @param string $min :  最低容量（% または　数値単位付きでもOK）
* @retval bool  true : ok / false : NG
*/

    function diskSpaceCheck($path = '/', $min = '50%'){
        try{
            // 最低ライン閾値
            $min_bytes = $this->getThresholdValue($path,$min);
            //空き容量

            $free_bytes = $this->getDiskFree($path,false);

            if($min_bytes > $free_bytes){
$this->log(__FILE__ .':'. __LINE__ .': 閾値['.number_format($min_bytes).'] 空き['.number_format($free_bytes).'] 容量オーバー');
                return false;
            }
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
        return true;
    }

/**
* func getDiskTotal
* @brief 既定のパスの全体容量を調べる　単位付きは小数点以下２桁まで
* @param string $path : 調べたいパス
* @param bool   $flg :  true : 単位付き　/ false : 単位なし
* @retval mix   全体容量
*/
    function getDiskTotal($path = '/',$flg = true){
        //全体サイズ
        $total_bytes = disk_total_space($path);
        if(!$flg){
            return $total_bytes;
        }
        $si_prefix = Configure::read('VALUE_SI_PREFIX');
        $base =  Configure::read('VALULE_SI_BASE');

        $class = min((int)log($total_bytes , $base) , count($si_prefix) - 1);
        $total = sprintf('%1.2f' , $total_bytes / pow($base,$class)) . $si_prefix[$class];
        return $total;
    }

/**
* func getDiskTotal
* @brief 既定のパスの全体容量を調べる　単位付きは小数点以下２桁まで
* @param string $path : 調べたいパス
* @param bool   $flg :  true : 単位付き　/ false : 単位なし
* @retval mix   全体容量
*/
    function getDiskFree($path = '/',$flg = true){
        //全体サイズ
        $free_bytes = disk_free_space($path);
        if(!$flg){
           return $free_bytes;
        }
        $si_prefix = Configure::read('VALUE_SI_PREFIX');
        $base =  Configure::read('VALULE_SI_BASE');

        $class = min((int)log($free_bytes , $base) , count($si_prefix) - 1);
        $free = sprintf('%1.2f' , $free_bytes / pow($base,$class)) . $si_prefix[$class];
        return $free;
    }

/**
* func getThresholdValue
* @brief 閾値を求める
* @param string $path : 調べたいパス
* @param string $val  : 値（単位付き、または%）
* @retval int   閾値の値
*/
    function getThresholdValue($path = '/', $val = '90%'){
		try{
            $total = $this->getDiskTotal($path,false);
            if(is_numeric($val)){
                // 数字だけだったらそのまま返す
                return $val;
            }
            $last = substr(trim($val), -1);
            if($last == '%'){
               // % だったら残り最低ラインを計算して返す
                $suji = preg_replace("/%/", "" , $val);
                // 全体容量から空き容量最低値を求める
                $value_min = ($total * $suji) / 100 ;
                return $value_min;
            } elseif($last == 'B'){
                // 単位付きだったらバイトに計算して返す
                $suji = preg_replace("/[A-Za-z]*/", "" , $val);
                $moji = preg_replace("/[0-9.]*/", "" , $val);
                $si_prefix = array_flip(Configure::read('VALUE_SI_PREFIX'));
                $base =  Configure::read('VALULE_SI_BASE');
                if(strpos($val,'i')!== false){
                    //'abcd'のなかに'i'が含まれている場合はベースを1024で計算
                    $si_prefix = array_flip(Configure::read('VALUE_SI_PREFIX_I'));
                    $base =  Configure::read('VALULE_SI_BASE_I');
                }
                $class = $si_prefix[$moji];
                $value_min = pow($base,$class) * $suji;
                return $value_min;
            }
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
        return $val;
    }

}
