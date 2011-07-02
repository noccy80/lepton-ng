<?php

class CliDebugProvider implements IDebugProvider {

    function inspect($data,$table=false) {

        $maxlenk = 16;
        $maxlent = 8;
        foreach($data as $k=>$v) {
            if (strlen($k)>$maxlenk) $maxlenk = strlen($k);
            if (strlen(typeOf($v))>$maxlent) $maxlent = strlen(typeOf($v));
        }
        $maxlent+= 2;
        foreach($data as $k=>$v) {
            console::writeLn('%-'.$maxlent.'s %-'.$maxlenk.'s = %s', '['.typeOf($v).']', $k, $v);
        }
        // var_dump($data);

    }

}

debug::setDebugProvider(new CliDebugProvider());
