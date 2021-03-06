<?php

interface IColor {
    function getRGBA();
    function setRGBA($rgba);
}

class ColorException extends BaseException { }

abstract class Color implements IColor {
    /**
     * Returns a color allocated from the image. If the second parameter
     * is true, the alpha value will be used.
     *
     * @param hImage $himage The image to allocate from
     * @param boolean $withalpha If true, returns RGBA value
     * @return hColor The color bound to the image
     */
    function getColor($himage, $withalpha=false) {
        list($r,$g,$b,$a) = $this->getRGBA();
        if ($withalpha) {
            return imagecolorallocatealpha($himage, $r, $g, $b, 127-floor($a/2));
        }
        return imagecolorallocate($himage, $r, $g, $b);
    }

    /**
     * @brief Helper function to assign a color value from an existing color
     *
     * @param Color $color The color to assign
     */
    function setColor(Color $color) {
        $this->setRGBA($color->getRGBA());
    }
    
    function getLuma() {
        list($r,$g,$b,$a) = $this->getRGBA();
        return (($r*0.3)+($g*0.59)+($b*0.11));
    }
    
    function set($property,$value) {
        $this->{$property} = $value;
        return $this;
    }
    
    function adjust($property,$value) {
        $this->{$property} += $value;
        return $this;
    }

    /**
     * @brief Return the RGB value of the color
     *
     * @return String The RGB balue
     */
    function __toString() {
        list($r,$g,$b,$a) = $this->getRGBA();
        return sprintf('#%02x%02x%02x',$r,$g,$b);
    }

    /**
     * @brief Convert a constructor argument to a number
     *
     */
    protected function argToValue($arg,$valmax) {
        if (is_float($arg) || is_integer($arg)) {
            if ($arg < 0) return 0;
            if ($arg > $valmax) return $valmax;
            return intval($arg);
        } else {
            throw new ColorException("Invalid argument");
        }
    }

}
