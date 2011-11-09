<?php

using('lpf.object');

class MovieObject extends LpfObject {

    private $_uid = null;
    private $_cache = null;

    public function __construct($movie) {

        parent::__construct($this);

        // Set up a cache for this clip
        $this->_uid = uniqid('mov');
        $this->_cache = sprintf('/tmp/%s',$this->_uid);
        mkdir($this->_cache);
    }
    
    public function __destruct() {
        
        // Empty the cache
        unlink($this->_cache);
        
    }

}
