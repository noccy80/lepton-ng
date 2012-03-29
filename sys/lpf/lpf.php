<?php

abstract class lpf {
	private static $currentframe = 0;
	private static $maxframe = 0;
	static function bool() { return 'BOOL:'; }
	static function int($min,$max) { return 'INT:'.$min.','.$max; }
	static function float($min,$max) { return 'FLOAT:'.$min.','.$max; }
	static function string() { return 'STRING:'; }
	static function color() { return 'COLOR:'; }
	static  function cast($val,$type) {
		list($type,$constrain) = explode(':',$type);
		switch($type) {
			case 'INT':
				$v = intval($val);
				list($min,$max) = explode(',',$constrain);
				$v = ($v<$min)?$min:(($v>$max)?$max:$v);
				break;
			case 'FLOAT':
				$v = floatval($val);
				list($min,$max) = explode(',',$constrain);
				$v = ($v<$min)?$min:(($v>$max)?$max:$v);
				break;
			case 'BOOL':
				if (is_string($val) && ($val == 'true')) {
					$v = true;
				} else {
					$v = ($val)?true:false;
				}
				break;
			case 'COLOR':
				if (!$val) $val = '#000000';
				$v = rgb($val);
				break;
			case 'STRING':
				$v = (string)$val;
				break;
		}
		return $v;
	}
	static function doTick() {
	}
	static function updateFrame($cur,$max) {
		self::$currentframe = $cur;
		self::$maxframe = $max;
	}
	static function getFrame() {
		return(array(self::$currentframe,self::$maxframe));
	}
	static function updateStatus($text,$cur,$max) {
		$mu = memory_get_usage();
		if ($mu > 1024) {
			$mu = $mu / 1024;
			$muu = 'kB';
			if ($mu > 1024) {
				$mu = $mu / 1024;
				$muu = 'mB';
			}
		} else {
			$muu = 'B';
		}
		$sout = sprintf('[%s %d%% %.1f%s]'.str_repeat(" ",5),$text,(100/$max)*$cur,$mu,$muu);
		printf('%s%s',$sout,str_repeat("\x08",strlen($sout)));
	}
	static function rndseed($seed = null) {
		if (!$seed) {
			list($usec, $sec) = explode(' ', microtime());
			$seed = (float) $sec + ((float) $usec * 100000);
		}
		mt_srand($seed);
		return $seed;
	}
}
