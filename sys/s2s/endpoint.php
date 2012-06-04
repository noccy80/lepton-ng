<?php

using('s2s.request');

abstract class S2SEndpointBase {

    function invoke(S2SRequest $request) {
        $cmd = request::get('cmd');
        $func = '_'.str_replace('.','_',$cmd);
        if (is_callable(array($this,$func))) {
            return call_user_func(array($this,$func),array($request));
        }
    }
    
}