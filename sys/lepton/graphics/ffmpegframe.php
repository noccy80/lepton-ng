<?php

using('lepton.graphics.canvas');

class FfmpegFrame extends Canvas {
	/**
	 * @overload Canvas::construct();
	 *
	 * Take a screenshot and return the image
	 */
	function __construct($filename,$frame=null) {
		if (!extension_loaded('ffmpeg')) {
			throw new BaseException("The FFMPEG extension is not loaded!");
		} else {
			$movie = new ffmpeg_movie($filename);
			if ($frame == null) {
				$frame = floor($movie->getFrameCount());
			}
			$frameimg = $movie->getFrame($frame);
		}
		if ($frameimg) {
			$sc = $frameimg->toGDImage();
			$this->setImage($sc);
		} else {
			throw new GraphicsException("Failed to capture frame!");
		}
	}
}
