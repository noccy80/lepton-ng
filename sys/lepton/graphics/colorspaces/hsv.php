<?php

	ModuleManager::load('lepton.graphics.colorspaces');

	class HsvColor extends Color {

		var $hue;
		var $saturation;
		var $value;

		public function __construct(Color $color) {


			$r = $color->r;
			$g = $color->g;
			$b = $color->b;


			$min = min( $r, $g, $b );
			$max = max( $r, $g, $b );
			$v = $max;				// v
			$delta = $max - $min;

			if( $max != 0 )
				$s = $delta / $max;		// s
			else {
				// r = g = b = 0		// s = 0, v is undefined
				$s = 0;
				$h = -1;
				return;
			}

			if( $delta == 0) {
				$h = 0;
			} else {
				if( $r == $max )
					$h = ( $g - $b ) / $delta;		// between yellow & magenta
				else if( $g == $max )
					$h = 2 + ( $b - $r ) / $delta;	// between cyan & yellow
				else
					$h = 4 + ( $r - $g ) / $delta;	// between magenta & cyan
				$h *= 60;				// degrees
				if( $h < 0 )
					$h += 360;
			}

			return array(
				$h,
				$s * 255,
				$v
			);
		}

		public function fromHSV($hue,$sat,$value) {

			if( $sat == 0 ) {
				$this->c['r'] = $value;
				$this->c['g'] = $value;
				$this->c['b'] = $value;
				return;
			}
			$s = (float)($sat / 256);
			$h = (float)($hue / 60);
			$v = (float)($value);
			$i = floor( $h );
			$f = $h - $i;			// factorial part of h
			$p = $v * ( 1 - $s );
			$q = $v * ( 1 - $s * $f );
			$t = $v * ( 1 - $s * ( 1 - $f ) );
			switch( $i ) {
				case 0:
					$this->c['r'] = $v;
					$this->c['g'] = $t;
					$this->c['b'] = $p;
					break;
				case 1:
					$this->c['r'] = $q;
					$this->c['g'] = $v;
					$this->c['b'] = $p;
					break;
				case 2:
					$this->c['r'] = $p;
					$this->c['g'] = $v;
					$this->c['b'] = $t;
					break;
				case 3:
					$this->c['r'] = $p;
					$this->c['g'] = $q;
					$this->c['b'] = $v;
					break;
				case 4:
					$this->c['r'] = $t;
					$this->c['g'] = $p;
					$this->c['b'] = $v;
					break;
				default:		// case 5:
					$this->c['r'] = $v;
					$this->c['g'] = $p;
					$this->c['b'] = $q;
					break;
			}
		}

	}

?>
