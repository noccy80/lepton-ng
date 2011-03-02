<?php

using('lepton.graphics.filter');

# Colors for sepia conversion
# $red = ( $index["red"] * 0.393 + $index["green"] * 0.769 + $index["blue"] * 0.189 ) / 1.351;
# $green = ( $index["red"] * 0.349 + $index["green"] * 0.686 + $index["blue"] * 0.168 ) / 1.203;
# $blue = ( $index["red"] * 0.272 + $index["green"] * 0.534 + $index["blue"] * 0.131 ) / 2.140;

class BlurImageFilter extends ImageFilter {

	function applyFilter(Canvas $canvas) {

		$himage = $canvas->getImage();

		$m = array(
			array(1.0, 2.0, 1.0),
			array(2.0, 4.0, 2.0),
			array(1.0, 2.0, 1.0)
		);
		$div = 16;
		$offs = 0;
		ImageUtils::imageconvolution($himage, $m, $div, $offs);
	}
	
}
