<?php

/**
 * @description Mutex testing
 */
class LeptonMutexTest extends LunitCase {

	private $mutex;
	
	function __construct() {
		using('lepton.system.mutex');
		mutex::void();
	}
	
	/**
	 * @description Acquiring a mutex lock
	 */
	function mutexcreate() {
		$this->mutex = new Mutex("lunitlock");
	}
	
	/**
	 * @description Reacquiring same lock
	 */
	function mutexrecreate() {
		try {
			$mutextest = new Mutex("lunitlock",100);
		} catch (MutexException $e) {
			unset($mutextest);
			$this->assertTrue(true);
			return;
		}
		$this->assertTrue(false);
	}
	
	/**
	 * @description Releasing lock through unset()
	 */
	function mutexrelease() {
		unset($this->mutex);
		$this->assertTrue(true);
	}
	
	/**
	 * @description Releasing a lock through mutex->release
	 */
	function mutexreleasemtd() {
		$m = new Mutex('lunitlock',100);
		$m->release();
	}

	function __destruct() {
		mutex::void();
	}

}

Lunit::register('LeptonMutexTest');
