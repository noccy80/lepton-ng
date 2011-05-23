<?php

__fileinfo("HSV Color Space Routines");

using('lepton.graphics.colorspace');
using('lepton.graphics.graphics');

class HsvColor extends Color {

	private $hue = 0;
	private $sat = 0;
	private $value = 0;
	private $alpha = 1.0;

	//public function __construct(Color $color) {

	public function __get($key) {
		switch($key) {
			case 'h':
			case 'hue':
				return $this->hue;
			case 's':
			case 'saturation':
				return $this->sat;
			case 'v':
			case 'value':
				return $this->value;
			case 'a':
			case 'alpha':
				return $this->alpha;
			default:
				throw new BadPropertyException("HSV Color does not have property ".$key);

		}
	}

	public function __set($key,$value) {
		switch($key) {
			case 'h':
			case 'hue':
				$this->hue = $this->argToValue($value,359);
				break;
			case 's':
			case 'saturation':
				$this->sat = $this->argToValue($value,255);
				break;
			case 'v':
			case 'value':
				$this->value = $this->argToValue($value,255);
				break;
			case 'a':
				$this->alpha = $this->argToValue($value,255);
				break;
			default:
				throw new BadPropertyException("HSV Color does not have property ".$key);

		}
	}

	public function __construct() {
		$args = func_get_args();
		switch (count($args)) {
			case 1:
				if (is_a($args[0],'Color')) {
					$this->setRGBA($args[0]->getRGBA());
				} else {
					throw new BadArgumentException("Single argument for HSL must be instance of Color");
				}
				break;
			case 3:
				// HSV
				$this->hue =    $this->argToValue( $args[0], 359 );
				$this->sat =    $this->argToValue( $args[1], 255 );
				$this->value =  $this->argToValue( $args[2], 255 );
				break;
			default:
				throw new GraphicsException('Bad constructor invocation for '.__CLASS__);
		}
		logger::debug("h:%.2f s:%.2f v:%.2f", $this->hue, $this->sat, $this->value);
	}

	public function setRGBA($color) {

		$r = $color[0];
		$g = $color[1];
		$b = $color[2];
		$a = $color[3];
		// TODO: Handle alpha here

		$min = min( $r, $g, $b );
		$max = max( $r, $g, $b );
		$v = $max;                // v
		$delta = $max - $min;

		if( $max != 0 ) {
			$s = $delta / $max;        // s
			if( $delta == 0) {
				$h = 0;
			} else {
				if( $r == $max ) {
					$h = ( $g - $b ) / $delta;        // between yellow & magenta
				} else if( $g == $max ) {
					$h = 2 + ( $b - $r ) / $delta;    // between cyan & yellow
				} else {
					$h = 4 + ( $r - $g ) / $delta;    // between magenta & cyan
				}
				$h *= 60;                // degrees
				if( $h < 0 ) {
					$h += 360;
				}
			}
		} else {
			// r = g = b = 0
			// s = 0, v is undefined
			$s = 0;
			$h = -1;
		}

		$this->hue =    $h;
		$this->sat =    $s * 255;
		$this->value =  $v;
		$this->alpha =  $a;
		logger::debug("h:%.2f s:%.2f v:%.2f", $this->hue, $this->sat, $this->value);

	}

	public function getRGBA() {
		$c = array();

		if( $this->sat == 0 ) {
			$c['r'] = $this->value;
			$c['g'] = $this->value;
			$c['b'] = $this->value;
		} else {

			$s = (float)($this->sat / 256);
			$h = (float)($this->hue / 60);
			$v = (float)($this->value);
			$i = floor( $h );
			$f = $h - $i;            // factorial part of h
			$p = $v * ( 1 - $s );
			$q = $v * ( 1 - $s * $f );
			$t = $v * ( 1 - $s * ( 1 - $f ) );

			switch( $i ) {
				case 0:
					$c['r'] = $v;
					$c['g'] = $t;
					$c['b'] = $p;
					break;
				case 1:
					$c['r'] = $q;
					$c['g'] = $v;
					$c['b'] = $p;
					break;
				case 2:
					$c['r'] = $p;
					$c['g'] = $v;
					$c['b'] = $t;
					break;
				case 3:
					$c['r'] = $p;
					$c['g'] = $q;
					$c['b'] = $v;
					break;
				case 4:
					$c['r'] = $t;
					$c['g'] = $p;
					$c['b'] = $v;
					break;
				default:        // case 5:
					$c['r'] = $v;
					$c['g'] = $p;
					$c['b'] = $q;
					break;
			}
		}
		
		return array($c['r'], $c['g'], $c['b'], 255);

	}

}

function hsv() {
	$args = func_get_args();
	if (count($args) == 0) {
		return new HsvColor();
	} elseif (count($args) == 1) {
		return new HsvColor($args[0]);
	} elseif (count($args) == 3) {
		return new HsvColor($args[0],$args[1],$args[2]);
	} elseif (count($args) == 4) {
		return new HsvColor($args[0],$args[1],$args[2],$args[3]);
	} else {
		throw new BadArgumentException("hsv() expects 0, 1, 3 or 4 parameters");
	}
}
