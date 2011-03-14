<?php

using('lunit.lunitcase');

abstract class Lunit {
	private static $cases = array();
	public static function register($case) {
		self::$cases[] = $case;
	}
	public static function getCases() {
		return self::$cases;
	}
}

class LunitRunner {

	function run() {

		$cases = Lunit::getCases();
		// Enumerate the cases
		foreach($cases as $case) {
			$r = new ReflectionClass($case);
			$tc = new $case($this);
			$ml = $r->getMethods();
			$meta = LunitUtil::parseDoc($r->getDocComment());
			console::writeLn(__astr('\b{%s}'),$meta['description']);
			foreach($ml as $method) {
				if ($method->isPublic() && (substr($method->getName(),0,1) != '_')) {
					console::write('%s: ', $method->getName());
					try {
						$tc->{$method->getName()}();
						console::writeLn('PASS');
						// Call test
					} catch (LunitAssertionFailure $f) {
						console::writeLn($f->getMessage());
					}
				}
			}
		}
		
	}
	
}

class LunitUtil {
	function parseDoc($str) {
		$se = explode("\n",$str);
		$se = array_slice($se,1,count($se)-2);
		$cur = null;
		$ret = array();
		foreach($se as $row) {
			$lnclear = trim(substr(trim($row),1));
			if ($lnclear[0] == '@') {
				$cur = substr($lnclear,1,strpos($lnclear,' ')-1);
				$data = substr($lnclear,strpos($lnclear,' ')+1);
				$ret[$cur] = $data;
			} else {
				$ret[$cur].= $lnclear;
			}
		}
		return $ret;
	}
}
