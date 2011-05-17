<?php

using('lepton.graphics.canvas');

/**
 * @class SvgRasterizer
 * @brief Rasterizes SVG images into a usable canvas.
 * 
 * This function will allow for loading and transforming an SVG image before
 * rasterizing it into a canvas. Modifications can be done to the canvas using
 * the usual tools (filters, painters, fonts etc) but any modification of the
 * SVG source using the transformSvg method will reset the canvas to that of
 * the rasterized state.
 * 
 * @author Christopher Vagnetoft
 * @todo Implement a fallback on the imagick binaries if extension not found
 */
class SvgRasterizer extends Canvas {
	
	private $svg = null;
	private $imh = null;
	
	function __construct($svgfile) {
		
		$this->imh = new Imagick();
		$this->svg = file_get_contents($svgfile);

		$this->svgToImage();
		// $im->writeImage('/path/to/colored/us-map.png');/*(or .jpg)*/

	}
	
	/**
	 * @brief Transform the XML of the SVG image using a function.
	 * 
	 * The function will be invoked with one parameter being the XML of the
	 * SVG image, and is expected to return the transformed XML. Not returning
	 * anything will leave the canvas intact and return false.
	 * 
	 * @param Function $transformfunction A function to do the transformation
	 * @return Bool True if the operation was successful
	 */
	public function transformSvg($transformfunction) {
		$xml = $this->svg;
		$xml = call_user_func_array($transformfunction,array($xml));
		if (!$xml) return false;
		$this->svg = $xml;
		// Update the image
		$this->svgToImage();
		return true;
	}
	
	private function svgToImage() {

		$this->imh->readImageBlob($this->svg);

		$this->imh->setImageFormat("png24");
		/*
		if ($height && $width) {
			$this->imh->resizeImage($width, $height, imagick::FILTER_LANCZOS, 1);
			$im->adaptiveResizeImage($width, $height);
		}
		*/

		$img = @imagecreatefromstring((string)$this->imh);
		if ($img) {
			$this->setImage($img);
			$this->gotimage = true;
		} else {
			throw new GraphicsException("Failed to load the image.", GraphicsException::ERR_LOAD_FAILURE);
		}
		
	}
	
	function __destruct() {
		$this->imh->clear();
		$this->imh->destroy();		
		unset($this->imh);
		
		parent::__destruct();
	}
	
}