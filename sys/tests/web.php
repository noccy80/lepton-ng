<?php

using('lunit.*');

class LeptonWebTests extends LunitCase {

    /**
     * @description Parsing URLs and setting properties
     */
    function url() {

        using('lepton.web.url');

        $u = new url('http://www.google.com?foo=bar');
        $u->setParameter('baz','bin');
        $this->assertEquals($u->toString(),'http://www.google.com?foo=bar&baz=bin');

    }

    /**
     * @description Service Discovery
     */
    function discover() {

        using('lepton.net.httprequest');
        using('lepton.web.discovery');
        $d = Discovery::discover('http://127.0.0.1');
        $this->assertNotNull($d);

    }

}

Lunit::register('LeptonWebTests');
