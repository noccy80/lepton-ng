<?php

    ModuleManager::load('lepton.graphics.colorspaces');

    class HsvColor extends Color {

        var $hue;
        var $sat;
        var $value;

        //public function __construct(Color $color) {

		public function __construct() {
			$args = function_get_args();
			switch (count($args)) {
				case 3:
					// HSV
					$this->hue = $args[0];
					$this->sat = $args[1];
					$this->value = $args[2];
					break;
				default:
					throw new GraphicsException('Bad constructor invocation for '.__CLASS__);
			}
		}

		public function fromRGB(Color $color) {

            $r = $color->r;
            $g = $color->g;
            $b = $color->b;


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
                if( $r == $max )
                    $h = ( $g - $b ) / $delta;        // between yellow & magenta
                else if( $g == $max )
                    $h = 2 + ( $b - $r ) / $delta;    // between cyan & yellow
                else
                    $h = 4 + ( $r - $g ) / $delta;    // between magenta & cyan
                $h *= 60;                // degrees
                if( $h < 0 )
                    $h += 360;
            }

            $this->hue = $h;
            $this->sat = $s * 255;
            $this->value = $v;
        }

        public function toRGB() {

            if( $this->sat == 0 ) {
                $this->c['r'] = $this->value;
                $this->c['g'] = $this->value;
                $this->c['b'] = $this->value;
                return;
            }
            $s = (float)($this->sat / 256);
            $h = (float)($this->hue / 60);
            $v = (float)($value);
            $i = floor( $h );
            $f = $h - $i;            // factorial part of h
            $p = $v * ( 1 - $s );
            $q = $v * ( 1 - $s * $f );
            $t = $v * ( 1 - $s * ( 1 - $f ) );
			$c = array();
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
			return new RgbColor($c['r'], $c['g'], $c['b']);
        }

    }

?>
