<?php

class S2SResponse implements ArrayAccess, IteratorAggregate, Countable {
    const RT_HTTP = 'http';
    const RT_XMLRPC = 'xmlrpc';
    const RT_JSON = 'json';
    private $data = array();
    private $cmd = null;
    public function __construct($requesttype=self::RT_HTTP,$cmd,$data) {
        // Figure
        $this->cmd = $cmd;
        $this->data = $data;
    }
    public function offsetExists($offset) {
        return (arr::hasKey($this->data,$offset));
    }
    public function offsetGet($offset) {
        if (arr::hasKey($this->data,$offset)) 
            return ($this->data[$offset]);
        return null;
        
    }
    public function offsetSet($offset,$value) {
        $this->data[$offset] = $value;
    }
    public function offsetUnset($offset) {
        if (!arr::hasKey($this->data,$offset)) return;
        unset($this->data[$offset]);
        sort($this->data);
    }
    public function count() {
        return count($this->data);
    }
    public function getCommand() {
        return $this->cmd;
    }
    public function getData() {
        return $this->data;
    }
    public function getIterator() {
        return new ArrayIterator($this->data);
    }
    static function createFromRequest(S2SRequest $req) {
        if ($req) {
            $rp = new S2SResponse($req->getTransport(),$req->getCommand());
        } else {
            $rp = new S2SResponse();
        }
        return $rp;
    }    
}
