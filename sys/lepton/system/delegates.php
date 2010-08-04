<?php

	class DelegateBase {

		protected $callable;

		function __construct($delegates=null) {
			$args = func_get_args();
			$this->callable = $args;
		}

		function add($delegate) {
			$this->callable[] = $delegate;
		}

	}

	class ClassDelegate extends DelegateBase {


		function __call($method,$arguments) {
			Console::debugEx(LOG_DEBUG1,__CLASS__,"ClassDelegate invocation for %s",$method);
			foreach($this->callable as $classes) {
				@call_user_func_array(array($classes,$method),(array)$arguments);
			}
		}

	}

	class Delegate extends DelegateBase {

		function __construct($delegates=null) {
			$args = func_get_args();
			$this->callable = $args;
		}

		function call($arguments) {
			Console::debugEx(LOG_DEBUG1,__CLASS__,"Delegate invocation");
			foreach($this->callable as $classes) {
				@call_user_func_array($classes,(array)$arguments);
			}
		}

	}

?>
