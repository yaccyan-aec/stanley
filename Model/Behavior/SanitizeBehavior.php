<?php
/**
 * SanitizeBehavior
 * 		Sanitize クラス非推奨化に対応
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Asahi Enginieering Co,. Ltd. (http://asahi-eg.co.jp)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Model.Behavior
 * @since         CakePHP(tm) v 2.4.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('ModelBehavior', 'Model');
App::uses('Sanitize', 'Utility');
class SanitizeBehavior extends ModelBehavior {

	var $patterns = array("/%/", "/\(/", "/\)/", "/\+/", "/-/");
	var	$replacements = array( "&#37;", "&#40;", "&#41;", "&#43;", "&#45;");

	// アポストロフィ, ダブルコーテーション　htmlentities でエンコードされるが、デコードされないのでここで
	var	$r_patterns = array( "/&#37;/", "/&#40;/", "/&#41;/", "/&#43;/", "/&#45;/", "/&#039;/" , "/&quot;/");
	var $r_replacements = array("%", "(", ")", "+", "-", "'", '"');

	var $tag_ptn = array( "/&lt;/", "/&gt;/");
	var $tag_rep = array( "<", ">");
	
	/**
     * サニタイズ
     *
     * @param Object $model
     * @param String $in_str : 変換前文字列
     * @param Boolean $html  : html タグを削除するかどうか(true:削除、false:エスケープして残す)
     * @retval String 変換後文字列
     */
	function sanitizeString( Model $model, $in_str ,$html = true ) {
		// 2重にサニタイズしないため、いったん戻してみる。
		$rev = $this->reverseSanitize($model,$in_str);
		if($in_str == $rev){
			// 同じなら、サニタイズしてよい
		} else {
			// サニタイズ済なので return
//$this->log('-- in != rev すでにサニタイズ済['.$in_str.']');
			return $in_str;
		}	
		if($html){
			// html タグを削除
			$str = strip_tags($in_str);							// html タグ削除
		} else {
			// タグもそのまま
			$str = $in_str;
		}
		// 基本的なエスケープ
		$flags = ENT_QUOTES;
		$str = htmlentities($str, $flags, "UTF-8");
		$str = nl2br( $str );									// 改行コードをbrタグに変換
		$str = preg_replace(array('/\r|\n/'),array(''),$str);	// 残った改行コードをスペースに
		// そのほかSQLのコマンドになるような文字をエスケープ
		$str = preg_replace($this->patterns, $this->replacements, $str);
		return $str;
	}
     /**
     * サニタイズしたデータを元に戻す
     *
     * @param Object $model
     * @param String $String : 変換前文字列
     * @retval String 変換後文字列
     */
	function reverseSanitize( Model $model, $String )	{
		$str = $String;
		// 基本的なデコード
		$flags = ENT_QUOTES;
		$str = html_entity_decode($str, $flags, "UTF-8");
		// 残った文字をデコード
		$str = preg_replace($this->r_patterns,$this->r_replacements, $str);
		// html 特殊文字のエスケープをもとにもどす
		//改行コードの変換
		$order = '<br />';
		$replace   = "\r\n";
		$str = str_replace( $order, $replace, $str );
		// タグエスケープを元に戻す
		$str = preg_replace($this->tag_ptn, $this->tag_rep, $str );
		return $str;
	}


     /**
     * サニタイズ（一部タグ許容）  今後使わない予定（2016）
     * script タグ、img タグは強制削除
     * @param Object $model
     * @param String $in_str : 変換前文字列
     * @param Boolean $html  : html タグを削除するかどうか(true:削除、false:エスケープして残す)
     * @retval String 変換後文字列
     */
	function sanitizeInfoString( Model $model, $in_str , $html = true ) {
		if($html){
			/**
			* 許容するタグ 変更したいときは↓を編集する
			*/
			$allowtag = "<strong><a><h1><h2><div>";
			$str = strip_tags($in_str,$allowtag);				// html タグ削除
		} else {
			$str = $in_str;
		}
		$flags = ENT_QUOTES;
		$str = htmlentities($str, $flags, "UTF-8");
//		$flags = ENT_QUOTES;
//		$str = h($str , $flags );								//　html　タグクオート
		$str = nl2br( $str );									// 改行コードをbrタグに変換
		$str = preg_replace(array('/\r|\n/'),array(''),$str);	// 残った改行コードをスペースに
		$str = preg_replace($this->patterns, $this->replacements, $str);
//		$str = mysql_real_escape_string($str);					// SQLをエスケープ
		return $str;
	}

}
?>
