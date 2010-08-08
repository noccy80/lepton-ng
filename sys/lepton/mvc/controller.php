<?php

	interface IController {
		function __request($method,$arguments);
	}

	abstract class Controller implements IController {
		private $_state;
		static function invoke($controller=null,$method=null,Array $arguments=null) {
			if (!$controller) $controller = 'default'; // config
			if (!$method) $method = 'index'; // config
			$ctlpath = BASE_PATH.'app/controllers/'.$controller.'.php';
			if (file_exists($ctlpath)) {
				Console::debugEx(LOG_VERBOSE,__CLASS__,'Invoking controller instance %s (method=\'%s\', args=\'%s\')...', $controller, $method, join('\',\'',(array)$arguments));
				require($ctlpath);
				$cc = $controller.'Controller';
				$ci = new $cc;
				if (!$ci->__request($method,(array)$arguments)) {

				}
			} else {
				throw new BaseException("Could not find controller class ". $controller);
				return 1;
			}
		}
		function __construct() {
			$this->_state = Array();
		}
		function __request($method,$arguments) {
			return call_user_func_array(array($this,$method),$arguments);
		}

		function __set($key,$value) {
			$this->_state[$key] = $value;
		}
		function __get($key) {
			return($this->_state[$key]);
		}
		function __isset($key) {
			return(isset($this->_state[$key]));
		}
		function __unset($key) {
			unset($this->_state[$key]);
		}
	}

?>
