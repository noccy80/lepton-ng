<?php

class CliDebugProvider implements IDebugProvider {

    function inspect($data,$table=false,$level=0) {
        
        $maxlenk = 30;
        $maxlent = 8;
        foreach($data as $k=>$v) {
            if (strlen($k)>$maxlenk) $maxlenk = strlen($k);
            if (strlen(typeOf($v))>$maxlent) $maxlent = strlen(typeOf($v));
        }
        $maxlent+= 2;
        foreach($data as $k=>$v) {
            if ((typeOf($v) == 'array') || (typeOf($v) == 'stdClass')) {
                console::writeLn(str_repeat('  ',$level).'%-'.$maxlent.'s %-'.$maxlenk.'s', '['.typeOf($v).']',  $k);
                self::inspect($v,$table,$level+1);
            } else {
                console::writeLn(str_repeat('  ',$level).'%-'.$maxlent.'s %-'.$maxlenk.'s = %s', '['.typeOf($v).']',  $k, $v);
            }
        }
        // var_dump($data);

    }

}

debug::setDebugProvider(new CliDebugProvider());
