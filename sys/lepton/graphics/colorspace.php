<?php

interface IColor {
	function getRGBA();
	function setRGBA($rgba);
}

abstract class Color implements IColor {
	/**
	 * Returns a color allocated from the image. If the second parameter
	 * is true, the alpha value will be used.
	 *
	 * @param hImage $himage The image to allocate from
	 * @param boolean $withalpha If true, returns RGBA value
	 * @return hColor The color bound to the image
	 */
	function getColor($himage, $withalpha=false) {
		list($r,$g,$b,$a) = $this->getRGBA();
		if ($withalpha) {
			return imagecolorallocatealpha($himage, $r, $g, $b, $a);
		}
		return imagecolorallocate($himage, $r, $g, $b);
	}
	
	/**
	 * @brief Helper function to assign a color value from an existing color
	 *
	 * @param Color $color The color to assign
	 */
	function setColor(Color $color) {
		$this->setRGBA($color->getRGBA());
	}

	/**
	 * @brief Return the RGB value of the color
	 *
	 * @return String The RGB balue
	 */
	function __toString() {
		list($r,$g,$b,$a) = $this->getRGBA();
		return sprintf('#%02x%02x%02x',$r,$g,$b);
	}

}
