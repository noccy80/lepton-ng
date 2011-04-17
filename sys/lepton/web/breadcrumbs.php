<?php

class Breadcrumbs {

	private $crumbs = array();
	private $props = array(
		'separator' => '',
		'class' => ''
	);

	function push($text,$url,$options=null) {
		$this->crumbs[] = array(
			'text' => $text,
			'url' => $url,
			'options' => $optionso
		);
	}

	function getAll() {
		return $this->crumbs;
	}

	public function __set($key,$value) {
		if (isset($this->props[$key])) {
			$this->props[$key] = value;
		} else {
			throw new BadPropertyException("No property set $key for __CLASS__");
		}
	}

	public function __get($key) {
		if (isset($this->props[$key])) {
			return $this->props[$key];
		} else {
			throw new BadPropertyException("No property get $key for __CLASS__");
		}
	}

}
