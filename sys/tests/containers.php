<?php

class TestContainer extends BasicContainer {
	protected $properties = array(
		'foo' => true
	);
}
class IncompleteTestContainer extends BasicContainer {
}

/**
 * @description Test of container classes
 */
class LeptonContainersTest extends LunitCase {

	/**
	 * @description Testing BasicContainer
	 */
	function basiccontainer() {

		$c1 = new TestContainer();
		$this->assertNotNull($c1);
		$c1->foo = 'bar';
		try {
			$c1->bar = 'baz';
		} catch(Exception $e) {
			$this->assertTrue(true);
			return;
		}
		$this->assertTrue(false);

	}

	/**
	 * @description Testing incomplete BasicContainer
	 */
	function ibasiccontainer() {

		try {
			$c1 = new IncompleteTestContainer();
			$this->assertTrue(false);
		} catch(Exception $e) {
			$this->assertTrue(true);
			return;
		}
		$this->assertTrue(false);

	}

}

Lunit::register('LeptonContainersTest');
