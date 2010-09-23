<?php



    if (!defined('PI')) define('PI',3.1415926535897931);

    /**
     * @class Graphics
     * @brief Lepton Graphics library
     *
     * This class wraps the basic functionality of the Lepton image
     * management class
     *
     * @since 0.2
     * @author Christopher Vagnetoft <noccy@chillat.net>
     */
    class Graphics {

        /**
         * Create a new canvas and load the requested file into it
         *
         * @param string $filename The name of the file to load
         * @return ImageCanvas The canvas
         */
        static function load($filename) {
            $i = new Canvas();
            $i->__loadFromFile($filename);
            return $i;
        }

        /**
         * Create a new canvas of the specified dimensions
         *
         * @param int $width The width
         * @param int $height The height
         * @return ImageCanvas The canvas
         */
        static function create($width, $height, Color $color = null) {
            $i = new Canvas($width, $height, $color);
            return $i;
        }

        static function render($width,$height,CanvasRenderer $renderer) {
            $c = new Canvas($width,$height);
            $renderer->render($c);
            return $c;
        }

    }

    class GraphicsException extends Exception {
        const ERR_GENERIC = 0;
        const ERR_FILE_NOT_FOUND = 1;
        const ERR_META = 2;
        const ERR_BAD_COLOR = 3; /// Bad color value
        const ERR_BAD_FONT = 4; /// Bad font
        const ERR_LOAD_FAILURE = 5;
        const ERR_SAVE_FAILURE = 6;
    }



////////// Tag management /////////////////////////////////////////////////////

    interface IImageTagCollection extends IteratorAggregate {
        function get($key);
        function getAll();
        function set($key,$value);
        function write();
    }
    abstract class ImageTagCollection implements IImageTagCollection {
        function getIterator() {
            return new BasicIterator($this->getAll());
        }
    }

    /**
     * Iptc tag class
     *
     * @author David Gidwani <dav@fudmenot.info>
     */
    class ImageIptc extends ImageTagCollection {

        const FLD_OBJECT_NAME = 005;
        const FLD_EDIT_STATUS = 007;
        const FLD_PRIORITY = 010;
        const FLD_CATEGORY = 015;
        const FLD_SUPPLEMENTAL_CATEGORY = 020;
        const FLD_FIXTURE_IDENTIFIER = 022;
        const FLD_KEYWORDS = 025;
        const FLD_RELEASE_DATE = 030;
        const FLD_RELEASE_TIME = 035;
        const FLD_SPECIAL_INSTRUCTIONS = 040;
        const FLD_REFERENCE_SERVICE = 045;
        const FLD_REFERENCE_DATE = 047;
        const FLD_REFERENCE_NUMBER = 050;
        const FLD_CREATED_DATE = 055;
        const FLD_CREATED_TIME = 060;
        const FLD_ORIGINATING_PROGRAM = 065;
        const FLD_PROGRAM_VERSION = 070;
        const FLD_OBJECT_CYCLE = 075;
        const FLD_BYLINE = 080;
        const FLD_BYLINE_TITLE = 085;
        const FLD_CITY = 090;
        const FLD_PROVINCE_STATE = 095;
        const FLD_COUNTRY_CODE = 100;
        const FLD_COUNTRY = 101;
        const FLD_ORIGINAL_TRANSMISSION_REFERENCE = 103;
        const FLD_HEADLINE = 105;
        const FLD_CREDIT = 110;
        const FLD_SOURCE = 115;
        const FLD_COPYRIGHT_STRING = 116;
        const FLD_CAPTION = 120;
        const FLD_LOCAL_CAPTION = 121;

        private $_meta = array();
        private $_img;

        function __construct($filename) {
            $this->_filename = $img;
            $this->checkIptc();
        }

        private function checkIptc() {
            getimagesize($this->_filename, $info);
            if (isset($info['APP13'])) {
                $this->_meta = iptcparse($info['APP13']);
                return true;
            }
        }

        function get($tag) {
            return isset($this->_meta["2#$tag"]) ? $this->_meta["2#$tag"][0] : false;
        }

        function getAll() {
            // TODO: Return all keys
        }

        function set($tag, $value) {
            $this->_meta["2#$tag"] = array($value);
        }

        function getBinary() {
            $data = '';
            foreach ($this->_meta as $tag => $value) {
                $tag = substr($tag, 2);
                $data .= $this->makeTag(2, $tag, $value);
            }
            return $data;
        }

        function makeTag($rec, $dat, $val) {
            $len = strlen($val);
            if ($len < 0x8000) {
                return chr(0x1c).chr($rec).chr($dat).
                chr($len >> 8).
                chr($len & 0xff).
                $val;
            } else {
                return chr(0x1c).chr($rec).chr($dat).
                chr(0x80).chr(0x04).
                chr(($len >> 24) & 0xff).
                chr(($len >> 16) & 0xff).
                chr(($len >> 8 ) & 0xff).
                chr(($len ) & 0xff).
                $val;
            }
        }

        function write() {
            $data = iptcembed($this->getBinary(), $this->_filename);
            $fp = fopen($this->_filename, 'wb');
            fwrite($fp, $data);
            fclose($fp);
        }

    }

    /**
     * Exif parsing class
     *
     * @author Christopher Vagnetoft <noccy@chillat.net>
     */
    class ImageExif extends ImageTagCollection {

        const TAG_FILENAME = 'filename';
        const TAG_FILEDATETIME = 'filedatetime';
        const TAG_FILESIZE = 'filesize';
        const TAG_MIMETYPE = 'mimetype';
        const TAG_MAKE = 'make';
        const TAG_MODEL = 'model';
        const TAG_ORIENTATION = 'orientation';
        const TAG_XRESOLUTION = 'xresolution';
        const TAG_YRESOLUTION = 'yresolution';
        const TAG_RESOLUTIONUNIT = 'resolutionunit';
        const TAG_SOFTWARE = 'software';
        const TAG_FNUMBER = 'fnumber';
        const TAG_EXPOSURETIME = 'exposuretime';
        const TAG_EXPOSUREBIAS = 'exposurebias';
        const TAG_METERINGMODE = 'meteringmode';

        private $data = array();
        private $ldata = array();

        function __construct($filename) {
            $this->data = @exif_read_data($filename);
            if (!$this->data) {
                throw new GraphicsException("The file '".$filename."' could not be found", GraphicsException::ERR_FILE_NOT_FOUND);
            } else {
                // convert all keys to lowercase
                foreach($this->data as $key => $value)
                    $this->ldata[strtolower($key)] = $value;
                $this->ldata['shutterspeed'] = $this->__getShutter();
                $this->ldata['fstop'] = $this->__getFstop();
            }
        }

        function get($key) {
            return $this->ldata[$key];
        }

        function set($key,$value) {
            $this->lldata[$key] = $value;
        }

        function getAll() {
            return $this->ldata;
        }

        private function exif_get_float($value) {
            $pos = strpos($value, '/');
            if ($pos === false) return (float) $value;
            $a = (float) substr($value, 0, $pos);
            $b = (float) substr($value, $pos+1);
            return ($b == 0) ? ($a) : ($a / $b);
        }

        private function __getShutter() {
            $exif = $this->exif;
            if (!isset($exif['ShutterSpeedValue'])) return false;
            $apex    = $this->exif_get_float($exif['ShutterSpeedValue']);
            $shutter = pow(2, -$apex);
            if ($shutter == 0) return false;
            if ($shutter >= 1) return round($shutter) . 's';
            return '1/' . round(1 / $shutter) . 's';
        }

        private function __getFstop() {
            $exif = $this->exif;
            if (!isset($exif['ApertureValue'])) return false;
            $apex  = $this->exif_get_float($exif['ApertureValue']);
            $fstop = pow(2, $apex/2);
            if ($fstop == 0) return false;
            return 'f/' . round($fstop,1);
        }

        function format($formatstring) {

        }

        function write() {

        }

    }







////////// Utility classes //////////////////////////////////////////////////

class ImageUtils {

    /**
     * imageconvolution replacement for when the gd function is missing.
     *
     * @author Chao Xu
     * @param himage $src The image
     * @param array $filter The array
     * @param float $div Filter divisor
     * @param float $offset Filter offset
     */
    static function imageconvolution($src, $filter, $div, $offset) {

        if (function_exists('imageconvolution')) {
            return imageconvolution($himage, $m, $div, $offs);
        }

        if ($src==NULL) {
            return 0;
        }

        $sx = imagesx($src); $sy = imagesy($src);
        $srcback = ImageCreateTrueColor ($sx, $sy);
        ImageCopy($srcback, $src,0,0,0,0,$sx,$sy);

        if($srcback==NULL){
            return 0;
        }

        $pxl = array(1,1);

        for ($y=0; $y<$sy; ++$y){
            for($x=0; $x<$sx; ++$x){
                $new_r = $new_g = $new_b = 0;
                $alpha = imagecolorat($srcback, $pxl[0], $pxl[1]);
                $new_a = $alpha >> 24;

                for ($j=0; $j<3; ++$j) {
                    $yv = min(max($y - 1 + $j, 0), $sy - 1);
                    for ($i=0; $i<3; ++$i) {
                            $pxl = array(min(max($x - 1 + $i, 0), $sx - 1), $yv);
                        $rgb = imagecolorat($srcback, $pxl[0], $pxl[1]);
                        $new_r += (($rgb >> 16) & 0xFF) * $filter[$j][$i];
                        $new_g += (($rgb >> 8) & 0xFF) * $filter[$j][$i];
                        $new_b += ($rgb & 0xFF) * $filter[$j][$i];
                    }
                }

                $new_r = ($new_r/$div)+$offset;
                $new_g = ($new_g/$div)+$offset;
                $new_b = ($new_b/$div)+$offset;

                $new_r = ($new_r > 255)? 255 : (($new_r < 0)? 0:$new_r);
                $new_g = ($new_g > 255)? 255 : (($new_g < 0)? 0:$new_g);
                $new_b = ($new_b > 255)? 255 : (($new_b < 0)? 0:$new_b);

                $new_pxl = ImageColorAllocateAlpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
                if ($new_pxl == -1) {
                    $new_pxl = ImageColorClosestAlpha($src, (int)$new_r, (int)$new_g, (int)$new_b, $new_a);
                }
                if (($y >= 0) && ($y < $sy)) {
                    imagesetpixel($src, $x, $y, $new_pxl);
                }
            }
        }
        imagedestroy($srcback);
        return 1;
    }

}

?>
