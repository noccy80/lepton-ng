<?php

ModuleManager::load('lepton.graphics.filter');

class ReflectionImageFilter extends ImageFilter {

	private $_os = null;

	/**
	 * Add a reflection to the image. Inspired by code from the php.net
	 * websites, written by klaproth at creative-mindworks dot de.
	 *
	 * Options is a hash consisting of one or more of these options:
	 *   reflectionheight - int: height of reflection (default: 25)
	 *   resizecanvas - bool: if true the image will be resized (default: true)
	 *   startopacity - float: opacity of reflection (default: 0.8) [NOT IMPLEMENTED]
	 *   endopacity - float: opacity at end of refletion (default: 0.5) [NOT IMPLEMENTED]
	 *   scale - float: ratio of pixels of reflection height (default: 2.0)
	 *   background - color: background color - disables alpha saving (default: null)
	 *
	 * @param array $options The options
	 */
	function __construct($options) {
		$this->_os = new Optionset($options);
	}

	function applyFilter(Canvas $canvas) {

		$himage = $canvas->getImage();

		$rheight = $this->_os->get('reflectionheight', 25);
		$iheight = imagesy($himage);
		$iwidth = imagesx($himage);

		if ($this->_os->get('resizecanvas',false)) {
			// create new canvas of iheight+rheight, set offset to
			// iheight
			$offs = $iheight;
			$hreflect = imagecreatetruecolor($iwidth, $iheight+$rheight);
		} else {
			// create new canvas of iheight, set offset to iheight to
			// iheight-rheight
			$offs = $iheight - $rheight;
			$hreflect = imagecreatetruecolor($iwidth, $iheight);
		}
		if ($this->_os->get('background', null)) {
			// Fill with background if specified, note that this disables
			// the alpha saving
			imagefilledrectangle($hreflect, 0, 0,
								 imagesx($hreflect), imagesy($hreflect),
								 new Color($this->_os->get('background')));
		} else {
			// Disable alphablending and enable saving of the alpha channel
			imagealphablending($hreflect, false);
			imagesavealpha($hreflect, true);
		}
		imagecopy($hreflect, $himage, 0, 0, 0, 0, $iwidth, $iheight);
		$as = 80 / $rheight;
		$sc = $this->_os->get('scale', 2);
		for ($y = 1; $y <= $rheight; $y++) {
			for ($x = 0; $x < $iwidth; $x++) {
				$rgba = imagecolorat($himage, $x, $offs - ($y * $sc));
				$alpha = max(($rgba >> 24) & 0x7F, 47 + ($y * $as));
				$rgba = imagecolorallocatealpha($hreflect,
												($rgba >> 16) & 0xFF,
												($rgba >> 8) & 0xFF,
												($rgba) & 0xFF,
												$alpha);
				imagesetpixel($hreflect, $x, $offs + $y - 1, $rgba);
			}
		}
		return $hreflect;

	}

}
