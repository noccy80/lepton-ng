<?php

class LeptonVartypeTest extends LunitCase {

	function stringtest() {

		$s = vartype::string()
			->required()
			->defaultvalue('foo');
		$this->assertTrue($s->getRequired());
		$this->assertEquals($s->getDefault(),'foo');

	}

	function floattest() {

		$s = vartype::float()
			->nullable()
			->defaultvalue('foo');
		$this->assertFalse($s->getRequired());
		$this->assertEquals($s->getDefault(),'foo');

	}

}

Lunit::register('LeptonVartypeTest');
