<?php

class Process {
	
	private $pid = null;
	
	public function __construct($pid=null) {
		if ($pid != null) {
			$this->pid = intval($pid);
		} else {
			$this->pid = posix_getpid();
		}
	}
	
	public function exists() {
		return (file_exists('/proc/'.$this->pid.'/cmdline'));
	}
	
	public function getPid() {
		return $this->pid;
	}
	
}