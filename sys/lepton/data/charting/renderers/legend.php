<?php

using('lepton.graphics.drawable');
using('lepton.graphics.colorspaces.*');

class ChartLegend extends Drawable {

	private $dataset = null;
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

	function __construct(DataSet $ds) {
		$this->dataset = $ds;	
	}
	
	function draw(Canvas $dest,$x=null,$y=null,$width=null,$height=null) {
		$c = new Canvas($width,$height,rgb($this->props['background']));
		$p = $c->getPainter();
		$p->drawRect(0,0,$width-1,$height-1,rgb($this->props['bordercolor']));
		
		$labels = $this->dataset->getLabels();
		$labelcount = count($labels);

		// $ls = floor($height / $labelcount) - 4;
		$ls = 16;
		for ($i = 0; $i < $labelcount; $i++) {
			$x1 = 3;
			$y1 = 3 + ($ls+2)*$i;
			$x2 = $x1+$ls;
			$y2 = $y1+$ls;
			$p->drawFilledRect($x1,$y1,$x2,$y2,rgb(80,80,80),rgb(rand(0,200),rand(0,200),rand(0,200)));
		}
		
		imagecopy($dest->getImage(), $c->getImage(), $x, $y, 0, 0, $width, $height);
	}

}
