<?php

class S2SRequest implements ArrayAccess, IteratorAggregate, Countable {
    const RT_HTTP = 'http';
    const RT_XMLRPC = 'xmlrpc';
    const RT_JSON = 'json';
    private $data = array();
    private $cmd = null;
    private $type = null;
    public function __construct($requesttype=self::RT_HTTP,$cmd,Array $data) {
        // Figure
        $this->cmd = $cmd;
        $this->data = $data;
        $this->type = $requesttype;
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
    public function getTransport() {
        return $this->type;
    }
    public function inspect() {
        debug::inspect($this->data);
    }
}
