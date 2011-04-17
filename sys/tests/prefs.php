<?php

using('lunit.*');

/**
 * @description Preferences Storage
 */
class LeptonPrefsTests extends LunitCase {

	function __construct() {
		using('lepton.utils.prefs');
	}
	
	/**
	 * @description Saving/loading of filesystem-backed storage
	 */
	function fsprefs() {
		$tf = $this->getTempFile();
		$s = new FsPrefs($tf);
		$this->assertNotNull($s);
		$s->foo = 'bar';
		unset($s);
		$s = new FsPrefs($tf);
		$this->assertNotNull($s);
		$this->assertEquals($s->foo, 'bar');
		unset($s);
		// unlink('/tmp/fsprefs.tmp');
	}

	/**
	 * @description Saving/loading of json-backed storage
	 */
	function jsonprefs() {
		$tf = $this->getTempFile();
		$s = new JsonPrefs($tf);
		$this->assertNotNull($s);
		$s->foo = 'bar';
		unset($s);
		$s = new JsonPrefs($tf);
		$this->assertNotNull($s);
		$this->assertEquals($s->foo, 'bar');
		unset($s);
		// unlink('/tmp/jsonprefs.tmp');
	}

	/**
	 * @description Saving/loading of database backed settings
	 */
	function dbprefs() {
		$tf = $this->getTempFile();
		$s = new DbPrefs('prefstest');
		$this->assertNotNull($s);
		$s->foo = 'bar';
		unset($s);
		$s = new DbPrefs('prefstest');
		$this->assertNotNull($s);
		$this->assertEquals($s->foo, 'bar');
		$s->destroy();
		unset($s);
		// unlink('/tmp/jsonprefs.tmp');
	}

	/**
	 * @description Array-based storage (Optionset)
	 */
	function arrayprefs() {
		$s = new ArrayPrefs(array(
			'foo' => 'bar'
		));
		$this->assertEquals($s->foo,'bar');
		$this->assertEquals($s->get('foo'),'bar');
		$this->assertEquals($s->get('bar','baz'),'baz');
	}

}

Lunit::register('LeptonPrefsTests');
