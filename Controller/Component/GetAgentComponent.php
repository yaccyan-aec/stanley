<?php
class GetAgentComponent extends Component {
	function get(){
		if(!empty($_SERVER) && isset($_SERVER['HTTP_USER_AGENT'])) {
			return ($this->parseUserAgent($_SERVER['HTTP_USER_AGENT']));
		}
		return array();
	}

	function parseUserAgent($ua)
	{
//$this->log("*************** getAgent start");
		if($ua == null) return array();

		$userAgent = array();
		$agent = $ua;
		$products = array();

		$pattern  = "([^/[:space:]]*)" . "(/([^[:space:]]*))?"
		."([[:space:]]*\[[a-zA-Z][a-zA-Z]\])?" . "[[:space:]]*"
		."(\\((([^()]|(\\([^()]*\\)))*)\\))?" . "[[:space:]]*";

		while( strlen($agent) > 0 )
		{
			if ($l = ereg($pattern, $agent, $a))
			{
				// product, version, comment
				array_push($products, array($a[1],    // Product
				$a[3],    // Version
				$a[6]));  // Comment
				$agent = substr($agent, $l);
			}
			else
			{
				$agent = "";
			}
		}

		// Directly catch these
		foreach($products as $product)
		{
			switch($product[0])
			{
				case 'Firefox':
				case 'Netscape':
				case 'Safari':
				case 'Camino':
				case 'Mosaic':
				case 'Galeon':
				case 'Opera':
					$userAgent[0] = $product[0];
					$userAgent[1] = $product[1];
					break;
			}
		}

		if (count($userAgent) == 0)
		{
			// Mozilla compatible (MSIE, konqueror, etc)
			if ($products[0][0] == 'Mozilla' &&
			!strncmp($products[0][2], 'compatible;', 11))
			{
				$userAgent = array();
				if ($cl = ereg("compatible; ([^ ]*)[ /]([^;]*).*",
				$products[0][2], $ca))
				{
					$userAgent[0] = $ca[1];
					$userAgent[1] = $ca[2];
				}
				else
				{
					$userAgent[0] = $products[0][0];
					$userAgent[1] = $products[0][1];
				}
			}
			else
			{
				$userAgent = array();
				$userAgent[0] = $products[0][0];
				$userAgent[1] = $products[0][1];
			}
		}

		$agent = array();
		$agent['ua'] = $userAgent[0];
		$agent['version'] = $userAgent[1];
		$agent['server'] = $_SERVER;
		return $agent;
	}
	
/**
* is_oldIE
* @brief MSIE 9 以下かどうか調べる
* @param mix $agent :
* @retval boolean $ret : true =　IE9以下　/ false = それ以外
*/
	function is_oldIE($agent){
		try{
			if(isset($agent['server']['HTTP_USER_AGENT'])){
				if(preg_match("/MSIE/", $agent['server']['HTTP_USER_AGENT'])){
					return true;
				}
			}
			return false;
		} catch (Exception $e){
$this->log(__FILE__ .':'. __LINE__ .': '. print_r($e->getMessage(),true));
		}
		return false;
	}
}

