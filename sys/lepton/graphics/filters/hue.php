<?php

using('lepton.graphics.filter');
using('lepton.graphics.colorspaces.hsv');

/**
 * GrayscaleImageFilter converts an image to grayscale. Uses the PHP/GD
 * imagefilter() method.
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 */
class HueImageFilter extends ImageFilter {
	private $r = null;
	private $g = null;
	private $b = null;
	private $hue = null;
	function __construct(Color $color) {
		$this->rs = (float)($color->r / 255);
		$this->gs = (float)($color->g / 255);
		$this->bs = (float)($color->b / 255);
		$hsv = hsv($color);
		$this->hue = $hsv->hue;
	}
	function applyFilter(Canvas $canvas) {
		$himage = $canvas->getImage();
		// If not we need to do some enumeration
		$total = imagecolorstotal( $himage );
		if ($total > 0) {
			// This works for indexed images but not for truecolor
			for ( $i = 0; $i < $total; $i++ ) {
				$index = imagecolorsforindex( $himage, $i );
				$rgb = rgb($index['red'],$index['green'],$index['blue']);
				$hsv = hsv($rgb);
				$hsv->hue = $this->hue;
				$rgb = rgb($hsv);
				$red = $rgb->red;
				$green = $rgb->green;
				$blue = $rgb->blue;
				imagecolorset( $himage, $i, $red, $green, $blue );
			}
		} else {
			// For truecolor we need to enum it all
			for ( $x = 0; $x < imagesx($himage); $x++ ) {
				for ( $y = 0; $y < imagesy($himage); $y++) {
					$index = imagecolorat($himage, $x, $y);
					$rgb = rgb($index['red'],$index['green'],$index['blue'],$index['alpha']);
					$hsv = hsv($rgb);
					$hsv->hue = $this->hue;
					$rgb = rgb($hsv);
					$red = $rgb->red;
					$green = $rgb->green;
					$blue = $rgb->blue;
					imagesetpixel($himage, $x, $y, ($red << 16) | ($green < 8) | ($blue));
				}
			}
		}
	}
}

