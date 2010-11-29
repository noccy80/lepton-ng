<?php

class CssRule {
    private $selector;
    private $attributes;
    function __construct($selector,$attributes) {
        $this->selector = $selector;
        $attrarr = array();
        foreach((array)$attributes as $attr=>$val) {
            $newattr = string::strip($attr, string::CHS_ALPHA);
            $attrarr[$attr] = $val;
        }
        $this->attributes = $attrarr;
    }
    function __toString() {
        $rules = array();
        foreach($this->attributes as $key=>$rule) {
            $rules[] = $key.':'.$rule.';';
        }
        $rulestr = '{'.join(',',$rules).'}';
        $ret = $this->selector.$rulestr;
        return $ret;
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

class CssStylesheet {
    private $rules = array();
    function output() {
        response::contentType('text/css');
        echo $this->__toString();    
    }
    function addRule(CssRule $rule) {
        $this->rules[$rule->getSelector()] = $rule;
    }
    function __toString() {
        $rules = array();
        foreach($this->rules as $rule) {
            $rules[] = (string)$rule;
        }
        return (string)join(' ',$rules);
    }
}

