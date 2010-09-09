<?php

class AirBinder {

	static $buffer;

	function bindClass($class) {

		printf("Binding %s\n", $class);

		ModuleManager::load('app.controllers.'.$class);
		$host = 'http://localhost:80/';

		$o = "var {$class} = { ";
		$f = array();
		$cr = new ReflectionClass($class.'Controller');
		foreach($cr->getMethods() as $method) {
			$name = $method->getName();
			if ($method->isPublic() && ($name[0] != '_')) {

				$args = array();
				foreach($method->getParameters() as $arg) {
					$args[] = 'a'.$arg->getName();
				}
				$fo = $method->getName().':function('.join(',',array_merge(array('cb'),$args)).'){';

				$foq = array();
				foreach($method->getParameters() as $arg) {
					$foq[] = 'a'.$arg->getName();
				}

				$fo.= "var queryurl = '".$host.'/'.$class.'/'.$name."';";
				$fo.= "new Ajax.Request(queryurl,{";
				$fo.= "method:'get', ";
				$fo.= "onSuccess:function(t){cb(t.responseText)}";
				$fo.= "});";
				$fo.= "}";
				$f[]= $fo;
			}

		}
		$o.= join('',$f);
		$o.= "};\n";

		AirBinder::$buffer = $o;

	}

	function save($file) {
		$buf = join('', (array)AirBinder::$buffer);
		$fh = fopen($file,'w');
		fwrite($fh,$buf);
		fclose($fh);
	}

}

// Bind the classes

$cfg = config::get('lepton.airbinder.classes');
foreach($cfg as $ci) {
	AirBinder::bindClass($ci);
}
