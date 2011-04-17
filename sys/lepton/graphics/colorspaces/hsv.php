<?php

__fileinfo("HSV Color Space Routines");

using('lepton.graphics.colorspace');
using('lepton.graphics.graphics');

class HsvColor extends Color {

	var $hue;
	var $sat;
	var $value;

	//public function __construct(Color $color) {

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
				$this->hue = $args[0];
				$this->sat = $args[1];
				$this->value = $args[2];
				break;
			case 0;					if (is_a($args[0], 'Color')) {
						$this->setRGBA($args[0]->getRGBA());
						break;
					} else {
						$arg = func_get_arg(0);
						if (substr($arg, 0, 1) == "#") {
							if (strlen($arg) == 9) {
								$red = hexdec(substr($arg, 1, 2));
								$green = hexdec(substr($arg, 3, 2));
								$blue = hexdec(substr($arg, 5, 2));
								$alpha = hexdec(substr($arg, 7, 2));
							} elseif (strlen($arg) == 7) {
								$red = hexdec(substr($arg, 1, 2));
								$green = hexdec(substr($arg, 3, 2));
								$blue = hexdec(substr($arg, 5, 2));
								$alpha = 255;
							} elseif (strlen($arg) == 4) {
								$red = hexdec(str_repeat(substr($arg, 1, 1), 2));
								$green = hexdec(str_repeat(substr($arg, 2, 1), 2));
								$blue = hexdec(str_repeat(substr($arg, 3, 1), 2));
								$alpha = 255;
							}
						} else {
							throw new GraphicsException("Invalid color specification", GraphicsException::ERR_BAD_COLOR);
						}
						break;
					}

				break;
			default:
				throw new GraphicsException('Bad constructor invocation for '.__CLASS__);
		}
	}

	public function setRGBA($color) {

		$r = $color[0];
		$g = $color[1];
		$b = $color[2];
		// TODO: Handle alpha here

		$min = min( $r, $g, $b );
		$max = max( $r, $g, $b );
		$v = $max;                // v
		$delta = $max - $min;

		if( $max != 0 )
			$s = $delta / $max;        // s
		else {
			// r = g = b = 0        // s = 0, v is undefined
			$s = 0;
			$h = -1;
			return;
		}

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

		$this->hue = $h;
		$this->sat = $s * 255;
		$this->value = $v;

	}

	public function getRGBA() {
		$c = array();

		if( $this->sat == 0 ) {
			$c['r'] = $this->value;
			$c['g'] = $this->value;
			$c['b'] = $this->value;
			return;
		}

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
		throw new BadArgumentException("hsv() expects 0, 3 or 4 parameters");
	}
}
