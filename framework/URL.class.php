<?php 

class URL {
	public static $_url;

	public static function getAction($name, $params=[]) {

		if(count(explode("/", $name)) == 1) {
			$name = CONTROLLER . "/" . $name;
		}
		
		$url = mb_strtolower((SBJ::config("baseURL") . "/" . $name));
		if(count($params) === 0) return $url;
		else {
			$buildedQuery = http_build_query($params);	
			$buildedUrl = $url . "?" . $buildedQuery;
			return $buildedUrl;
		} 
		
	}

	public static function init() {
		$url = array();

		$baseURL = explode("/", SBJ::config("baseURL"));
		$baseURL = array_diff($baseURL, array(''));
		if(($request = self::getRequestUri()) !== null) {

			$url = explode("/", $request);	
		} 
		

		while(($key = array_search("", $url)) !== false) {
			unset($url[$key]);
		}
		sort($url, SORT_NUMERIC);
		$mailURL = array_slice($url, count($baseURL), 2);

		if(!isset($mailURL[0])) $mailURL[0] = SBJ::config("defaultController");
		if(!isset($mailURL[1])) $mailURL[1] = SBJ::config("defaultAction");
		define('CONTROLLER', ucfirst($mailURL[0]));
		define('ACTION', $mailURL[1]);

		self::$_url = array_splice($url, 2);
	}


	public static function get($number) {
		return @self::$_url[$number];
	}

	public static function getRequestUri() {


		if(isset($_SERVER['PATH_INFO'])) return $_SERVER['PATH_INFO'];
		if(isset($_SERVER['REQUEST_URI'])) {
			$uri = $_SERVER['REQUEST_URI'];
			if($pos = strripos($uri, "?")){
				$uri = substr($uri, 0, $pos);
			}
			return $uri;
		}

		return null;
	}
}
?>