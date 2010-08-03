<?php

	class ClassDelegate {

		private $callable;

		function __construct($delegates=null) {
			$args = func_get_args();
			$this->callable = $args;
		}

		function __call($method,$arguments) {
			foreach($this->callable as $classes) {
				@call_user_func_array(array($classes,$method),(array)$arguments);
			}
		}

	}

	class Delegate {

		private $callable;

		function __construct($delegates=null) {
			$args = func_get_args();
			$this->callable = $args;
		}

		function call($arguments) {
			foreach($this->callable as $classes) {
				@call_user_func_array($classes,(array)$arguments);
			}
		}

	}

	class DelegateTest1 {
		function testfunc1($foo,$bar) {
			Console::debug('Testing 1: %s %s', $foo, $bar);
		}
	}
	class DelegateTest2 {
		function testfunc1($foo,$bar) {
			Console::debug('Testing 2: %s %s', $foo, $bar);
		}
	}
	function freebeer($foo,$bar) { Console::debug('Free beer for %s %s', $foo, $bar); }

	$d = new Delegate(
		array('DelegateTest1','testfunc1'),
		array('DelegateTest2','testfunc1'),
		'freebeer'
	);
	$d->call('foo','bar');

?>
