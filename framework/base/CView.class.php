<?php
class CView {

	private $_data = [
		'title' => 'unknown'
	];
	private $_layout;
	private $_context;
	private $_addonBody = '';
	static private $_files = [];
	static private $_langsign;

	/**
	* 
	* @param mixed[] $arr Array for use in view file, use $this->[param].
	*/
	public function __construct($arr=[], $files=[]) {
		self::$_files = array_merge(self::$_files, $files);
		$this->_data['title'] = SBJ::config('projectName');
		
		
		$this->_data = array_merge($arr, $this->_data);
		$lang = SBJ::lang();
		self::$_langsign = md5( @md5_file($lang->getPath(CONTROLLER, ACTION)) . 
								@md5_file($lang->getCommonPath()) );


	}


	/**
	* Output content from view file in monitor.
	* @param string $name Path to the view file.
	*/
	public function view($name=null) {
		$html = "";
		$this->_context = $this->_getContext($name);
		
		
		if(null === $this->_layout) {
			$html = $this->_context;
		} else {
			$html = $this->_getContext($this->_layout);
		}


		
		if(count(self::$_files) > 0) {

			foreach (self::$_files as $key => $value) {
				
		
		        $this->_addonBody .= "<script type=\"text/javascript\" src=\"". $this->assets() . "/" . $value ."\"></script>\n";
			}
		}
		if(false !== $pos = stripos($html, '</body>')) {
			$html = substr_replace($html, $this->_addonBody ."</body>", $pos);
		}

		
		echo $html;
	}
	
	public function widget($name, $arr=[]) {

		$widget = null;
		$class = SBJ::config('widgetPrefix') . ucfirst($name) . SBJ::config('widgetSuffix');
		
		if(is_file($path = APP . '/' . SBJ::config('controllerDir') . '/' . $class . '.class.php')) {
			$widget = new $class($name, $arr);
		} else {
			$widget = new CWidget($name, $arr);
		}
		$widget->init($arr);
	}

	/**
	* Get Context by view file and return this.
	* @param string $name Path to the view file.
	* @return string HTML code
	*/
	private function _getContext($name) {
		$name = (null != $name ? $name : CONTROLLER . "/" . ACTION);

		// If only the action is specified, add the current controller
		$tmp = explode("/", $name);
		if(!isset($tmp[1])) $name = CONTROLLER . "/" . $tmp[0];


		if(is_file($path = APP . "/" . SBJ::Config("viewDir") . "/". $name .".php")) {			

			$cache = self::getCachePath($path, self::$_langsign);
			ob_start();
			if(file_exists($cache)) {
				include $cache;
			} else {
				if(!is_dir(RUNTIME)) {
					mkdir(RUNTIME);
				}
				if(!is_dir($dircache = (RUNTIME . '/cache/'))) {
					mkdir($dircache);
				}

				$code = file_get_contents($path);
				
				
				$code = preg_replace_callback('|{{(.*)}}|', function($m) {
					$lang = SBJ::lang();
					$r = substr($m[0], 2, strlen($m[0])-4);
					if(substr($r, 0, 1) == "$") {
						return '<?php echo $this->'. substr($r, 1) .'; ?>';
					} else if(substr($r, 0, 1) == ":") { 
						return '<?php echo '. substr($r, 1) .'; ?>';
					} else {
						return $lang->$r;
					}
				}, $code);

				file_put_contents($cache, $code);
				include $cache;
			}
        	$html = ob_get_clean();
        	return $html;	
		} else return null;
	}

	/**
	* Designed to be performed inside a layout. At the location of the function, the context of the view is.
	*/
	private function showContext() {
		echo $this->_context;
	}
	


	/**
	* Designed to perform in a view. Sets the path to the template.
	* @param string $name Path to the view file.
	*/
	private function setLayout($name) {
		$this->_layout = $name;
	}

	/**
	* Get link to asset dir
	* @return string $path
	*/
	public static function assets() {
		return SBJ::Config("baseURL") . "/". SBJ::Config("assets");
	}
	public function __set($key, $val) {
		return $this->_data[$key] = $val;
	}

	public function __get($key) {
		return (isset($this->_data[$key])? $this->_data[$key] : null);
	}

	public static function getCachePath($path, $sign=null) {
		return RUNTIME . '/cache/'. md5(md5_file($path) . $sign) .".php";
	} 

	public static function addClientScript($path) {
		if(!in_array($path, self::$_files)) {
			self::$_files[] = $path;
		}
	}
}