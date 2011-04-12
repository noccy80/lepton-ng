<?php

using('lepton.data.charting');

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
	
	}

}

/*
	OLD CODE -- REWRITE
	
	Lepton::using('lepton.chart.charting');

	**
	 * PieChart presenter class: Draws a pie chart with the specified
	 * parameters.
	 *
	 * @note This class is highly under development and may change.
	 * @since 0.2.1
	 * @author Christopher Vagnetoft <noccy@chillat.net>
	 *
	class PieChartPresenter extends ChartPresenter {

		**
		 * Render te chart. Options are (defaults in paranthesis):
		 *   aspect     The aspect ratio (0.8)
		 *   radius     The radius of the chart - 100 being half canvas width (0.95)
		 *   cakeheight The height of the cake in pixels
		 *   explosion  How far out to push the center of the piece
		 *
		public function render($options=null) {
			$options = new OptionSet($options);

			$c = Graphics::create(400,400);
			$c->fillCanvas(new Color(255,255,255));

			$radiusx = 180;
			$radiusy = 90;

			$cx = $c->getWidth() / 2;
			$cy = $c->getHeight() / 2;
			$explode = $options->get('explosion',0);

			$palette = array(
				'#FF7777',
				'#77FF77',
				'#7777FF',
				'#FFFF77',
				'#77FFFF',
				'#FF77FF',
				'#FFAAFF'
			);

			$vals = $this->getSerie(0);
			$sum = $vals->getSum(0);
			$ci = 0;
			$sa = 0;
			foreach($vals->getValues(0) as $key=>$val) {
				$a = (360/$sum)*$val; // Get angle
				$ea = $sa + $a;
				$ch = new Color($palette[$ci]);
				$cs = new Color();
				list($hue,$sat,$val) = $ch->toHSV();
				$cs->fromHSV($hue,$sat,$val - 10);
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

			$f = new ImageFont('FreeSerif.ttf',8);
			for ($yp = 20; $yp >= 0; $yp--) {
				foreach($data as $slice) {
					$ox = $slice['ox'];
					$oy = $slice['oy'];
					$c->drawFilledArc($cx+$ox,$cy+$oy+$yp,$radiusx*2,$radiusy*2,$slice['sa'],$slice['ea'],($yp==0)?$slice['c1']:$slice['c2']);
					// TODO: Labels
					// $c->drawText($f, new Color(0,0,0), $cx+($ox*10),$cy+($oy*10),$slice['key']);
				}
			}

			$c->output('image/png');

		}

	}
	

*/
