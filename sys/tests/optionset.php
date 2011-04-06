<?php

using('lunit.*');
using('lepton.utils.optionset');


/**
 * @description OptionSet Helper
 */
class LeptonOptionsetTests extends LunitCase {

	private $os = null;
	private $osd = null;

	function __construct() {
	}

	/**
	 * @description Creating optionset without defaults
	 */
	function optclean() {
		$this->os = new OptionSet(array('foo' => 'bar', 'baz' => 'bin'));
		$this->assertNotNull($this->os);
	}

	/**
	 * @description Creating optionset with defaults
	 */
	function optdefaults() {
		$this->osd = new OptionSet(array('foo' => 'bar', 'baz' => 'bin'), array('xyzzy' => 'omg'));
		$this->assertNotNull($this->osd);
	}

	/**
	 * @description Accessing via properties without defaults
	 */
	function opttestclean() {
		$this->assertEquals($this->os->foo,'bar');
		$this->assertEquals($this->os->xyzzy,null);
	}

	/**
	 * @description Accessing via properties with defaults
	 */
	function opttestdefaults() {
		$this->assertEquals($this->osd->foo,'bar');
		$this->assertEquals($this->osd->xyzzy,'omg');
	}

	/**
	 * @description Accessing via get() without defaults
	 */
	function opttestgetclean() {
		$this->assertEquals($this->os->get('foo'),'bar');
		$this->assertEquals($this->os->get('xyzzy'),null);
		$this->assertEquals($this->os->get('xyzzy','omg'),'omg');
	}

	/**
	 * @description Accessing via properties with defaults
	 */
	function opttestgetdefaults() {
		$this->assertEquals($this->osd->get('foo'),'bar');
		$this->assertEquals($this->osd->get('xyzzy'),'omg');
		$this->assertEquals($this->osd->get('xyzzy','xing'),'xing');
	}
}

Lunit::register('LeptonOptionsetTests');
