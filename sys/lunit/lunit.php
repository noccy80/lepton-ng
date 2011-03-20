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
	private $results = null;

	function setStatusCallback(ILunitStatusCallback $object) {
		
		$this->statuscb = $object;	
		
	}
	
	function getResults() {
	
		return $this->results;
		
	}

	function run() {

		$cases = Lunit::getCases();
		$casedata = array();
		// Enumerate the cases
		foreach($cases as $case) {
			// Setup report structure
			$casereport = array();
			// Reflect the class to find methods and metadata
			$r = new ReflectionClass($case);
			$ml = $r->getMethods();
			$skip = false;
			$meta = LunitUtil::parseDoc($r->getDocComment());
			if (!isset($meta['description'])) $meta['description'] = $case;
			if (isset($meta['extensions'])) {
				$extn = explode(' ',$meta['extensions']);
				foreach($extn as $ext) {
					if (!extension_loaded($ext)) $skip = true;
				}
			}

			$casereport['meta'] = $meta;
			// Callback if set
			if ($this->statuscb) $this->statuscb->onCaseBegin($case,$meta);

			if (!$skip) $tc = new $case($this);
			foreach($ml as $method) {
				$methodname = $method->getName();
				if ($method->isPublic() && (substr($methodname,0,1) != '_')) {
					$methodreport = array();
					$tmeta = LunitUtil::parseDoc($method->getDocComment());
					if (!isset($tmeta['description'])) $tmeta['description'] = $methodname;

					// Save meta to method report
					$methodreport['meta'] = $tmeta;
					// Callback if set, then create timer
					if ($this->statuscb) $this->statuscb->onTestBegin($methodname,$tmeta);
					if (!$skip) {
					$tm = new Timer();
					try {
						$tm->start();
						$tc->{$methodname}();
						$tm->stop();
						$methodreport['passed'] = true;
						$methodreport['message'] = null;
						if ($this->statuscb) $this->statuscb->onTestEnd(true,null);
					} catch (LunitAssertionFailure $f) {
						$tm->stop();
						$methodreport['passed'] = false;
						$methodreport['message'] = $f->getMessage();
						if ($this->statuscb) $this->statuscb->onTestEnd(false,$f->getMessage());
					} catch (Exception $e) {
						$tm->stop();
						$methodreport['passed'] = false;
						$methodreport['message'] = $e->getMessage();
						if ($this->statuscb) $this->statuscb->onTestEnd(false,$e->getMessage());
					}
					} else {
						$methodreport['passed'] = false;
						$methodreport['message'] = 'Skipped';
						$this->statuscb->onTestEnd(false,'Skipped');
					}
					$methodreport['elapsed'][] = $tm->getElapsed();
					// Save report
					$casereport['tests'][$methodname] = $methodreport;
				}
			}
			
			$casedata[$case] = $casereport;

			// Callback if set
			if ($this->statuscb) $this->statuscb->onCaseEnd();

		}
		
		$this->results = $casedata;
		
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
