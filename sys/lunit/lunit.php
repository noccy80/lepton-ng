<?php

using('lunit.lunitcase');

interface ILunitStatusCallback {
	function onCaseBegin($name,$meta);
	function onCaseEnd();
	function onTestBegin($name,$meta);
	function onTestEnd($status,$message);
}

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

	private $statuscb = null;

	function setStatusCallback(ILunitStatusCallback $object) {
		
		$this->statuscb = $object;	
		
	}

	function run() {

		$cases = Lunit::getCases();
		// Enumerate the cases
		foreach($cases as $case) {
			// Reflect the class to find methods and metadata
			$r = new ReflectionClass($case);
			$tc = new $case($this);
			$ml = $r->getMethods();
			$meta = LunitUtil::parseDoc($r->getDocComment());
			
			// Callback if set
			if ($this->statuscb) $this->statuscb->onCaseBegin($case,$meta);

			foreach($ml as $method) {
				if ($method->isPublic() && (substr($method->getName(),0,1) != '_')) {
					$tmeta = LunitUtil::parseDoc($method->getDocComment());
					if (!isset($tmeta['description'])) $tmeta['description'] = $method->getName();
					// Callback if set
					if ($this->statuscb) $this->statuscb->onTestBegin($method->getName(),$tmeta);
					try {
						$tc->{$method->getName()}();
						if ($this->statuscb) $this->statuscb->onTestEnd(true,null);
					} catch (LunitAssertionFailure $f) {
						if ($this->statuscb) $this->statuscb->onTestEnd(false,$f->getMessage());
					}

				}
			}

			// Callback if set
			if ($this->statuscb) $this->statuscb->onCaseEnd();

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
