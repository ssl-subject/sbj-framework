<?php 

class CLang {
	private $_lang;
	private $_data;

	public function __construct($name=null) {
		if($name === null) $this->_lang = $this->getMyLang();
		else $this->_lang = $name;
		$this->_data = [];

		if(file_exists($path = $this->getPath(CONTROLLER, ACTION))) {
			$this->_data = json_decode(file_get_contents($path), true);
		} 

		if(file_exists($path = $this->getCommonPath())) {
			$this->_data = array_merge($this->_data, json_decode(file_get_contents($path), true));

		}

	}

	public function __get($name) {
		return (isset($this->_data[$name])?$this->_data[$name]:null);
	}

	public function getMyLang() {
		if(isset($_COOKIE["_lang"])) {
			return $_COOKIE["_lang"];
		} else {
			return SBJ::config("defaultLang");
		}
	}

	public function setMyLang($name) {
		setcookie('_lang', $name, time()+3600*24*365, "/");
	}

	public function getPath($controller, $action) {
		return APP . "/lang/". $this->_lang ."/". $controller . "/". $action . ".json";
	}
	public function getCommonPath() {
		return APP . "/lang/". $this->_lang ."/common.json";
	}
}