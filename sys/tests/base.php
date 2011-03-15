<?php

using('lunit.*');

/**
 * @description Base tests for Lepton 1.0 (ng)
 */
class LeptonBaseTests extends LunitCase {

	/**
	 * @description Test of fnmatch() functionality
	 */
	function testfnmatch() {
		$this->assertTrue(fnmatch("foo.*","foo.bar"));
		$this->assertFalse(fnmatch("bar.*","foo.bar"));
		$this->assertTrue(fnmatch("*.bar","foo.bar"));
	}
	
	/**
	 * @description Test of sys_getloadavg() functionality
	 */
	function testloadavg() {
		$this->assertNotNull(sys_getloadavg());
	}

	/**
	 * @description Test of __fmt() shorthand
	 */
	function testfmt() {
		$this->assertEquals(__fmt('foo%s'),'foo%s');
		$this->assertEquals(__fmt('foo%s','bar'),'foobar');
	}
	
	/**
	 * @description __filepath() and __fileext() shorthands
	 */	
	function filepaths() {
		$this->assertEquals(__fileext('/foo/bar.baz'),'baz');
		$this->assertEquals(__filepath('/foo/bar.baz'),'/foo');
	}

}

Lunit::register('LeptonBaseTests');
