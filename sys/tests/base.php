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
		$this->assertEquals(__fmt(array('foo%s')),'foo%s');
		$this->assertEquals(__fmt(array('foo%s','bar')),'foobar');
	}
	
	/**
	 * @description __filepath() and __fileext() shorthands
	 */	
	function filepaths() {
		$this->assertEquals(__fileext('/foo/bar.baz'),'baz');
		$this->assertEquals(__filepath('/foo/bar.baz'),'/foo');
	}
	
	/**
	 * @description file_find() to locate file with globbing
	 */
	function filefind() {
		$this->assertEquals(file_find(base::basePath(),'base.php'), base::basePath().'sys/base.php');
	}

	/**
	 * @description file_find() on a bad path returning null
	 */
	function filefindbaddir() {
		$this->assertNull(file_find('/foo/bar','baz'));
	}

}

Lunit::register('LeptonBaseTests');
