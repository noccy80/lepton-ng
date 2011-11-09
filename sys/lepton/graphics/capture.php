<?php

using('lepton.graphics.canvas');

class ScreenShot extends Canvas {
    /**
     * @overload Canvas::construct();
     *
     * Take a screenshot and return the image
     */
    function __construct() {
        if (WINDOWS) {
            $sc = imagegrabscreen();
        } else {
            if (getenv('DISPLAY')) {
                $bin = shell_exec('import -window root png:-');
                if (!$bin) throw new GraphicsException("Failed to capture screenshot! Ensure that imagemagick is installed and try again.");
                $sc = imagecreatefromstring($bin);
            } else {
                throw new GraphicsException('$DISPLAY not set');
            }
        }
        if ($sc) {
            $this->setImage($sc);
        } else {
            throw new GraphicsException("Failed to capture screenshot!");
        }
    }
}
