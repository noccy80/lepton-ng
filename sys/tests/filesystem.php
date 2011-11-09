<?php

using('lunit.*');

/**
 * @description Filesystem test
 */
class LeptonFsTests extends LunitCase {

    function __construct() {
        using('lepton.fs.*');
    }

    /**
     * @description Filesystem Factory test (FsObject)
     */
    function factory_test() {
        $f = FsObject::get(base::appPath());
        $this->assertNotNull($f);
        $this->assertEquals(typeof($f),'FsDirectory');
    }

    function paths() {
        $f = FsObject::get(base::appPath());
        $path = $f->getDirname();
        $this->assertNotNull($path);
    }

    function usage() {
        $f = FsObject::get(base::appPath());
        $usage = $f->getDiskUsage(true);
        $this->assertNotNull($usage);
        $this->assertEquals(typeof($usage),'array');
    }

    function joininigpaths() {
        $f = FsObject::get(base::appPath());
        $p1 = $f->joinPath('foo/bar');
        $p2 = $f->joinPath(array('foo','bar'));
        $this->assertEquals($p1,$p2);
    }

}

Lunit::register('LeptonFsTests');

