<?php

class LunitAssertionFailure extends Exception { }

abstract class LunitCase {
	
	protected function assertEqual($val,$test) { 
		if ($val != $test) {
			throw new LunitAssertionFailure(
				sprintf("assertEqual(): %s != %s",  __printable ($val),__printable($test))
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