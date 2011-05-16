<?php

class SvgRasterizer extends Canvas {
	
	function __construct($svgfile,$width,$height) {
		$im = new Imagick();
		$svgin = file_get_contents($svgfile);

		/*loop to color each state as needed, something like 
		1)explode $svgin
		2)foreach($array as $state){preg_replace blank color->state color }
		3)implode to $svgout*/
		$svgout = $svgin;

		$im->readImageBlob($svgout);

		/*png settings*/
		$im->setImageFormat("png24");
		$im->resizeImage($width, $height, imagick::FILTER_LANCZOS, 1);  /*Optional, if you need to resize*/

		/*jpeg*/
		// $im->setImageFormat("jpeg");
		// $im->adaptiveResizeImage(720, 445); /*Optional, if you need to resize*/

		$img = @imagecreatefromstring((string)$im);
		if ($img) {
			$this->setImage($img);
			$this->gotimage = true;
		} else {
			throw new GraphicsException("Failed to load the image.", GraphicsException::ERR_LOAD_FAILURE);
		}
		
		// $im->writeImage('/path/to/colored/us-map.png');/*(or .jpg)*/
		$im->clear();
		$im->destroy();		
	}
	
	
}