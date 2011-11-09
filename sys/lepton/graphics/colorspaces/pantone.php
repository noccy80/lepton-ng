<?php

using('lepton.graphics.colorspaces.rgb');
using('lepton.graphics.graphics');

/**
 * WARNING! Pantone(r) and the Pantone Color Scale(r) is the proprietary 
 * property of Pantone, and use of this class may require a license from
 * Pantone.
 */

class PantoneColor extends RgbColor {
    
    private $red;
    private $green;
    private $blue;
    
    function __construct($pms) {
        if (!globals::get('pantonescale')) { die("pantone.dat not found!"); }
        $vals = globals::get('pantonescale');
        if (!arr::hasKey($vals,strtoupper($pms))) {
            throw new ColorException(sprintf("Pantone color %s not known!", $pms));
        }
        $val = $vals[strtoupper($pms)];
        $this->red = $val['r'];
        $this->green = $val['g'];
        $this->blue =$val['b'];
    }

    /**
     * Utility function to ensure a value is within the range 0-255
     * @param integer $value The input value
     * @param integer The output value
     */
    private function bounds($value) {
        return ($value > 255) ? 255 : ($value < 0) ? 0 : $value;
    }

    function getRGBA() {
        return array($this->red, $this->green, $this->blue, 255);
    }

    function setRGBA($rgba) {
        $this->red = $this->bounds($rgba[0]);
        $this->green = $this->bounds($rgba[1]);
        $this->blue = $this->bounds($rgba[2]);
        $this->alpha = 255;
    }

    function __get($key) {
        switch ($key) {
            case 'red':
            case 'r':
                return $this->red;
            case 'green':
            case 'g':
                return $this->green;
            case 'blue':
            case 'b':
                return $this->blue;
            case 'alpha':
            case 'a':
                return 255;
            case 'hex':
                return sprintf('#%02.x%02.x%02.x', $this->red, $this->green, $this->blue);
            case 'hexstr':
                return sprintf('%02.x%02.x%02.x', $this->red, $this->green, $this->blue);
        }
        return null;
    }

    function __set($key, $value) {
        switch ($key) {
            case 'red':
            case 'r':
                $this->red = $this->bounds($value);
                break;
            case 'green':
            case 'g':
                $this->green = $this->bounds($value);
                break;
            case 'blue':
            case 'b':
                $this->blue = $this->bounds($value);
                break;
            case 'alpha':
            case 'a':
                $this->alpha = 255;
        }
    }

    
}

function pantone($pms) {
    return new PantoneColor($pms);
}

$fn = base::sysPath().'lepton/graphics/colorspaces/pantone.sd';
if (file_exists($fn)) {
    globals::set('pantonescale',unserialize(file_get_contents($fn)));
}
