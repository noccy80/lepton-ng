<?php

class LunitAssertionFailure extends Exception { }

abstract class LunitCase {

	private $tempfiles = array();

	protected function getTempFile($extension = null) {
		if ($extension) {
			$strext = '.'.$extension;
		} else {
			$strext = '.tmp';
		}
		$tmpdir = sys_get_temp_dir();
		$tmpfil = tempnam($tmpdir,'lunit');
		unlink($tmpfil);
		$tmpfil .= $strext;
		$this->tempfiles[] = $tmpfil;
		return $tmpfil;
	}

	public function __destruct() {
		foreach($this->tempfiles as $tempfile) {
			if (file_exists($tempfile)) {
				// console::writeLn("Unlinking: %s", $tempfile);
				unlink($tempfile);
			}
		}
	}

	protected function assertEquals($val,$test) { 
		if ($val != $test) {
			throw new LunitAssertionFailure(
				sprintf("assertEquals(): %s != %s",  __printable ($val),__printable($test))
			);
		}
	}
	protected function assertNull($test) {
		if ($test != null) {
			throw new LunitAssertionFailure(
				sprintf("assertNull(): %s",  __printable ($test))
			);
		}
	}
	protected function assertNotNull($test) {
		if ($test == null) {
			throw new LunitAssertionFailure(
				sprintf("assertNotNull(): %s",  __printable ($test))
			);
		}
	}
	protected function assertTrue($test) {
		if ($test != true) {
			throw new LunitAssertionFailure(
				sprintf("assertTrue(): %s",  __printable ($test))
			);
		}
	}
	protected function assertFalse($test) {
		if ($test != false) {
			throw new LunitAssertionFailure(
				sprintf("assertFalse(): %s",  __printable ($test))
			);
		}
	}
	
}
