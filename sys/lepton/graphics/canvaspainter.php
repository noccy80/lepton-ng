<?php

class CanvasPainter {

	private $himage;
	private $canvas;

	function __construct(Canvas $canvas) {
		$this->himage = $canvas->getImage();
		$this->canvas = $canvas;
	}

	/**
     * Set a pixel in the image
     *
     * @param int $x The X offset of the pixel to set
     * @param int $y The Y offset of the pixel to set
     * @param Color $color The color to set to
     */
    function setPixel($x, $y, Color $color) {

        imagesetpixel($this->himage,$x,$y,$color->getColor($this->himage,$this->canvas->savealpha));

    }

    /**
     * Get the color of a pixel in the image
     *
     * @param int $x The X offset of the pixel to get
     * @param int $y The Y offset of the pixel to get
     * @return Color The color of the pixel
     */
    function getPixel($x, $y) {

        return new Color(imagecolorat($this->himage,$x,$y));

    }

	    /**
     * Fill the canvas with the specified colosaver.
     *
     * @param Color $fillcolor The Color() to fill with.
     */
    function fillCanvas(Color $fillcolor) {

        $w = imageSX($this->himage);
        $h = imageSY($this->himage);
        imagefilledrectangle($this->himage, 0, 0, $w, $h, $fillcolor->getColor($this->himage,$this->canvas->savealpha));

    }

    /**
     *
     *
     *
     *
     */
    function fillArea($x, $y, Color $border, Color $fillcolor) {

        imagefilltoborder($this->himage, $x, $y, $border->getColor($this->himage), $fillcolor->getColor($this->himage));

    }

    /**
     * Draw a line between the coordinates specified by {x1,y1} and {x2,y2}
     *
     *
     *
     */
    function drawLine($x1, $y1, $x2, $y2, Color $color) {

        imageline($this->himage, $x1, $y1, $x2, $y2, $color->getColor($this->himage));

    }

    /**
     * Draw a rectangle between the coordinates specified by {x1,y1} and
     * {x2,y2}
     *
     *
     *
     */
    function drawRect($x1, $y1, $x2, $y2, Color $color) {

        imagerectangle($this->himage,$x1,$y1,$x2,$y2,$color->getColor($this->himage));

    }

    /**
     * Draw a filled rectangle between the coordinates specified by {x1,y1}
     * and {x2,y2}
     *
     *
     *
     */
    function drawFilledRect($x1, $y1, $x2, $y2, Color $color, Color $fill) {

		imagefilledrectangle($this->himage,$x1,$y1,$x2,$y2,$fill->getColor($this->himage));
        imagerectangle($this->himage,$x1,$y1,$x2,$y2,$color->getColor($this->himage));

    }

    /**
     * Draw an arc.
     *
     * @see ImageCanvas::drawFilledArc
     * @param int $x X coordinate
     * @param int $y Y coordinate
     * @param int $width The width of the circle
     * @param int $height The height of the circle
     * @param int $start Starting angle, 0 being north
     * @param int $end Ending angle
     * @param Color $color The color
     */
    function drawArc($x, $y, $width,$height,$start, $end, Color $color) {

        $sa = ($start - 90) % 360;
        $ea = ($end - 90) % 360;
        imagearc($this->himage,$x,$y,$width,$height,$sa,$ea,$color->getColor($this->himage));

    }

    /**
     * Draw a filled arc. Will use the imagefilledarc() function if available,
     * and otherwise fall back on a custom method of achieving the same
     * result.
     *
     * @see ImageCanvas::drawArc
     * @param int $x X coordinate
     * @param int $y Y coordinate
     * @param int $width The width of the circle
     * @param int $height The height of the circle
     * @param int $start Starting angle, 0 being north
     * @param int $end Ending angle
     * @param Color $color The color
     */
    function drawFilledArc($x, $y, $width, $height, $start, $end, Color $color) {

        $sa = ($start - 90) % 360;
        $ea = ($end - 90) % 360;
        if (function_exists('imagefilledarc')) {
            imagefilledarc($this->himage,$x,$y,$width,$height,$sa,$ea,$color->getColor($this->himage),IMG_ARC_PIE);
        } else {
            $ch = $color->getColor($this->himage);
            imagearc($this->himage,$x,$y,$width,$height,$sa,$ea,$ch);
            $p1x = cos(($sa * PI) / 180);
            $p1y = sin(($sa * PI) / 180);
            $p2x = cos(($ea * PI) / 180);
            $p2y = sin(($ea * PI) / 180);
            imageline($this->himage,$x,$y,$x+$p1x*($width/2),$y+$p1y*($height/2),$ch);
            imageline($this->himage,$x,$y,$x+$p2x*($width/2),$y+$p2y*($height/2),$ch);
            $xmin = min($x,$p1x,$p2x);
            $xmax = max($x,$p1x,$p2x);
            $ymin = min($y,$p1y,$p2y);
            $ymax = max($y,$p1y,$p2y);
            $xc = ($xmax+$xmin)/2;
            $yc = ($ymax+$ymin)/2;
            // TODO: This fill doesn't work
            imagefilltoborder($this->himage,$xc,$yc,$ch,$ch);
        }

    }

    /**
     * Draws a polygon with the specified color. The first parameter
     * should be an array of X/Y values.
     *
     * @note The spline/bezier implementation should be based on this
     *
     * @param array $points The points
     * @param Color $color The color to use
     */
    function drawPoly($points, Color $color) {

        $c = $color->getColor($this->himage);
        for($n = 0; $n < count($points) - 1; $n++) {
            imageline($this->himage,
                $points[$n][0], $points[$n][1],
                $points[$n+1][0], $points[$n+1][1],
                $c);
        }

    }

}
