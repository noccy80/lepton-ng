<?php

using('lunit.*');

/**
 * @description Testing of stream wrappers
 */
class LeptonStreamsTest extends LunitCase {

    private $fn = null;
    private $sw = null;
    private $sr = null;
    private $sc = null;
    
    function __construct() {
        using('lepton.utils.streams');
    }
    
    /**
     * @description Creating a stream for writing
     */
    function writecreate() {
        $this->fn = $this->getTempFile();
        $this->sw = new Stream($this->fn,'w+');
        $this->assertNotNull($this->sw);
    }

    /**
     * @description Writing data to stream
     */
    function writedata() {
        $this->sw->puts("Hello World!");
    }

    /**
     * @description Closing stream
     */
    function writeclose() {
        unset($this->sw);
    }

    /**
     * @description Reopening stream for reading
     */
    function readcreate() {
        $this->rw = new Stream($this->fn,'r');
        $this->assertNotNull($this->rw);
    }

    /**
     * @description Reading data from stream
     */
    function readdata() {
        $data = $this->rw->gets();
        $this->assertEquals($data,'Hello World!');
    }

    /**
     * @description Seeking, telling, and reading again
     */
    function readseek() {
        $this->rw->seek(0);
        $this->assertEquals($this->rw->tell(),0);
        $data = $this->rw->gets();
        $this->assertEquals($data,'Hello World!');
    }

    /**
     * @description Closing stream
     */
    function readclose() {
        unset($this->rw);
    }

}

Lunit::register('LeptonStreamsTest');
