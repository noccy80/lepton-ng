<?php

ModuleManager::load('lepton.graphics.filter');

/**
 * GrayscaleImageFilter converts an image to grayscale. Uses the PHP/GD
 * imagefilter() method.
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 */
class GrayscaleImageFilter extends ImageFilter {
	function applyFilter(Canvas $canvas) {
		$himage = $canvas->getImage();
		if (function_exists('imagefilter')) {
			// If gd is bundled this will work
			imagefilter($himage, IMG_FILTER_GRAYSCALE);
		} else {
			// If not we need to do some enumeration
			$total = imagecolorstotal( $himage );
			if ($total > 0) {
				// This works for indexed images but not for truecolor
				for ( $i = 0; $i < $total; $i++ ) {
					$index = imagecolorsforindex( $himage, $i );
					$avg = ( $index["red"] + $index["green"] + $index["blue"] ) / 3;
					$red = $avg;
					$green = $avg;
					$blue = $avg;
					imagecolorset( $himage, $i, $red, $green, $blue );
				}
			} else {
				// For truecolor we need to enum it all
				for ( $x = 0; $x < imagesx($himage); $x++ ) {
					for ( $y = 0; $y < imagesy($himage); $y++) {
						$index = imagecolorat($himage, $x, $y);
						$avg = ((($index & 0xFF) + (($index >> 8) & 0xFF) + (($index >> 16) & 0xFF))/3);
						imagesetpixel($himage, $x, $y, $avg | ($avg << 8) | ($avg << 16 ));
					}
				}
			}
		}
	}
}

