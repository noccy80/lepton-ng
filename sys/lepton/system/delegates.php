<?php

	/**
	 * @class DelegateBase
	 * @brief Abstract base class for delegates
	 *
	 * Make sure your custom delegates inherit from this class.
	 */
	abstract class DelegateBase {

		protected $callable = array();

		function __construct($delegates=null) {
			$args = func_get_args();
			$this->callable = $args;
		}

		function addDelegate($delegate) {
			$this->callable[] = $delegate;
		}

	}

	/**
	 * @class ClassDelegate
	 * @brief Class delegate class
	 *
	 * Wraps a number of uniform classes and allows calling them simultaneously.
	 */
	class ClassDelegate extends DelegateBase {

		function __call($method,$arguments=null) {
			Console::debugEx(LOG_DEBUG1,__CLASS__,"ClassDelegate invocation for %s",$method);
			foreach($this->callable as $classes) {
				@call_user_func_array(array($classes,$method),(array)$arguments);
			}
		}

	}

	/**
	 * @class Delegate
	 * @brief Function delegate class
	 *
	 * Use as:  $d = new Delegate(array('foo','bar'),array('baz','bin')); $d->call();
	 */
	class Delegate extends DelegateBase {

		function call($arguments=null) {
			Console::debugEx(LOG_DEBUG1,__CLASS__,"Delegate invocation");
			foreach($this->callable as $classes) {
				@call_user_func_array($classes,(array)$arguments);
			}
		}

	}

?>
