<?php

class OptionSet {
	
	private $options = array();
	
	function __construct(array $options) {
		$this->options = $options;
	}
	
	function get($key,$default=null) {
		if (isset($this->options[$key])) {
			return $this->options[$key];
		} else {
			return $default;
		}
	}
	
	function has($key) {
		return (isset($this->options[$key]));
	}
	
}