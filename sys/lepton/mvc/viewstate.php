<?php

class ViewState {

	private $stateid = null;
	private $state = array();
	private static $pstate = array();

	function __construct($stateid=null) {
		if ($stateid == null) {
			$this->state = self::$pstate;
		} else {
			if (session::has('viewstate_'.$stateid)) {
				$this->state = session::get('viewstate_'.$stateid);
			} else {
				throw new BaseException("No viewstate found with id");
			}
		}
	}

	function __get($key) {
		return $this->get($key);
	}

	function get($key,$default=null) {
		if (isset($this->state[$key])) return $this->state[$key];
		return $default;
	}

	function __set($key,$value) {
		$this->set($key,$value);
	}

	function set($key,$val) {
		$this->state[$key] = $val;
		if ($this->stateid==null) self::$pstate[$key]=$val;
	}

	function save() {
		$this->stateid = 'viewstate_'.uniqid();
		session::set('viewstate_'.$this->stateid,$this->state);
		return $this->stateid;
	}

}

function viewstate($id=null) {
	return new ViewState($id);
}
