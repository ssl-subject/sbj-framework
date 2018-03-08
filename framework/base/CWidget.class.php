<?php 

class CWidget {

	protected $name = null;
	protected $data;

	public function __construct($name, $data=[]) {
		if($this->name === null) $this->name = $name;
		$this->data = $data;
	}

	public function __set($key, $val) {
		return $this->_data[$key] = $val;
	}
	public function __get($key) {
		return (isset($this->$key)?$this->$key:null);
	}

	public function init($arr) {
		$this->render($arr);
	}
	public function render($arr=[], $name = null) {
		$widget = new CView(array_merge(get_object_vars($this), $arr, $this->data));

		$widget->view( $name === null ? $this->name : $name );
		
	}


}