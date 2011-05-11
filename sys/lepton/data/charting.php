<?php

using('lepton.graphics.canvas');
using('lepton.graphics.colorspaces.*');

interface IChart { 
	public function __construct($width,$height);
	// Render and return canvas
	function render();
}

abstract class Chart implements IChart {

	protected $width = null;
	protected $height = null;
	protected $dataset = null;
	protected $props = array();
	protected $ovlobjects = array();

	protected function getProperty($key,$default=null) {
		if (isset($this->props[$key]))
			return ($this->props[$key]);
		return $default;
	}
	
	protected function setProperty($key,$value) {
		$this->props[$key] = $value;
	}

	protected function setProperties(Array $data) {
		foreach($data as $key=>$value)
			$this->props[$key] = $value;
	}

	public function __construct($width,$height) {
		$this->width = $width;
		$this->height = $height;
	}
	
	public function setData(DataSet $data) {
		$this->dataset = $data;
	}
	
	public function __set($key,$value) {
		$this->props[$key] = $value;
	}
	
	public function __get($key) {
		if (isset($this->props[$key]))
			return ($this->props[$key]);
		return null;
	}
	
	public function addObject(Drawable $object, Rect $placement) {
		$this->ovlobjects[] = array(
			'object' => $object,
			'placement' => $placement
		);
	}
	
	protected function renderObjects(Canvas $c) {
		foreach($this->ovlobjects as $object) {
			list($x,$y,$w,$h) = $object['placement']->getRect();
			$object['object']->draw($c,$x,$y,$w,$h);
		}
	}

}
