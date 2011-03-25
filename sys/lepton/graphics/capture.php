<?php

using('lepton.graphics.canvas');

class ScreenShot extends Canvas {
	function __construct() {
		if (WINDOWS) {
			$sc = imagegrabscreen();
		} else {
			$bin = shell_exec('import -window root png:-');
			if (!$bin) throw new GraphicsException("Failed to capture screenshot! Ensure that imagemagick is installed and try again.");
			$sc = imagecreatefromstring($bin);
		}
		if ($sc) {
			$this->setImage($sc);
		} else {
			throw new GraphicsException("Failed to capture screenshot!");
		}
	}
}
