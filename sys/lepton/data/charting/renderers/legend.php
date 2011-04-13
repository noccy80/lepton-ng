<?php

using('lepton.graphics.drawable');
using('lepton.graphics.colorspaces.*');

class ChartLegend extends Drawable {

	private $legend = array();
	private $props = array(
		'bordercolor' => "#888888",
		'background' => "#FFFFF0",
		'padding' => 5,
		'spacing' => 3,
		'font' => null
	);

	function __get($key) {
		if (isset($this->props[$key])) {
			return $this->props[$key];
		} else {
			return null;
		}
	}

	function __set($key,$value) {
		if (isset($this->props[$key])) {
			$this->props[$key] = $value;
		} else {
			throw new BadPropertyException("No property ".$key." for ".__CLASS__);
		}
	}

	function __construct(Array $legend) {
		$this->legend = $legend;	
	}
	
	function draw(Canvas $dest,$x=null,$y=null,$width=null,$height=null) {
		$c = new Canvas($width,$height,rgb($this->props['background']));
		$p = $c->getPainter();
		$p->drawRect(0,0,$width-1,$height-1,rgb($this->props['bordercolor']));
		imagecopy($dest->getImage(), $c->getImage(), $x, $y, 0, 0, $width, $height);
	}

}
