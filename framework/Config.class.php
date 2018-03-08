<?php 
class Config extends CModel {
	const IMPORT_MERGE = 1;
	const IMPORT_HARD = 2;

	private $_file;
	private $_isSaved;

	public function init($file=CONFIG_PATH) {
		if(file_exists(CONFIG_DIR . '/' . $file)) {
			$this->_file = CONFIG_DIR . '/' . $file;
			$data = include $this->_file;
			$this->import($data);
			$this->_isSaved = true;

		} else throw new Exception("Config ". $file ." not found!", 404);	
	}

	public function __destruct() {
		if(!$this->_isSaved) {
			$this->save();
		}
	} 

	public function import($data, $type = Config::IMPORT_MERGE) {
		$this->_isSaved = false;
		if($type === Config::IMPORT_MERGE) $this->_data = array_merge($this->_data, $data);
		elseif($type === Config::IMPORT_HARD) $this->_data = $data;
		else throw new Exception("Invalid type import config", 500);
		
	}
	public function export() {
		return $this->_data;
	}

	public function __set($name, $val) {
		$this->_isSaved = false;
		$this->_data[$name] = $val;
	}

	public function __get($name) {
		
		return $this->_data[$name];
	}


	public function save() {
		$code = "<?php \r\nreturn [\r\n";
		foreach ($this->_data as $key => $value) {

			$code .= "	'". $key ."' => " . "'". $value ."'," . "\r\n";
		}
		$code = substr($code, 0, -1); 
		$code .= "];";
		if(file_put_contents($this->_file, $code) !== false) {
			$this->_isSaved = true;
			return true;
		} else return false;
	}

	

	
}