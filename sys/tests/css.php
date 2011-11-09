<?php

using('lunit.*');

/**
 * @description CSS Tools
 */
class LeptonCssTests extends LunitCase {

    private $stylesheet = null;

    function __construct() {
        using('lepton.web.css');
    }

    /**
     * @description Creating a stylesheet
     */
    function testgenerate() {
        $this->stylesheet = new CssStylesheet();
        $this->assertNotNull($this->stylesheet);
    }
    
    /**
     * @description Adding rules
     */
    function addrules() {
        $this->stylesheet->addRule(new CssRule( 'foo', array( 'bar' => 'baz' ) ));
        $this->assertEquals($this->stylesheet->getRule('foo'), 'foo{bar:baz;}');
    }

    /**
     * @description Replacing rules
     */
    function replacerules() {
        $this->stylesheet->addRule(new CssRule( 'foo', array( 'bar' => 'bin' ) ));
        $this->assertEquals($this->stylesheet->getRule('foo'), 'foo{bar:bin;}');
    }

    /**
     * @description Overwriting rules
     */
    function overwriterules() {
        $this->stylesheet->addRule(new CssRule( 'foo', array( 'bar' => 'bin', 'bar' => 'ben' ) ));
        $this->assertEquals($this->stylesheet->getRule('foo'), 'foo{bar:ben;}');
    }

    
}

Lunit::register('LeptonCssTests');
