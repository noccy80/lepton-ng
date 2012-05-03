<?php

/**
 * @deprecated To be replaced by delegate
 */
class Callback {
    private $cbarray = null;
    private $cbfixed = null;
    function __construct(&$object,$method) {
        $this->cbarray = array($object,$method);
        $args = func_get_args();
        if (count($args)>2) { $this->cbfixed = array_slice($args,2); }
    }
    function call() {
        $args = func_get_args();
        return call_user_func_array($this->cbarray,array_merge((array)$this->cbfixed,$args));
    }
}
function cb(callback $cb = null) {
    $args = func_get_args();
    if ($cb) call_user_func_array(array($cb,'call'),array_slice($args,1));
}
// Semantic prettification method
function callback(&$object,$method) { 
    // return array($o,$m);
    $args = func_get_args();
    return new Callback($object,array_slice($args,1));
}