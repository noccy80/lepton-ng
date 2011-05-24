<?php

__fileinfo("HSL Color Space Routines");

using('lepton.graphics.colorspace');
using('lepton.graphics.graphics');

class HslColor extends Color {

	private $hue = 0;
	private $sat = 0;
	private $lum = 0;
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
			case 'l':
			case 'luminance':
				return $this->lum;
			case 'a':
			case 'alpha':
				return $this->alpha;
			default:
				throw new BadPropertyException("HSL Color does not have property ".$key);

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
			case 'l':
			case 'luminance':
				$this->lum = $this->argToValue($value,255);
				break;
			case 'a':
				$this->alpha = $this->argToValue($value,255);
				break;
			default:
				throw new BadPropertyException("HSL Color does not have property ".$key);

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
				$this->lum =    $this->argToValue( $args[2], 255 );
				break;
			default:
				throw new GraphicsException('Bad constructor invocation for '.__CLASS__);
		}
		logger::debug("h:%.2f s:%.2f v:%.2f", $this->hue, $this->sat, $this->lum);
	}

	public function setRGBA($color) {
		
		// Convert the RBG values to the range 0-1
		$r = $color[0] / 255;
		$g = $color[1] / 255;
		$b = $color[2] / 255;
		$a = $color[3];

		// Find min and max values of R, B, G
		$min = min( $r, $g, $b );
		$max = max( $r, $g, $b );

		// L = (maxcolor + mincolor)/2 
		$l = ($max + $min) / 2;
		
		// If the max and min colors are the same (ie the color is some kind 
		// of grey), S is defined to be 0, and H is undefined but in programs 
		// usually written as 0, Otherwise, test L. 
		if ($max == $min) {
			$s = 0;
			$h = 0;
		} else {
			// If L < 0.5, S=(maxcolor-mincolor)/(maxcolor+mincolor)
			// If L >=0.5, S=(maxcolor-mincolor)/(2.0-maxcolor-mincolor)
			if ($l < 0.5) {
				$s = ($max-$min)/($max+$min);
			} else {
				$s = ($max-$min)/(2.0-$max-$min);
			}
		}

		// If R=maxcolor, H = (G-B)/(maxcolor-mincolor)
		// If G=maxcolor, H = 2.0 + (B-R)/(maxcolor-mincolor)
		// If B=maxcolor, H = 4.0 + (R-G)/(maxcolor-mincolor)
		if ($r == $max) {
			$h = ($g - $b) / ($max - $min);
		} elseif ($g == $max) {
			$h = 2.0 + ($b - $r) / ($max - $min);
		} elseif ($b == $max) {
			$h = 4.0 + ($r - $g) / ($max - $min);
		}

		$this->hue =    $h * 60;
		$this->sat =    $s * 255;
		$this->lum =    $l * 255;
		if ($this->hue < 0) $this->hue += 360;
		$this->alpha =  $a;
		logger::debug("hsl in - h:%.2f s:%.2f v:%.2f", $this->hue, $this->sat, $this->lum);

	}

	public function getRGBA() {
		
		$c = array('r'=>0,'g'=>0,'b'=>0,'a'=>255);
		$h = $this->hue / 360;
		$s = $this->sat / 255;
		$l = $this->lum / 255;

		// If S=0, define R, G, and B all to L
		if( $s == 0 ) {
			$c['r'] = $l;
			$c['g'] = $l;
			$c['b'] = $l;
		} else {
			// Otherwise, test L.
			// If L < 0.5, temp2=L*(1.0+S)
			// If L >= 0.5, temp2=L+S - L*S
			if ($l < 0.5) {
				$temp2 = $l * (1.0 + $s);
			} else {
				$temp2 = ($l + $s) - ($l * $s);
			}

			// temp1 = 2.0*L - temp2
			$temp1 = 2.0 * $l - $temp2;
		
			// For each of R, G, B, compute another temporary value, temp3, as follows:
			// for R, temp3=H+1.0/3.0
			$rtemp3 = $h + 1.0 / 3.0;
			if ($rtemp3 < 0) $rtemp3 = $rtemp3 + 1.0;
			if ($rtemp3 > 1) $rtemp3 = $rtemp3 - 1.0;
			// for G, temp3=H
			$gtemp3 = $h;
			if ($gtemp3 < 0) $gtemp3 = $gtemp3 + 1.0;
			if ($gtemp3 > 1) $gtemp3 = $gtemp3 - 1.0;
			// for B, temp3=H-1.0/3.0
			$btemp3 = $h - 1.0 / 3.0;
			if ($btemp3 < 0) $btemp3 = $btemp3 + 1.0;
			if ($btemp3 > 1) $btemp3 = $btemp3 - 1.0;
			// if temp3 < 0, temp3 = temp3 + 1.0
			// if temp3 > 1, temp3 = temp3 - 1.0

			// For each of R, G, B, do the following test:
			// If 6.0*temp3 < 1, color=temp1+(temp2-temp1)*6.0*temp3
			// Else if 2.0*temp3 < 1, color=temp2
			// Else if 3.0*temp3 < 2, color=temp1+(temp2-temp1)*((2.0/3.0)-temp3)*6.0
			// Else color=temp1
			foreach(array('r'=>$rtemp3,'g'=>$gtemp3,'b'=>$btemp3) as $ck=>$temp3) {
				if (6.0 * $temp3 < 1) {
					$c[$ck] = $temp1+($temp2-$temp1)*6.0*$temp3;
				} elseif (2.0 * $temp3 < 1) {
					$c[$ck] = $temp2;
				} elseif (3.0 * $temp3 < 2) {
					$c[$ck] = $temp1 + ($temp2 - $temp1) * ((2.0 / 3.0) - $temp3) * 6.0;
				} else {
					$c[$ck] = $temp1;
				}
			}
		}

		$c['r'] = floor($c['r']*255);
		$c['g'] = floor($c['g']*255);
		$c['b'] = floor($c['b']*255);
		
		logger::debug("hsl out - r:%.2f g:%.2f b:%.2f", $c['r'],$c['g'],$c['b']);
		return array($c['r'], $c['g'], $c['b'], 255);

	}

}

function hsl() {
	$args = func_get_args();
	if (count($args) == 0) {
		return new HslColor();
	} elseif (count($args) == 1) {
		return new HslColor($args[0]);
	} elseif (count($args) == 3) {
		return new HslColor($args[0],$args[1],$args[2]);
	} elseif (count($args) == 4) {
		return new HslColor($args[0],$args[1],$args[2],$args[3]);
	} else {
		throw new BadArgumentException("hsl() expects 0, 1, 3 or 4 parameters");
	}
}
