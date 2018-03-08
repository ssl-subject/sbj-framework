<?php


class CModel {
	protected $db;
	protected $dbConnection = false;
	protected $_data = array();
	protected $autoload = false;

	public function __construct($pk=null) {
	    // parent::__construct();
		if($this->dbConnection) {
			$this->db = new CMysql();
			if($this->autoload !== false && $pk !== null) {
				$tmp = explode(":", $this->autoload);
				$id = "id";
				$table = "undefined";
				if(isset($tmp[1])) {
					$id = $tmp[0];
					$table = $tmp[1];
				} else $table = $tmp[0];

				$this->_data =$this->db->query("SELECT * FROM $table WHERE $id=:id", [
					":id"=>$pk
				])->fetch(PDO::FETCH_ASSOC);
			}
		}

		if(method_exists($this, 'init')) {
			call_user_func_array([$this, 'init'], func_get_args());
		}
	}

	public function __get($name) {
		return (isset($this->_data[$name])?$this->_data[$name]:null);
	}

	public function getData() {
		return $this->_data;
	}
	public function setData($data) {
		return $this->_data = $data;
	}

	public function __set($name, $val) {
		return $this->_data[$name] = $val;
	}

}

?>