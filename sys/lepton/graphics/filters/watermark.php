<?php

	ModuleManager::load('lepton.graphics.filters');

	/**
	 * WatermarkImageFilter applies a watermark on top of an image. The
	 * watermark could (should?) be a transparent PNG image. The x and y values
	 * passed to the constructor allows for positioning around the image edge.
	 * If a positive number is used, this is seen as relative to the left or
	 * top edge of the image. If a negative number is used, it is instead seen
	 * as relative to the right or bottom edge of the image.
	 *
	 * @author Christopher Vagnetoft <noccy@chillat.net>
	 */
	class WatermarkImageFilter extends ImageFilter {
		const POS_RELATIVE = 0;
		const POS_ABSOLUTE = 1;
		const POS_CENTERED = 2;

		private $hwatermark;
		private $placement;
		private $width;
		private $height;
		private $x;
		private $y;
		/**
		 * @param int $x The X offset (positive values from left, negative from right)
		 * @param int $y The Y offset (positive valeus from top, negative from bottom)
		 * @param string $watermark The watermark image to apply
		 * @param int $placement The placement method to use
		 */
		function __construct($x,$y,$watermark,$placement=WatermarkImageFilter::POS_RELATIVE) {
			$this->hwatermark = imagecreatefromstring(file_get_contents($watermark));
			$this->x = $x; $this->y = $y;
			$this->placement = $placement;
			$this->width = imageSX($this->hwatermark);
			$this->height = imageSY($this->hwatermark);
		}
		function __destruct() {
			imagedestroy($this->hwatermark);
		}
		function applyFilter($himage) {
			$iw = imagesx($himage);
			$ih = imagesy($himage);
			switch($this->placement) {
				case WatermarkImageFilter::POS_RELATIVE:
					$dx = ($this->x >= 0)?($this->x):($iw - $this->width + $this->x + 1);
					$dy = ($this->y >= 0)?($this->y):($ih - $this->height + $this->y + 1);
					break;
				case WatermarkImageFilter::POS_ABSOLUTE:
					$dx = $this->x;
					$dy = $this->y;
					break;
				case WatermarkImageFilter::POS_CENTERED;
					$dx = ($iw/2) + $this->x;
					$dy = ($ih/2) + $this->y;
					break;
			}
			imagecopymerge_alpha($himage, $this->hwatermark, $dx, $dy, 0, 0, $this->width, $this->height, 0);
		}
	}

?>
