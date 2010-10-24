<?php

    /**
     * @class  ImageCanvas
     * @author Christopher Vagnetoft <noccy@chillat.net>
     *
     * The ImageCanvas class contains all the methods needed to work with an
     * image file, including drawing, resizing and applying filters.
     *
     * @todo   Private properties should be prefixed with an underscore.
     * @note   This class uses late loading, meaning that it will load the
     *         image as required. F.ex. simply calling write() after loading
     *         will send the file through as it is. Calling on resize() or any
     *         drawing related functions will load the image. Polling the width
     *         or image format will read the metadata.
     */
    class ImageCanvas {

        private $himage = null;
        private $width = 0;
        private $height = 0;
        private $imgtype = null;
        private $imgsize = null;
        private $imgmime = "";
        private $filename = "";

        private $gotmeta = false;
        private $gotimage = false;

        const KEEP_NONE = 0;  // Ignore the aspect ratio
        const KEEP_CROP = 1;  // Maintain aspect, crop and fill
        const KEEP_FILL = 2;  // Maintain aspect, simply pad

        /**
         * Property overloading to get tag information and image properties.
         *
         * @param string $key The key to query
         * @return any
         */
        function __get($key) {
            switch($key) {
                case 'exif':
                    if (!$this->_exif)
                        $this->_exif = new ImageExif($this->filename);
                    return $this->_exif;
                case 'iptc':
                    if (!$this->_iptc)
                        $this->_iptc = new ImageIptc($this->filename);
                    return $this->_iptc;
                case 'height':
                    return $this->height;
                case 'width':
                    return $this->width;
            }
        }

        /**
         * Attempts to retrieve the Exif data from the image.
         *
         * @deprecated since 0.2.1
         * @return ImageExif The exif information object
         */
        function getExif() {
            return new ImageExif($this->filename);
        }

        /**
         * Attempts to retrieve the Iptc data from the image.
         *
         * @deprecated since 0.2.1
         * @return ImageIptc The itpc information object
         */
        function getIptc() {
            $iptc = new ImageIptc($this);
            return $iptc;
        }

        /**
         * Load image from file
         *
         * @param string $filename The filename
         */
        function __loadFromFile($filename) {
            // Check if the file exist
            if (file_exists($filename)) {
                // On success, cache the filename for use with the save() function
                $this->readcanvas = false;
                $this->readmeta = false;
                $this->filename = $filename;
            } else {
                throw new GraphicsException("File not found", GraphicsException::ERR_FILE_NOT_FOUND);
            }
        }

        function __construct($width=null,$height=null,Color $color = null) {
            if ($width && $height) {
                $this->__create($width,$height,$color);
            }
        }

        /**
         * Create a new image, normally called from the Graphics::create() method
         *
         * @param int $width The width of the canvas to create
         * @param int $height The height of the canvas to create
         * @param Color $color The color to fill width
         */
        function __create($width,$height,Color $color = null) {
            // TODO: Create a new file
            $this->himage = imagecreatetruecolor($width,$height);
            $this->gotmeta = true; // We set this to true to avoid trying to read metadata
            $this->gotimage = true; // We created the image so we got it
            $this->width = $width;
            $this->height = $height;
            if ($color != null) {
                imagefilledrectangle($this->himage,0,0,$this->width,$this->height,$color->getColor($this->himage));
            }
        }

        /**
         * Saves the image. If no filename is specified, the one used to load
         * the file is used. If no filename can be determined, an exeption is
         * thrown. The content type is determined from the filename.
         *
         * @param string $filename The filename to write to (optional)
         */
        function save($filename=null) {
            $this->checkImage();
            $fn = ($filename)?$filename:$this->filename;
            if (preg_match('/\.([a-z0-9]+?)$/', strtolower($fn), $ret)) {
                switch($ret[1]) {
                    case 'jpg':
                    case 'jpeg':
                        $ret = imagejpeg($this->himage, $fn);
                        break;
                    case 'png':
                        $ret = imagepng($this->himage, $fn);
                        break;
                    default:
                        throw new GraphicsException("Unknown format", GraphicsException::ERR_SAVE_FAILURE);
                }
                if (!$ret) {
                    throw new GraphicsException("Failed to save image", GraphicsException::ERR_SAVE_FAILURE);
                }
            } else {
                throw new GraphicsException("Invalid filename", GraphicsException::ERR_SAVE_FAILURE);
            }
        }

        /**
         * Outputs the content to the client. Sets the appropriate content-
         * type first.
         *
         * @param string $contenttype The content type of the output
         * @param int $qualitycompression Quality/Compression (in percent)
         * @param boolean $return If true return data rather than output
         */
        function output($contenttype='image/png', $qualitycompression=75, $return=false) {
            $this->checkImage();
            if (!$return) {
                response::contentType($contenttype);
            } else {
                response::buffer(true);
            }
            switch($contenttype) {
                case 'image/png':
                    imagesavealpha($this->himage, true);
                    $compression = floor(($qualitycompression / 100)*9);
                    imagepng($this->himage, null, $compression);
                    break;
                case 'image/jpeg':
                case 'image/jpg':
                    $quality = $qualitycompression;
                    imagejpeg($this->himage, null, $quality);
                    break;
            }
            if ($return) {
                $img = response::getBuffer();
                return $img;
            }
        }

        /**
         * Private function to make sure that the metadata is loaded. Any
         * method that works on image metadata should call on this function
         * prior.
         */
        private function checkMeta() {
            if ($this->gotmeta) return true;
            $meta = getimagesize($this->filename);
            if ($meta !== false) {
                $this->width = $meta[0];
                $this->height = $meta[1];
                $this->imgtype = $meta[2];
                $this->imgsize = $meta[3];
                $this->imgmime = $meta['mime'];
            } else {
                throw new GraphicsException("Couldn't read metadata from file", GraphicsException::ERR_META);
            }
            $this->gotmeta = true;
            return true;
        }

        /**
         * Private function to make sure that the image is loaded. Any method
         * that tworks on the actual image handle should call on this function
         * first as the himage handle may not be instantiated already.
         *
         * @exception GraphicsException
         * @return bool True if the image is loaded
         */
        private function checkImage() {
            if ($this->gotimage) return true;
            $img = @imagecreatefromstring(file_get_contents($this->filename));
            if ($img) {
                $this->himage = $img;
                $this->gotimage = true;
            } else {
                throw new GraphicsException("Failed to load the image.", GraphicsException::ERR_LOAD_FAILURE);
            }
            return true;
        }

        /**
         * Fill the canvas with the specified color.
         *
         * @param Color $fillcolor The Color() to fill with.
         */
        function fillCanvas(Color $fillcolor) {
            $this->checkImage();
            $w = imageSX($this->himage);
            $h = imageSY($this->himage);
            imagefilledrectangle($this->himage, 0, 0, $w, $h, $fillcolor->getColor($this->himage));
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
         * Get the width of the image
         *
         * @return int The width of the image
         */
        function getWidth() {
            if ($this->checkMeta()) { return $this->width; }
            throw new GraphicsException("Couldn't read metadata", GraphicsException::ERR_GENERIC);
        }

        /**
         * Get the height of the image
         *
         * @return int The height of the image
         */
        function getHeight() {
            if ($this->checkMeta()) { return $this->height; }
            throw new GraphicsException("Couldn't read metadata", GraphicsException::ERR_GENERIC);
        }

        /**
         *
         *
         */
        function getColors() {
            $this->checkImage();
            $total = imagecolorstotal( $this->himage );
        }

        /**
         *
         *
         */
        function getImageInfo() {
            getimagesize($this->filename, $info);
            return $info;
        }

        /**
         * Resize the image to the specified width and height, optionally
         * keeping the aspect ratio and pads or crops the resulting image to
         * the desired dimensions.
         *
         * @param Integer $width The desired width
         * @param Integer $height The desired height
         * @param Integer $keepaspect One of KEEP_NONE, KEEP_CROP, and KEEP_FILL
         * @param Color $fillcolor The color to fill with (if KEEP_FILL)
         */
        function resize($width,$height,$keepaspect=ImageCanvas::KEEP_NONE,Color $fillcolor=null) {

            $this->checkImage();
            $cw = imageSX($this->himage);
            $ch = imageSY($this->himage);
            $nr = (float)($width/$height); // Get new aspect ratio
            $cr = (float)($cw/$ch); // Get current aspect ratio

            switch($keepaspect){
                case ImageCanvas::KEEP_NONE:
                    $n = imagecreatetruecolor($width,$height);
                    imagecopyresampled($n,$this->himage,0,0,0,0,$width,$height,$cw,$ch);
                    break;
                case ImageCanvas::KEEP_CROP:
                    $ratio = $cw/$ch;
                    if ($width/$height > $ratio) {
                        $nh = round($width/$ratio);
                        $nw = $width;
                    } else {
                        $nw = round($height*$ratio);
                        $nh = $height;
                    }
                    $m = imagecreatetruecolor($nw,$nh);
                    imagecopyresampled($m, $this->himage, 0, 0, 0, 0, $nw, $nh, $cw, $ch);
                    $n = imagecreatetruecolor($width, $height);
                    imagecopyresampled($n, $m, 0, 0, ($nw-$width)/2, ($nh-$height)/2,
                        $width, $height, $width, $height);
                    imagedestroy($m);
                    break;
                case ImageCanvas::KEEP_FILL:
                    $ratio = $cw/$ch;
                    $cw <= $width ? $nw = $cw : $nw = $width;
                    $nh = round($ch * $nw / $cw);
                    if ($nh > $height) {
                        $nw = ($cw * $height / $ch);
                        $nh = $height;
                    }
                    $n = imagecreatetruecolor($width,$height);
                    imagefilledrectangle($n, 0, 0, $width, $height, $fillcolor->getColor($this->himage));
                    imagecopyresampled($n, $this->himage, round(($width-$nw)/2), round(($height-$nh)/2),
                        0, 0, $nw, $nh, $cw, $ch);
                    break;
            }

            imagedestroy($this->himage);
            $this->himage = $n;
        }

        /**
         * Crop the image to the specified dimensions. The image will be
         * cropped from the center.
         *
         * @param int $width The new width
         * @param int $height The new height
         */
        function cropTo($width,$height) {

            $this->checkImage();
            // Get the current width and height
            $cw = imageSX($this->himage);
            $ch = imageSY($this->himage);
            // Calculate position to crop at
            $x = ($cw/2)-($width/2);
            $y = ($ch/2)-($height/2);
            // Create the new image and crop into the new canvas
            $n = imagecreatetruecolor($width,$height);
            imagecopy($n,$this->himage,0,0,$x,$y,$width,$height);
            // Destroy the original and replace with cropped copy
            imagedestroy($this->himage);
            $this->himage = $n;

        }

        function cropToXy($x, $y, $width, $height) {

            $this->checkImage();
            // Get the current width and height
            $cw = imageSX($this->himage);
            $ch = imageSY($this->himage);
            // Create the new image and crop into the new canvas
            $n = imagecreatetruecolor($width,$height);
            imagecopy($n,$this->himage,0,0,$x,$y,$width,$height);
            // Destroy the original and replace with cropped copy
            imagedestroy($this->himage);
            $this->himage = $n;

        }

        /**
         * Set a pixel in the image
         *
         * @param int $x The X offset of the pixel to set
         * @param int $y The Y offset of the pixel to set
         * @param Color $color The color to set to
         */
        function setPixel($x, $y, Color $color) {
            $this->checkImage();
            imagesetpixel($this->himage,$x,$y,$color->getColor($this->himage));
        }

        /**
         * Get the color of a pixel in the image
         *
         * @param int $x The X offset of the pixel to get
         * @param int $y The Y offset of the pixel to get
         * @return Color The color of the pixel
         */
        function getPixel($x, $y) {
            $this->checkImage();
            return new Color(imagecolorat($this->himage,$x,$y));
        }

        /**
         * Draw a line between the coordinates specified by {x1,y1} and {x2,y2}
         *
         *
         *
         */
        function drawLine($x1, $y1, $x2, $y2, Color $color) {
            $this->checkImage();
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
            $this->checkImage();
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
            $this->checkImage();
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
            $this->checkImage();
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
            $this->checkImage();
            $sa = ($start - 90) % 360;
            $ea = ($end - 90) % 360;
            if (function_exists('imagefilledarc')) {
                imagefilledarc($this->himage,$x,$y,$width,$height,$sa,$ea,$color->getColor($this->himage),IMG_ARC_PIE);
            } else {
                $ch = $color->getColor($this->himage);
                imagearc($this->himage,$x,$y,$width,$height,$sa,$ea,$ch);
                $p1x = cos(($sa * PI) / 180); $p1y = sin(($sa * PI) / 180);
                $p2x = cos(($ea * PI) / 180); $p2y = sin(($ea * PI) / 180);
                imageline($this->himage,$x,$y,$x+$p1x*($width/2),$y+$p1y*($height/2),$ch);
                imageline($this->himage,$x,$y,$x+$p2x*($width/2),$y+$p2y*($height/2),$ch);
                $xmin = min($x,$p1x,$p2x); $xmax = max($x,$p1x,$p2x);
                $ymin = min($y,$p1y,$p2y); $ymax = max($y,$p1y,$p2y);
                $xc = ($xmax+$xmin)/2; $yc = ($ymax+$ymin)/2;
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

            $this->checkImage();

            $c = $color->getColor($this->himage);
            for($n = 0; $n < count($points) - 1; $n++) {
                imageline($this->himage,
                    $points[$n][0], $points[$n][1],
                    $points[$n+1][0], $points[$n+1][1],
                    $c);
            }

        }


        /**
         * Apply a filter or transformation to the image.
         *
         * @param ImageFilter $filter The filter to apply
         * @todo Reread metadata after processing
         */
        function apply(ImageFilter $filter) {
            $this->checkImage();
            $htemp = $filter->applyFilter($this->himage);
            if ($htemp) {
                imagedestroy($this->himage);
                $this->himage = $htemp;
            }
        }

        /**
         * Shorthand for new ImageFont(name,size)
         *
         * @param string $fontname The name of the font to load
         * @param int $fontsize The size of the font to load
         * @return ImageFont The font
         */
        function createFont($fontname, $fontsize) {
            return new ImageFont($fontname,$fontsize);
        }

        /**
         *
         *
         *
         * @param ImageFont $font The font to use
         * @param Color $color The color to use
         * @param int $x Bottomleft X coordinate
         * @param int $y Bottomleft Y coordinate
         * @param string $text The text to output
         */
        function drawText(ImageFont $font, Color $color, $x, $y, $text) {
            $this->checkImage();
            $fd = $font->__getFont();
            imagettftext(
                $this->himage,
                $fd['fontsize'], $fd['angle'], $x, $y,
                $color->getColor($this->himage),
                $fd['fontname'], $text
            );
        }

        /**
         * Draws a text within the bounding rectangle specified by the
         * parameters. The text will be wrapped if it doesn't fit within
         * the specified width.
         *
         * If x2 or y2 is null, the width and height of the image will be
         * used.
         *
         * @param ImageFont $font The font to use
         * @param Color $color The color to use
         * @param int $x1 Topleft X coordinate
         * @param int $y1 Topleft Y coordinate
         * @param int $x2 Bottomright X coordinate (or null)
         * @param int $y2 Bottomright Y coordinate (or null)
         * @param string $text The text to output
         */
        function drawTextRect(ImageFont $font, Color $color, $x1, $y1, $x2=null, $y2=null, $text) {
            $this->checkImage();

        }

    }


?>
