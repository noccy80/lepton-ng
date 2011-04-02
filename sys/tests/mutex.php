<?php

using('lunit.*');

/**
 * @description Mutex testing
 */
class LeptonMutexTest extends LunitCase {

	private $mutex;
	
	function __construct() {
		using('lepton.system.mutex');
	}
	
	/**
	 * @description Acquiring a mutex lock
	 */
	function mutexcreate() {
		mutex::void();
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
	 * @description Releasing lock through mutex->release()
	 */
	function mutexreleasemtd() {
		$m = new Mutex('lunitlock',100);
		$m->release();
	}

	/**
	 * @description Cleaning up locks
	 */
	function cleanup() {
		mutex::void();
	}

}

Lunit::register('LeptonMutexTest');
