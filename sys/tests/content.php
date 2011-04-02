<?php

using('lunit.*');
using('lepton.content.*');

class DummyContentExtension extends ContentExtension {
	private $object;
	function getHandle() { return 'dummy'; }
	function __construct($object=null) { $this->object = $object; }
	function reverse() { 
		$data = $this->object->getHtml();
		$datao = '';
		for($n = strlen($data) - 1; $n >= 0; $n--) {
			$datao = $datao . $data[$n];
		}
		return $datao;
	}
}

class DummyContentObject extends ContentObject {
	private $id;
	function __construct($id) {
		$this->id = $id;
		parent::__construct();
	}
	function getObjectId() {
		return $this->id;
	}
	function getHtml() {
		return $this->id;
	}
	function getUri() {
		return 'dummy:'.$this->id;
	}
}

class DummyContentProvider extends ContentProvider {
	function getNameSpace() { return "dummy"; }
	function getContentFromObjectId($id) { return new DummyContentObject($id); }
}

ContentManager::registerProvider(new DummyContentProvider());
ContentManager::registerExtension(new DummyContentExtension());

/**
 * @description Content Wrappers
 */
class LeptonContentTests extends LunitCase {

	private $co = null;

	function __construct() {
	}

	/**
	 * @description Testing provider for namespace dummy
	 */
	function providertest() {
		$this->co = ContentManager::get('dummy:hello-world');
		$this->assertNotNull($this->co);
		$this->assertEquals($this->co->getHtml(),'hello-world');
	}

	/**
	 * @description Testing extension via dummy->reverse()
	 */
	function extensiontest() {
		$this->assertNotNull($this->co);
		$this->assertEquals($this->co->dummy->reverse(),'dlrow-olleh');
	}
}

Lunit::register('LeptonContentTests');
