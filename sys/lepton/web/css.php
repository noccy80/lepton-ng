<?php

class CssRule {
    private $selector;
    private $attributes;
    function __construct($selector,$attributes) {
        $this->selector = $selector;
        $this->attributes = $attributes;
    }
    function __toString() {
        $rules = array();
        foreach($this->attributes as $key=>$rule) {
            $rules[] = $key.':'.$rule.';';
        }
        $rulestr = '{'.join(',',$rules).'}';
        $ret = $this->selector.$rulestr;
    }
    function __set($key,$value) {
        $this->attributes[$key] = $value;
    }
    function __get($key) {
        return $this->attributes[$key];
    }
    function getSelector() {
        return $this->selector;
    }
    function setSelector($selector) {
        $this->selector = $selector;
    }
}

