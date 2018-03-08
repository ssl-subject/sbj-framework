<?php

class SBJ {
	static private $_config;
	static private $_lang;
	const VERSION = "1.0.0";

	/**
	* Method for launching the application.
	* @param mixed[] $config Path to config file.
	*/
	public static function start($config) {
		try {

			set_error_handler(function ($err_severity, $err_msg, $err_file, $err_line, array $err_context) {
			    if (0 === error_reporting()) { return false;}
			    if(!self::_initError($err_msg, $err_file, $err_line)) {
			    	throw new Exception ($err_msg . " | File: ". $err_file ."(". $err_line .")", 0);
			    }
				
			});

			self::$_config = include $config;

			// Missing htaccess web server files
			self::_initHtaccess();
			
			// Checks if PHP is greater than 7
			self::_initPHP();

			define("FRAMEWORK", __DIR__);
			define('APP', self::config('appDir'));
			define("RUNTIME", $_SERVER["DOCUMENT_ROOT"] . "/" . SBJ::config("runtimeDir"));
			define("ROOT", $_SERVER["DOCUMENT_ROOT"]);
			define("CONFIG_DIR", dirname($config));
			define("CONFIG_PATH", $config);

			if (!defined( "PATH_SEPARATOR" )) { 
				if (strpos( $_ENV[ "OS" ], "Win") !== false ) 
					define( "PATH_SEPARATOR", ";" ); 
				else define( "PATH_SEPARATOR", ":" ); 
			} 
			set_include_path(APP . self::config('controllerDir') .'/'. PATH_SEPARATOR . APP . self::config('modelDir') .'/'. PATH_SEPARATOR. FRAMEWORK . '/'. PATH_SEPARATOR . FRAMEWORK . '/base/');
			spl_autoload_register(function ($class) {
				include $class . '.class.php';
			});

			// Parse URL and write controller and action to constants (CONTROLLER, ACTION)
			URL::init();

			$error = explode("/", self::config("error_404"));


			if(false === self::_initAction(CONTROLLER, ACTION) && false == self::_initAction($error[0], $error[1])) {
				throw new Exception("Not Found ". CONTROLLER . " / " . ACTION, 404);
			};

		} catch (Exception $e) {
			self::_displayError($e);
		} catch (Error $e) {
			self::_displayError($e);
		} catch (Throwable $e) {
			self::_displayError($e);

		}
	}

	/**
	* Method for displaying exceptions.
	* @param objectstring $e The object by "throw".
	*/
	private static function _displayError($e) {
		
		if(!self::_initError($e->getMessage(), $e->getFile(), $e->getLine())) {
			header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
			echo "<pre><h1>Exception</h1>" . $e->getMessage() . " (". $e->getCode() .")";
			echo "<br>". $e->getFile() . " | Line: ". $e->getLine();
			echo "<br><br>Trace:<br>" . $e->getTraceAsString();
			echo "<br><br><hr>". $_SERVER["SERVER_SIGNATURE"] . " &#169; SBJ Framework";
			echo "</pre>";	
		}
		
		exit();
	}
	

	private static function _initError($msg, $file, $line) {
		$error = SBJ::config("error");
		$error = explode("/", $error);
		/*if(count($error) == 2 && class_exists($error[0]) && method_exists( $errorClass = (new $error[0]) , $error[1])) {
			$errorClass->$error[1]($msg, $file, $line);
			return true;
		} else return false;*/
		
		return false;
	}

	/**
	* Initialize Action
	* @param string $controller Name of the Controlller.
	* @param string $action Name of the Action.
	* @return bool
	*/
	private static function _initAction($controller, $action) {
		$className = (self::config("controllerPrefix") . $controller . self::config("controllerSuffix"));


		if(!class_exists($className)) {

			return false;
		} else {
			$class = new $className;
			
			$GLOBALS["CONTROLLER_NOW"] = $class;

			self::initCommon();

			if(!isset($class->isController)) throw new Exception("Security error");
			
			if(!method_exists($class, $action)) {
				return false;
			} else {
				$reflection = new ReflectionMethod($class, $action);
			    $pass = [];
			    foreach($reflection->getParameters() as $param) {
			      if(isset($_REQUEST[$param->getName()])) {
			        $pass[] = $_REQUEST[$param->getName()];
			      } else if($param->isOptional()) {
			        $pass[] = $param->getDefaultValue();
			      } else {
			     	throw new Exception("Required parameter \"". $param->getName() ."\" not passed.");
			      }
			    }

			    $stop = false;
			    if(method_exists($class, "parentBeforeAction")) {
					if(!$class->parentBeforeAction($action)) $stop = true;
				}
			    if(method_exists($class, "beforeAction")) {
					if(!$class->beforeAction($action)) $stop = true;
				}
				
				if(!$stop) {
					$result = $reflection->invokeArgs($class, $pass);

				    if(method_exists($class, "parentAfterAction")) {
						$class->parentAfterAction($action);
					}

					if(method_exists($class, "afterAction")) {
						$class->afterAction($action);
					}
				    if($result != null) {
				    	header('Content-Type: application/json');
				    	echo (is_string($result)?$result:json_encode($result));
				    }
				}
			    return true;
			}
		}
	}

	private static function _initHtaccess() {
		if(!is_file(__DIR__ . '/.htaccess')) {
			file_put_contents(__DIR__ . '/.htaccess', 'deny from all');
		}
		if(!is_file($_SERVER['DOCUMENT_ROOT'] . '/.htaccess')) {
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/.htaccess', "RewriteEngine on\r\nRewriteCond %{REQUEST_FILENAME} !-d\r\nRewriteCond %{REQUEST_FILENAME} !-f\r\nRewriteRule . index.php [L]");
		}
	}


	private static function _initPHP() {
		if (!defined('PHP_VERSION_ID')) {
		    $version = explode('.', PHP_VERSION);
		    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
		}
		if(PHP_VERSION_ID < 70000) throw new Exception("PHP must be at least 7 versions", 0);
	}


	public static function config($val1, $val2=null) {
		return @(null === $val2 ? self::$_config[$val1] : self::$_config[$val1][$val2]);
	}

	public static function lang() {
		if(self::$_lang == null) {
			self::$_lang = new CLang;
		} 
		return self::$_lang;
	}

	public static function isAjax() {
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') return true;
		else return false;
	}

	public static function initCommon() {
		$common = scandir(APP . "/" . SBJ::config("commonDir"), SCANDIR_SORT_NONE);

		foreach ($common as $key => $value) {
			if(preg_match("/^\.(.*)/", $value)) continue;
			 
			if(is_file($path = APP . "/" . SBJ::config("commonDir") . "/" . $value)) require_once $path;
			else if(!ctype_upper(substr($value, 1,2)) && substr($value, 0,1) != "C") $value = "C". $value;
			
			require_once APP . "/" . SBJ::config("commonDir") . "/" . $value;
		}
	}

	public static function isMobile() {
		if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$_SERVER['HTTP_USER_AGENT'])||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($_SERVER['HTTP_USER_AGENT'],0,4))) return true;
		return false;


	}


}
