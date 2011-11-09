<?php

using('lunit.*');

/**
 * @description Delegates and Events
 */
class LeptonDelegatesTests extends LunitCase {

    private $delegate = null;
    private $results = array();

    function __construct() {
        using('lepton.system.delegates');
    }

    function _dlgcb($word) {
        $this->results[] = $word;
    }

    /**
     * @description Creating delegate chain
     */
    function delegatecreate() {
        $this->delegate = new Delegate();
        $this->assertNotNull($this->delegate);
    }

    /**
     * @description Attaching delegates to chain
     */
    function delegateattach() {
        $this->delegate->addDelegate(array(&$this,'_dlgcb'));
        $this->delegate->addDelegate(array(&$this,'_dlgcb'));
    }

    /**
     * @description Calling delegate
     */
    function delegatecall() {
        $this->delegate->call("foo");
        $this->assertTrue(count($this->results) == 2);
        $this->assertEquals($this->results[0], "foo");
        $this->assertEquals($this->results[0], $this->results[1]);
    }

}

Lunit::register('LeptonDelegatesTests');
