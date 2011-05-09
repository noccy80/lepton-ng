<?php

__fileinfo("RGB Color Space Routines");

using('lepton.graphics.colorspace');
using('lepton.graphics.graphics');

class RgbColor extends Color {

	private $red;
	private $green;
	private $blue;
	private $alpha;

	/**
	 * Constructor. This method will accept parameters in a number of
	 * different formats:
	 *   (R,G,B)     red, green, and blue (0-255)
	 *   (R,G,B,A)   red, green, blue and alpha(0-255)
	 *   #RGB        rgb, as a hexadecimal string (0-F)
	 *   #RRGGBB     rgb, as a hexadecimal string (00-FF)
	 *   #RRGGBBAA   rgba, as a hexidecimal string (00-FF)
	 */
	function __construct() {
		$args = func_get_args();
		switch (func_num_args ()) {
			case 0:
					$red = 0;
					$green = 0;
					$blue = 0;
					$alpha = 255;
					break;
			case 1: { // #RRGGBB[AA]
					if (is_a($args[0], 'Color')) {
						$this->setRGBA($args[0]->getRGBA());
						return;
					} else {
						$arg = func_get_arg(0);
						if (substr($arg, 0, 1) == "#") {
							if (strlen($arg) == 9) {
								$red = hexdec(substr($arg, 1, 2));
								$green = hexdec(substr($arg, 3, 2));
								$blue = hexdec(substr($arg, 5, 2));
								$alpha = hexdec(substr($arg, 7, 2));
							} elseif (strlen($arg) == 7) {
								$red = hexdec(substr($arg, 1, 2));
								$green = hexdec(substr($arg, 3, 2));
								$blue = hexdec(substr($arg, 5, 2));
								$alpha = 255;
							} elseif (strlen($arg) == 4) {
								$red = hexdec(str_repeat(substr($arg, 1, 1), 2));
								$green = hexdec(str_repeat(substr($arg, 2, 1), 2));
								$blue = hexdec(str_repeat(substr($arg, 3, 1), 2));
								$alpha = 255;
							}
						} else {
							throw new GraphicsException("Invalid color specification", GraphicsException::ERR_BAD_COLOR);
						}
						break;
					}
				}
			case 3: { // (R,G,B)
					$red =    $this->argToValue(func_get_arg(0),255);
					$green =  $this->argToValue(func_get_arg(1),255);
					$blue =   $this->argToValue(func_get_arg(2),255);
					$alpha =  255;
					break;
				}
			case 4: { // (R,G,B,A)
					$red =    $this->argToValue(func_get_arg(0),255);
					$green =  $this->argToValue(func_get_arg(1),255);
					$blue =   $this->argToValue(func_get_arg(2),255);
					$alpha =  $this->argToValue(func_get_arg(3),255);
					break;
				}
			default: {
					throw new GraphicsException("Invalid color specification", GraphicsException::ERR_BAD_COLOR);
				}
		}
		$this->red = $this->bounds($red);
		$this->green = $this->bounds($green);
		$this->blue = $this->bounds($blue);
		$this->alpha = $this->bounds($alpha);
	}

	/**
	 * Utility function to ensure a value is within the range 0-255
	 * @param integer $value The input value
	 * @param integer The output value
	 */
	private function bounds($value) {
		return ($value > 255) ? 255 : ($value < 0) ? 0 : $value;
	}

	function getRGBA() {
		return array($this->red, $this->green, $this->blue, $this->alpha);
	}

	function setRGBA($rgba) {
		$this->red = $this->bounds($rgba[0]);
		$this->green = $this->bounds($rgba[1]);
		$this->blue = $this->bounds($rgba[2]);
		$this->alpha = $this->bounds($rgba[3]);
	}

	function __get($key) {
		switch ($key) {
			case 'red':
			case 'r':
				return $this->red;
			case 'green':
			case 'g':
				return $this->green;
			case 'blue':
			case 'b':
				return $this->blue;
			case 'alpha':
			case 'a':
				return $this->alpha;
			case 'hex':
				return sprintf('#%02.x%02.x%02.x', $this->red, $this->green, $this->blue);
			case 'hexstr':
				return sprintf('%02.x%02.x%02.x', $this->red, $this->green, $this->blue);
		}
		return null;
	}

	function __set($key, $value) {
		switch ($key) {
			case 'red':
			case 'r':
				$this->red = $this->bounds($value);
				break;
			case 'green':
			case 'g':
				$this->green = $this->bounds($value);
				break;
			case 'blue':
			case 'b':
				$this->blue = $this->bounds($value);
				break;
			case 'alpha':
			case 'a':
				$this->alpha = $this->bounds($value);
		}
	}

}

function rgb() {
	$args = func_get_args();
	if (count($args) == 0) {
		return new RgbColor();
	} elseif (count($args) == 1) {
		return new RgbColor($args[0]);
	} elseif (count($args) == 3) {
		return new RgbColor($args[0], $args[1], $args[2]);
	} elseif (count($args) == 4) {
		return new RgbColor($args[0], $args[1], $args[2], $args[3]);
	} else {
		throw new BadArgumentException("rgb() expects 0, 1, 3 or 4 parameters");
	}
}
