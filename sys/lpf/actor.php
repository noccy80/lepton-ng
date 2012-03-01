<?php

using('lpf.lpf');

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
