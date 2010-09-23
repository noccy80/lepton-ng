<?php

////////// Fonts //////////////////////////////////////////////////////////////

    /**
     * Class to wrap a font instance. Allows manipulation of the font once
     * assigned.
     */
    class ImageFont {

        private $font = array();

        /**
         * Constructor, attempts to load the font from the paths defined in the
         * lepton.graphics.fontpaths key or the default locations.
         *
         * @param string $fontname The font name
         * @param int $fontsize The size
         */
        function __construct($fontname, $fontsize) {
            $p = config::get('lepton.graphics.fontpaths', array('./','./fonts/','../','/usr/share/fonts/truetype/'));
            foreach($p as $fp) {
                if (@file_exists($fp.$fontname) === true) {
                    $fullname = $fp.$fontname;
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
            //imagettftext($this->hImage, $fontsize, $angle, $x, $y, $color, $fontname, $text);

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

?>
