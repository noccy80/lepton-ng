<?php

/**
 * @description Base tests for Lepton Application Platform
 */
class BaseTests extends LunitCase {

	/**
	 * @description Simple test
	 */
	function simple() {
		$this->assertTrue(false);
	}

}

Lunit::register('BaseTests');
