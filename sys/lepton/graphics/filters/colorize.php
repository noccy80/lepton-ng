<?php

using('lepton.graphics.filter');

/**
 * GrayscaleImageFilter converts an image to grayscale. Uses the PHP/GD
 * imagefilter() method.
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 */
class ColorizeImageFilter extends ImageFilter {
	private $r = null;
	private $g = null;
	private $b = null;
	function __construct(Color $color) {
		$this->r = $color->r;
		$this->g = $color->g;
		$this->b = $color->b;
	}
	function applyFilter(Canvas $canvas) {
		$himage = $canvas->getImage();
		if (function_exists('imagefilter') && defined('IMG_FILTER_COLORIZE')) {
			// If gd is bundled this will work
			imagefilter($himage, IMG_FILTER_COLORIZE, $this->r, $this->g, $this->b);
		} else {
			throw new FunctionNotSupportedException("Colorize not supported by this version of GD");
		}
	}
}

