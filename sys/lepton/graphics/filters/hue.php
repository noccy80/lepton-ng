<?php

ModuleManager::load('lepton.graphics.filter');

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
	function __construct(Color $color) {
		$this->rs = (float)($color->r / 255);
		$this->gs = (float)($color->g / 255);
		$this->bs = (float)($color->b / 255);
	}
	function applyFilter(Canvas $canvas) {
		$himage = $canvas->getImage();
		// If not we need to do some enumeration
		$total = imagecolorstotal( $himage );
		if ($total > 0) {
			// This works for indexed images but not for truecolor
			for ( $i = 0; $i < $total; $i++ ) {
				$index = imagecolorsforindex( $himage, $i );
				$avg = ( $index["red"] + $index["green"] + $index["blue"] ) / 3;
				$red = $index["red"] * $this->rs;
				$green = $index["green"] * $this->gs;
				$blue = $index["blue"] * $this->bs;
				imagecolorset( $himage, $i, $red, $green, $blue );
			}
		} else {
			// For truecolor we need to enum it all
			for ( $x = 0; $x < imagesx($himage); $x++ ) {
				for ( $y = 0; $y < imagesy($himage); $y++) {
					$index = imagecolorat($himage, $x, $y);
					$red = $index["red"] * $this->rs;
					$green = $index["green"] * $this->gs;
					$blue = $index["blue"] * $this->bs;
					imagesetpixel($himage, $x, $y, ($red << 16) | ($green < 8) | ($blue));
				}
			}
		}
	}
}

