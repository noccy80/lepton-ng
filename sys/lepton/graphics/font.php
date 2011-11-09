<?php

module("Font Wrapper");

using('lepton.graphics.canvas');
using('lepton.graphics.drawable');

interface IFont {
    function drawText(Canvas $canvas, $x, $y, $color, $text);
    function measure($text);
}

/**
 * Class to wrap a font instance. Allows manipulation of the font once
 * assigned.
 */
class TruetypeFont implements IFont {

    private $font = array();

    /**
     * Constructor, attempts to load the font from the paths defined in the
     * lepton.graphics.fontpaths key or the default locations.
     *
     * @param string $fontname The font name
     * @param int $fontsize The size
     */
    function __construct($fontname, $fontsize) {
        $fullname = null;
        $p = config::get('lepton.graphics.fontpaths', array(
            './',
            base::appPath() . '/fonts',
            base::appPath(),
            '/usr/share/fonts/truetype/',
            base::basePath(),
        ));
        foreach ($p as $fp) {
            $ff = file_find($fp, $fontname);
            if ($ff != null) {
                $fullname = $ff;
                break;
            }
        }
        if ($fullname) {
            $this->font = array(
                'fontname' => $fullname,
                'fontsize' => $fontsize,
                'angle' => 0
            );
        } else {
            throw new GraphicsException("Font " . $fontname . " not found", GraphicsException::ERR_BAD_FONT);
        }
    }

    /**
     * Measure the text as bounding box.
     *
     * @author blackbart at simail dot it
     * @param string $text The text
     * @return array Left, Top, Width, Height of bounding box
     */
    function measure($text) {

        $box = imagettfbbox($this->font['fontsize'], $this->font['angle'], $this->font['fontname'], $text);
        if (!$box)
            return false;
        $min_x = min(array($box[0], $box[2], $box[4], $box[6]));
        $max_x = max(array($box[0], $box[2], $box[4], $box[6]));
        $min_y = min(array($box[1], $box[3], $box[5], $box[7]));
        $max_y = max(array($box[1], $box[3], $box[5], $box[7]));
        $width = ( $max_x - $min_x );
        $height = ( $max_y - $min_y );
        $left = abs($min_x) + $width;
        $top = abs($min_y) + $height;
        // to calculate the exact bounding box i write the text in a large image
        $img = @imagecreatetruecolor($width << 2, $height << 2);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefilledrectangle($img, 0, 0, imagesx($img), imagesy($img), $black);
        // for sure the text is completely in the image!
        imagettftext($img, $this->font['fontsize'],
                $this->font['angle'], $left, $top,
                $white, $this->font['fontname'], $text);
        // start scanning (0=> black => empty)
        $rleft = $w4 = $width << 2;
        $rright = 0;
        $rbottom = 0;
        $rtop = $h4 = $height << 2;
        for ($x = 0; $x < $w4; $x++)
            for ($y = 0; $y < $h4; $y++)
                if (imagecolorat($img, $x, $y)) {
                    $rleft = min($rleft, $x);
                    $rright = max($rright, $x);
                    $rtop = min($rtop, $y);
                    $rbottom = max($rbottom, $y);
                }
        // destroy img and serve the result
        imagedestroy($img);
        //	"left" => $left - $rleft,
        //	"top" => $top - $rtop,
        return array(
            "width" => $rright - $rleft + 1,
            "height" => $rbottom - $rtop + 1
        );
    }

    /**
     * Return a handle to the font. Intended to be used by the ImageCanvas
     * class.
     *
     * @return handle The handle of the font
     */
    function __getFont() {
        return $this->font;
    }

    function drawText(Canvas $canvas, $x, $y, $color, $text) {
        $himage = $canvas->getImage();
        $dim = $this->measure($text);
        imagettftext(
                $himage, $this->font['fontsize'], $this->font['angle'],
                $x, $y + $dim['height'], $color->getColor($himage), $this->font['fontname'], $text
        );
    }

    /**
     * Rotate the font relative to its current rotation.
     *
     * @param int $degress The number of degrees to rotate (positive or negative)
     */
    public function rotate($degrees) {
        $angle = $this->font['angle'];
        $angle+= $degrees % 360;
        $this->font['angle'] = $angle;
    }

    /**
     * Set the angle of the font.
     *
     * @param int $degrees The new rotation of the font
     */
    public function setAngle($degrees) {
        $this->font['angle'] = ($degrees % 360);
    }

}

class BitmapFont implements IFont {
    const EFFECT_NONE = null;
    const EFFECT_OUTLINE = 'outline';
    const EFFECT_SHADOW = 'shadow';

    private $font = null;
    private $fontfile = null;
    private $effect = null;
    private $options = null;

    function __construct($font) {
        if ((typeof($font) == "string") && (intval($font) == 0)) {
            $this->font = imageloadfont($font);
            $this->fontfile = $font;
        } else {
            $this->font = $font;
        }
    }

    function setTextEffect($effect, $color) {
        $this->effect = $effect;
        $this->options = $color;
    }

    function drawText(Canvas $canvas, $x, $y, $color, $text) {
        $himage = $canvas->getImage();
        if ($this->effect == BitmapFont::EFFECT_OUTLINE) {
            $ow = 1;
            for ($xx = $x - $ow; $xx <= $x + $ow; $xx++) {
                for ($yy = $y - $ow; $yy <= $y + $ow; $yy++) {
                    imagestring($himage, $this->font, $xx, $yy, $text, $this->options->getColor($himage));
                }
            }
        } elseif ($this->effect == BitmapFont::EFFECT_SHADOW) {
            $ow = 1;
            for ($z = 0; $z <= $ow; $z++) {
                imagestring($himage, $this->font, $x + $z, $y + $z, $text, $this->options->getColor($himage));
            }
        }
        imagestring($himage, $this->font, $x, $y, $text, $color->getColor($himage));
    }

    function measure($text) {
        return array(
            'width' => (imagefontwidth($this->font) * strlen($text)),
            'height' => (imagefontheight($this->font) * count(explode("\n",$text)))
        );
    }

}
