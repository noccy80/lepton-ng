<?php

    /**
     * Class to wrap a color and allow for basic manipulation of the color
     * components.
     */
    class Color {

        private $c = array(
            'r' => 0,
            'g' => 0,
            'b' => 0,
            'a' => 0
        );

        /**
         * Constructor. This method will accept parameters in a number of
         * different formats:
         *   (R,G,B)     red, green, and blue (0-255)
         *   (R,G,B,A)   red, green, blue and alpha(0-255)
         *   #RGB        rgb, as a hexadecimal string (0-F)
         *   #RRGGBB     rgb, as a hexadecimal string (00-FF)
         *   #RRGGBBAA   rgba, as a hexidecimal string (00-FF)
         */
        function __construct() {
            switch (func_num_args()) {
                case 0:
                    $red = 0;
                    $green = 0;
                    $blue = 0;
                    $alpha = 255;
                    break;
                case 1:{ // #RRGGBB[AA]
                    $arg = func_get_arg(0);
                    if (substr($arg,0,1) == "#") {
                        if (strlen($arg) == 9) {
                            $red =   hexdec(substr($arg,1,2));
                            $green = hexdec(substr($arg,3,2));
                            $blue =  hexdec(substr($arg,5,2));
                            $alpha = hexdec(substr($arg,7,2));
                        } elseif (strlen($arg) == 7) {
                            $red =   hexdec(substr($arg,1,2));
                            $green = hexdec(substr($arg,3,2));
                            $blue =  hexdec(substr($arg,5,2));
                            $alpha = 255;
                        } elseif (strlen($arg) == 4) {
                            $red =   hexdec(str_repeat(substr($arg,1,1),2));
                            $green = hexdec(str_repeat(substr($arg,2,1),2));
                            $blue =  hexdec(str_repeat(substr($arg,3,1),2));
                            $alpha = 255;
                        }
                    } else {
                        throw new GraphicsException("Invalid color specification", GraphicsException::ERR_BAD_COLOR);
                    }
                    break;
                }
                case 3:{ // (R,G,B)
                    $red =      func_get_arg(0);
                    $green = func_get_arg(1);
                    $blue =  func_get_arg(2);
                    $alpha = 255;
                    break;
                }
                case 4:{ // (R,G,B,A)
                    $red =      func_get_arg(0);
                    $green = func_get_arg(1);
                    $blue =  func_get_arg(2);
                    $alpha = func_get_arg(3);
                    break;
                }
                default:{
                    throw new GraphicsException("Invalid color specification", GraphicsException::ERR_BAD_COLOR);
                }
            }
            $this->c['r'] = $this->bounds($red);
            $this->c['g'] = $this->bounds($green);
            $this->c['b'] = $this->bounds($blue);
            $this->c['a'] = $this->bounds($alpha);
        }

        /**
         * Utility function to ensure a value is within the range 0-255
         * @param integer $value The input value
         * @param integer The output value
         */
        private function bounds($value) {
            return ($value>255)?255:($value<0)?0:$value;
        }

        /**
         * Returns a color allocated from the image. If the second parameter
         * is true, the alpha value will be used.
         *
         * @param hImage $himage The image to allocate from
         * @param boolean $withalpha If true, returns RGBA value
         * @return hColor The color bound to the image
         */
        function getColor($himage,$withalpha=false) {
            if ($withalpha) {
                return imagecolorallocatealpha($himage, $this->c['r'], $this->c['g'], $this->c['b'], $this->c['a']);
            }
            return imagecolorallocate($himage, $this->c['r'], $this->c['g'], $this->c['b']);
        }

        function __get($key) {
            switch($key) {
                case 'red': 
                case 'r':
                    return $this->c['r'];
                case 'green': 
                case 'g':
                    return $this->c['g'];
                case 'blue': 
                case 'b':
                    return $this->c['b'];
                case 'hex': 
                    return sprintf('#%2.x%2.x%2.x', $this->c['r'], $this->c['g'], $this->c['b']);
            }
            return null;
        }

        function __set($key,$value) {
            switch($key) {
                case 'red':
                case 'r':
                    $this->c['r'] = $value;
                    break;
                case 'green':
                case 'g':
                    $this->c['g'] = $value;
                    break;
                case 'blue':
                case 'b':
                    $this->c['b'] = $value;
                    break;
            }
        }

        /**
         * Dump color information for testing
         *
         * @internal
         */
        function dump() {
            var_dump($this->c);
        }

    }

    ModuleManager::load('lepton.graphics.colorspaces.*');

?>
