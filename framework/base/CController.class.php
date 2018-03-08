<?php
class CController {
	// Before using the controller, you need to make sure that it is really a controller if we do not use a suffix or a prefix
	public $isController = true;
	private $_view;
	
	private $_files = [];

	private $_assign = [];
	protected $lang;

	public function __construct() {
		$this->lang = SBJ::lang();
	}
	protected function render($arr=[], $name=null) {
		$this->_view = new CView(array_merge(get_object_vars($this), $arr, $this->_assign), $this->_files );
		if(null === $this->_view) $this->_view->title = ucfirst(ACTION);

		$this->_view->view($name);
	}


	protected function addClientScript($path) {
		$this->_files[] = $path;
	}

	protected function redirect($url) {
		header('Location: ' . $url);
	}

	protected function back() {
		if (@$_SERVER['HTTP_REFERER'] != null) {
            header("Location: ".$_SERVER['HTTP_REFERER']);
            exit;
        }
	}

	protected function assign($name, $value) {
		$this->_assign[$name] = $value;
	} 

}