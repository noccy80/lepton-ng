<?php __fileinfo("Font Wrapper");

using('lepton.graphics.canvas');
using('lepton.graphics.drawable');

interface IFont {
	function drawText(Drawable $drawable,$x,$y,$color,$text);
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
        $p = config::get('lepton.graphics.fontpaths', array(
        	'/usr/share/fonts/truetype/',
        	APP_PATH,
        	'./',
        	APP_PATH.'/fonts/',
        	'../'
        ));
        foreach($p as $fp) {
        	$ff = file_find($fp,$fontname);
            if ($ff != null) {
                $fullname = $ff;
                break;
            }
        }
        if ($fullname) {
            $this->font = array(
                'fontname'        => $fullname,
                'fontsize'        => $fontsize,
                'angle'            => 0
            );
        } else {
            throw new GraphicsException("Font ".$fontname." not found", GraphicsException::ERR_BAD_FONT);
        }
    }

    /**
     * Measure the text as bounding box.
     *
     * @param string $text The text
     * @return array Left, Top, Width, Height of bounding box
     */
    function measure($text) {
        $dim = imagettfbbox( $this->font['fontsize'], $this->font['angle'], $this->font['fontname'] , $text);
        return(array( $dim[0], $dim[1] - $dim[7], $dim[2]-$dim[0], $dim[3]-$dim[5] ));
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

	
	function drawText(Drawable $drawable,$x,$y,$color,$text) {

		$himage = $drawable->getImage();
        imagettftext(
			$himage, $this->font['fontsize'], $this->font['$angle'],
			$x, $y, $color, $this->font['fontname'], $text
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

	function drawText(Drawable $drawable,$x,$y,$color,$text) { }

}
