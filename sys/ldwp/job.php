<?php

using('lepton.crypto.uuid');

interface ILdwpJob {
	function start();
}

abstract class LdwpJob {

	const STATE_QUEUED = 0;
	const STATE_ERROR = 1;
	const STATE_RUNNING = 2;
	const STATE_SUSPENDED = 3;

	protected $_state = 0;
	protected $_message = null;
	protected $_progress = null;
	protected $_id = null;

	public function __construct() {
		$this->setState(LdwpJob::STATE_QUEUED);
		$this->setProgress("Job Queued",0,1);
		$this->_id = uuid::v4();
	}

	public function getId() {
		return $this->_id;
	}

	protected function setState($state) {
		$this->_state = $state;
	}

	protected function setProgress($message,$current,$max) {
		$this->_message = $message;
		$this->_progress = array($current, $max);

	}

}
