<?php

using('lunit.*');

class LeptonMemcachedTests extends LunitCase {

    function __construct() {
        using('lepton.utils.cache');
    }

    function setcacheitems() {
        cache::set('foo','bar','1m');
    }

    function hitcacheitem() {
        $r = cache::get('foo');
        $this->assertNotNull($r);
    }

    function hitfailcacheitem() {
        try {
            $r = cache::get('bar');
        } catch (CacheException $e) {
        }
    }

}

Lunit::register('LeptonMemcachedTests');
