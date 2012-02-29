<?php

abstract class lpf {
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
		$sout = sprintf('[%s %d%% %.1f%s]',$text,(100/$max)*$cur,$mu,$muu);
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

class SceneState { }
class ActorState { }

abstract class LpfActor {
	protected $properties = array();
	protected $id = null;
	protected function addProperty($property,$init,$type) {
		$val = lpf::cast($init,$type);
		$this->properties[$property] = array(
			'type' => $type,
			'value' => $val
		);
	}
	public function __get($property) {
		if (arr::hasKey($this->properties,$property)) {
			return $this->properties[$property]['value'];
		}
		printf("No such property %s\n", $property); return null;
	}
	public function __set($property,$value) {
		if (arr::hasKey($this->properties,$property)) {
			$ptype = $this->properties[$property]['type'];
			$val = lpf::cast($value,$ptype);
			$this->properties[$property]['value'] = $val;
			list($type) = explode(':',$ptype);
			return;
		}
		printf("No such property %s\n", $property); return null;
	}
	public function __construct($id=null) {
		$this->id = $id;
		// Add basic properties
		$this->addProperty('visible', false, lpf::bool());
		$this->addProperty('id', $id, lpf::string());
		$this->addProperty('zindex', 0, lpf::int(0,65535));
		$this->addProperty('left', 0, lpf::int(-32000,32000));
		$this->addProperty('top', 0, lpf::int(-32000,32000));
		$this->addProperty('width', 0, lpf::int(-32000,32000));
		$this->addProperty('height', 0, lpf::int(-32000,32000));
		$this->create();
	}
	public function moveTo($x, $y, $w, $h) {
		$this->left = $x;
		$this->top = $y;
		$this->width = $w;
		$this->height = $h;
	}
	abstract function create();
	abstract function render(SceneState $ss, ActorState $as, Canvas $c);
}
