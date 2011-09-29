<?php

ModuleManager::load('lepton.graphics.filter');

class PixelateImageFilter extends ImageFilter {

	private $pixelsize = 1;
	private $usegdfilter = false;
	private $advanced = false;

	function __construct($pixelsize,$advanced=false) {

		if ((PHP_VERSION_ID >= 50300) && (defined('IMG_FILTER_PIXELATE'))) {
			// use existing GD filter code
			$this->usegdfilter = true;
		}
		$this->advanced = $advanced;
		$this->pixelsize = $pixelsize;

	}

    function applyFilter(Canvas $canvas) {
		$himage = $canvas->getImage();
		if ($this->usegdfilter) {
			if (!imagefilter($himage,IMG_FILTER_PIXELATE,$this->pixelsize,$this->advanced))
				throw new GraphicsException("Failed to apply filter");
		} else {
			// TODO: Implement our own pixelation filter
		}
		$canvas->setImage($himage);
		return null;

	}

}
