<?php

	interface IController {
		function __request($method,$arguments);
	}

	class Controller implements IController {
		private $_state;
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
