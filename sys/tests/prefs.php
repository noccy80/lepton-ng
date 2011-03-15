<?php

/**
 * @description Preferences Storage
 */
class LeptonPrefsTests extends LunitCase {

	function __construct() {
		using('lepton.utils.prefs');
	}

	function fsprefs() {
		$s = new FsPrefs('/tmp/fsprefs.tmp');
		$this->assertNotNull($s);
		$s->foo = 'bar';
		unset($s);
		$s = new FsPrefs('/tmp/fsprefs.tmp');
		$this->assertNotNull($s);
		$this->assertEqual($s->foo, 'bar');
		unset($s);
		unlink('/tmp/fsprefs.tmp');
	}

	function jsonprefs() {
		$s = new JsonPrefs('/tmp/jsonprefs.tmp');
		$this->assertNotNull($s);
		$s->foo = 'bar';
		unset($s);
		$s = new JsonPrefs('/tmp/jsonprefs.tmp');
		$this->assertNotNull($s);
		$this->assertEqual($s->foo, 'bar');
		unset($s);
		unlink('/tmp/jsonprefs.tmp');
	}

}

Lunit::register('LeptonPrefsTests');
