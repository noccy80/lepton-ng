<?php

class Point {
	private $x, $y;
	function __construct($x,$y) { 
		$this->x = intval($x); $this->y = intval($y); 
	}
	function __get($key) {
		if (isset($this->{$key})) return $this->{$key};
		throw new BadPropertyException($key);
	}
	function getPoint() { 
		return array($this->x, $this->y); 
	}
}
class Rect {
	private $x, $y, $w, $h;
	function __construct($x,$y,$w,$h) { 
		$this->x = intval($x); $this->y = intval($y); 
		$this->w = intval($w); $this->h = intval($h); 
	}
	function __get($key) {
		if (isset($this->{$key})) return $this->{$key};
		throw new BadPropertyException($key);
	}
	function getRect() { 
		return array($this->x, $this->y, $this->w, $this->h); 
	}
}

function point($x,$y) {
	return new Point($x,$y);
}
function rect($x,$y,$w,$h) {
	return new Rect($x,$y,$w,$h);
}
