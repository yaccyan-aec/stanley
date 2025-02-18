<?php
/**
*
* CSV読み込みを行う
*
* ※使用方法
* 1．このｺﾝﾎﾟｰﾈﾝﾄをNewする
* ex. $csvcomp = new CsvComponent();
* 2．ﾌｧｲﾙｵｰﾌﾟﾝ
* ex. $csvcomp->csvopen( $filepath, $charset,1000 );
* 3．CSVを一行ずつ読む
* e. $csvarray = $csvcomp->readline();
* 4．ﾌｧｲﾙｸﾛｰｽﾞ
* $csvcomp->csvclose()
*
*※空白行は、読み飛ばす
*※行頭に#があればコメント行として読み飛ばす
*
*
* 2010/4/2 sugawara
*
**/
class CsvComponent extends Component
{
	var $handle = null;
	var $charset;
	var $recsize;
	var $titleMap = array();
	var $me;
	var $titleStrs = array();

	function CsvComponent( $me ){
		$this->me = $me;
	}

	/*
	 * CSVファイルオープン
	 * $filepath …　CSVﾌｧｲﾙのﾊﾟｽ
	 * $charset …　ﾌｧｲﾙの文字ｺｰﾄﾞ
	 * $recsize　…　CSVﾃﾞｰﾀのｻｲｽﾞ
	 */
	function csvopen( $filepath, $charset, $recsize ){
//$this->log(__FILE__ .':'. __LINE__ .': '. print_r($filepath,true));
		$this->charset = $charset;
		$this->recsize = $recsize;

		setlocale(LC_ALL, 'ja_JP.UTF-8');
//$this->log(__FILE__ .':'. __LINE__ .': '. print_r("fopen",true));

		if( ($this->handle = fopen( $filepath, "r" )) === FALSE ){
//$this->log(__FILE__ .':'. __LINE__ .': '. print_r("fopen",true));
			throw new Exception("db error");
		}
//$this->log(__FILE__ .':'. __LINE__ .': '. print_r("setCsvTitle",true));
		if( $this->setCsvTitle()=== FALSE ){
//$this->log(__FILE__ .':'. __LINE__ .': '. print_r("setCsvTitle",true));
			$this->csvclose();
			throw new Exception(__('Please specify the CSV file of a correct format.', true));
		}
		return TRUE;
	}

	/*
	 * CSVファイルクローズ
	 */
	function csvclose(){
		if( fclose( $this->handle ) === FALSE ){
			throw new Exception("db error");
		}
	}

	/*
	 * CSV一行読込み
	 */
	function readline(){
		$ret = FALSE;

		while (($data =$this->fgetcsv_reg($this->handle, $this->recsize)) !== FALSE) {
			//if(empty($data)|| substr_compare( trim($data[0]), "#", 0, 1  )===0){
			//	Warningが出るため修正 2016.11.28 S.Tsuji
			if(empty($data)|| (!empty($data[0]) && substr_compare( trim($data[0]), "#", 0, 1  ))===0){
				// 空白行、コメント行読み飛ばし
				continue;
			}

			if( strcmp( $this->charset, 'EUC-JP' ) == 0 ){
				mb_convert_variables("UTF-8", "EUC-JP", $data );
			}
			else if( strcmp( $this->charset, 'SJIS' ) == 0 ){
				mb_convert_variables("UTF-8", "SJIS", $data );
			}
			else if( strcmp( $this->charset, 'auto' ) == 0 ){
				//	auto対応 2016.11.28 S.Tsuji
				//mb_convert_variables("UTF-8", "UTF-8,EUC-JP,SJIS", $data );
				mb_convert_variables("UTF-8", "ASCII,JIS,UTF-8,EUC-JP,SJIS", $data );
			}
			$ret = $data;
			break;
		}
		return $ret;
	}

	/**
	 * http://yossy.iimp.jp/wp/?p=56 より
	 */
	function fgetcsv_reg (&$handle, $length = null, $d = ',', $e = '"') {
        $d = preg_quote($d);
        $e = preg_quote($e);
        $_line = "";
        $eof = FALSE;
        while (($eof != true)and(!feof($handle))) {
            $_line .= (empty($length) ? fgets($handle) : fgets($handle, $length));
	        // add by sugawara　↓
	        $_line = $this->delete_bom($_line);
	        $_line = $this->delete_bom($_line);
			// add by sugawara ↑
            $itemcnt = preg_match_all('/'.$e.'/', $_line, $dummy);
            if ($itemcnt % 2 == 0) $eof = true;
        }

        $_csv_line = preg_replace('/(?:\r\n|[\r\n])?$/', $d, trim($_line));
        $_csv_pattern = '/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';
        preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
        $_csv_data = $_csv_matches[1];
        for($_csv_i=0;$_csv_i<count($_csv_data);$_csv_i++){
            $_csv_data[$_csv_i]=preg_replace('/^'.$e.'(.*)'.$e.'$/s','$1',$_csv_data[$_csv_i]);
            $_csv_data[$_csv_i]=str_replace($e.$e, $e, $_csv_data[$_csv_i]);
        }
        return empty($_line) ? false : $_csv_data;
    }

    /*
	 * ﾃﾞｰﾀﾁｪｯｸ
	 */
	function check( $csvarray ){
		if( count( $csvarray ) < count( $this->titleStrs ) ){
			//$this->log("CSVカラム数が不正");
			throw new Exception(__('Please specify CSV files of a correct number of columns.', true));
		}
	}

	/*
	 * CSV一行読込み
	 */
	function setCsvTitle(){
		if( ! $this->isHandle() ){
			return FALSE;
		}
		$ret = FALSE;
		while (($data = $this->fgetcsv_reg($this->handle, $this->recsize)) !== FALSE) {

			//2012.05.12:okamoto:warningが出ていたので empty($data[0]) を条件文に入れたがこれで本当にいいかが微妙。
			if(empty($data)|| empty($data[0]) ||substr_compare( trim($data[0]), "#", 0, 1  )===0 ){
				// 空白行、コメント行読み飛ばし
				continue;
			}
			if( strcmp( $this->charset, 'EUC-JP' ) == 0 ){
				mb_convert_variables("UTF-8", "EUC-JP", $data );
			}
			else if( strcmp( $this->charset, 'SJIS' ) == 0 ){
				mb_convert_variables("UTF-8", "SJIS", $data );
			}
			for( $i=0; $i<count($data); $i++ ){
				$data[$i] = str_replace( " ", "", $data[$i] );
				$titlestr = $data[$i];
				$this->titleMap[$titlestr] = $i;
			}
			$ret = $data;
			break;
		}
		if( ! $this->checkTitle( $data )){
			$ret = FALSE;
		}

		return $ret;
	}

	/*
	 * ﾃﾞｰﾀﾁｪｯｸ
	 */
	function checkTitle( $csvarray ){
		foreach( $this->titleStrs as $titleStr ){
			if( ! in_array( $titleStr, $csvarray ) ){
				return FALSE;
			}
		}
		return TRUE;
	}


	function isHandle(){
		if( $this->handle ){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}

	function delete_bom($str)
	{
		//$str = trim( $str );
		//	Noticeエラーがでるため修正 2016.11.28 S.Tsuji
		if ($str) {
		    if (ord($str{0}) == 0xef && ord($str{1}) == 0xbb && ord($str{2}) == 0xbf) {
		        $str = substr($str, 3);
		    }
		}
	    return $str;
	}

}


?>