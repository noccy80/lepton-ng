<?php

class CliDebugProvider implements IDebugProvider {

    function inspect($data,$table=false,$level=0) {

		self::__inspect_recurs($data);        

    }
    
    function __inspect_recurs($data,$head='') {

        $maxlenk = 30;
        $maxlent = 8;
        foreach($data as $k=>$v) {
            if (strlen($k)>$maxlenk) $maxlenk = strlen($k);
            if (strlen(typeOf($v))>$maxlent) $maxlent = strlen(typeOf($v));
        }
        $maxlent+= 2;
        $itemcount = count($data);
        $idx = 0;
        foreach($data as $k=>$v) {
        	$idx++;
            if ((typeOf($v) == 'array') || (typeOf($v) == 'stdClass')) {
            	$ttl = $head.$k.' ';
                console::writeLn($ttl);
            	$myend = '|_';
            	$nhead = $head;
            	if ($idx++ > 0) $nhead = substr($head,0,strlen($head)-1).' ';
                self::__inspect_recurs($v,$nhead.$myend);
            } else {
            	switch(typeOf($v)) {
            	case 'boolean':
            		$sv = ($v)?'true':'false';
            		break;
            	default:
            		$sv = '"'.$v.'"';
            	}
                console::writeLn('%s = %s', $head.sprintf('%s<%s>',$k, typeOf($v)),  $sv);
            }
        }
    
    }

}

debug::setDebugProvider(new CliDebugProvider());
