<?php

using('lepton.data.charting');
using('lepton.data.charting.renderers.*');

class PieChart extends Chart {

	const STYLE_2D = '2d';
	const STYLE_3D = '3d';

	function __construct($width,$height) {
		$this->setProperties(array(
			'legend' => true,
			'style' => PieChart::STYLE_3D
		));
		parent::__construct($width,$height);
	}

	function render() {

		$c = new Canvas($this->width, $this->height, rgb($this->getProperty('background','#FFFFFF')));

		$radiusx = 180;
		$radiusy = 90;

		$cx = $c->getWidth() / 2;
		$cy = $c->getHeight() / 2;
		$explode = $this->getProperty('explode',0);

		$palette = array(
			'#FF7777',
			'#77FF77',
			'#7777FF',
			'#FFFF77',
			'#77FFFF',
			'#FF77FF',
			'#FFAAFF'
		);

		list($label,$vals) = $this->dataset->getSeries(0);
		$sum = $vals->getSum();
		$ci = 0;
		$sa = 0;
		for($n = 0; $n < $vals->getCount(); $n++) {
			list($val,$key) = $vals->getValue($n);
			$a = (360/$sum)*$val; // Get angle
			$ea = $sa + $a;
			$ch = rgb($palette[$ci]);
			$cs = hsv();
			$cs->setRGBA($ch->getRGBA());
			$cs->value = $cs->value - 30;
			$data[] = array(
				'key' => $key,
				'c1' => $ch,
				'c2' => $cs,
				'sa' => $sa,
				'ea' => $ea
			);
			$sa=$ea;
			$ci++;
		}

		$offs = array();
		foreach($data as $id=>$slice) {
			$avg = ($slice['sa'] + $slice['ea']) / 2;
			$data[$id]['ox'] = cos(((($avg-90)%360) * PI) / 180) * $explode;
			$data[$id]['oy'] = sin(((($avg-90)%360) * PI) / 180) * $explode;
		}

		$f = new TruetypeFont('FreeSerif.ttf',8);
		$p = $c->getPainter();
		for ($yp = 20; $yp >= 0; $yp--) {
			foreach($data as $slice) {
				$ox = $slice['ox'];
				$oy = $slice['oy'];
				$p->drawFilledArc($cx+$ox,$cy+$oy+$yp,$radiusx*2,$radiusy*2,$slice['sa'],$slice['ea'],($yp==0)?$slice['c1']:$slice['c2']);
				// TODO: Labels
				// $c->drawText($f, new Color(0,0,0), $cx+($ox*10),$cy+($oy*10),$slice['key']);
			}
		}
		
		/*
		$legend = new ChartLegend(array('#FF0000'=>'Hello','#0000FF'=>'World'));
		$legend->draw($c,10,10,150,100);
		*/
		
		// Return the canvas
		return $c;
	
	}

}


