<?php

	/**
	 * ConvolutionImageFilter applies a convolution matrix on the image, uses
	 * the PHP/GD imageconvolution() method.
	 *
	 * @author Christopher Vagnetoft <noccy@chillat.net>
	 */
	class ConvolutionImageFilter extends ImageFilter {
		private $matrix;
		private $div;
		private $offset;
		/**
		 * @param array $matrix Array of 9 values for convolution array
		 * @param float $div Divisor
		 * @param float $offset Offset
		 */
		function __construct($matrix, $div, $offset) {
			$this->matrix = array_chunk($matrix, 3);
			$this->div = $div;
			$this->offset = $offset;
		}
		function applyFilter($himage) {
			imageconvolution($himage, $this->matrix, $this->div, $this->offset);
		}
	}

?>
