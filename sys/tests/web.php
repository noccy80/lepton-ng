<?php

using('lunit.*');
using('lepton.web.url');

class LeptonWebTests extends LunitCase {

	/**
	 * @description Parsing URLs and setting properties
	 */
	function url() {

		$u = new url('http://www.google.com?foo=bar');
		$u->setParameter('baz','bin');
		$this->assertEquals($u->toString(),'http://www.google.com?foo=bar&baz=bin');

	}

}

Lunit::register('LeptonWebTests');
