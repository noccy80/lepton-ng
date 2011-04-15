<?php

using('lepton.graphics.font');
using('lepton.graphics.drawable');
using('lepton.graphics.canvaspainter');
using('lepton.graphics.tags');
using('lepton.graphics.colorspaces.rgb');
using('lepton.graphics.exception');

interface ICanvas {
	function getImage();
	function getDimensions();
}

/**
 * @class Canvas
 * @author Christopher Vagnetoft <noccy@chillat.net>
 *
 * The ImageCanvas class contains all the methods needed to work with an
 * image file, including drawing, resizing and applying filters.
 *
 * 
 *
 * Properties:
 *   exif Access to the EXIF tag data
 *   iptc Access to the IPTC tag data
 *   width The width of the canvas
 *   height The height of the canvas
 *
 * @property get exif Return the exif information
 * @property get iptc Return the iptc information
 * @property get height The height of the image
 * @property get width The width of the image
 * @property getset alphablending True if alphablending is enabled
 *
 * @todo Private properties should be prefixed with an underscore.
 * @note This class uses late loading, meaning that it will load the
 *		 image as required. F.ex. simply calling write() after loading
 *		 will send the file through as it is. Calling on resize() or any
 *		 drawing related functions will load the image. Polling the width
 *		 or image format will read the metadata.
 * 
 */
class Canvas implements IDrawable,ICanvas {

	private $himage = null;
	private $width = 0;
	private $height = 0;
	private $imgtype = null;
	private $imgsize = null;
	private $imgmime = "";
	private $alphablending = false;
	protected $filename = "";

	protected $gotmeta = false;
	protected $gotimage = false;

	const KEEP_NONE = 0;  // Ignore the aspect ratio
	const KEEP_CROP = 1;  // Maintain aspect, crop and fill
	const KEEP_FILL = 2;  // Maintain aspect, simply pad

///// Static Methods /////////////////////////////////////////////////////////

	static function load($filename) {

		__deprecated('Canvas::load()', 'new Image()');
		return new Image($filename);

	}

///// Class Methods /////////////////////////////////////////////////////////

	/**
	 * @brief Constructor, creates a canvas based on the parameters.
	 *
	 * @note  Currently works even when called without any parameters, this
	 *		should throw an exception of operated upon! It is supported here
	 *		for the purpose of loading an image that already exists.
	 * @param integer $width The width of the canvas to create
	 * @param integer $height The height of the canvas to create
	 * @param Color $color The background color of the new canvas
	 */	
	function __construct($width,$height,Color $color = null) {

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
	 * @brief Retrieve the GD image handle
	 *
	 * @throws GraphicsException
	 * @return Resource The image handle
	 */
	function getImage() {

		$this->checkImage();
		if ($this->himage) return $this->himage;
		throw new GraphicsException("getImage called without image available");

	}

	/**
	 * @brief Assign a GD image handle to the image
	 *
	 * Used by derived classes that load or create images.
	 *
	 * @param Resource $himage The image to assign.
	 */
	function setImage($himage) {

		$this->himage = $himage;
		$this->gotmeta = false;
		$this->gotimage = true;

	}

	/**
	 * @brief Get the dimensions of the canvas.
	 *
	 * Also accessible with the width and height properties.
	 *
	 * @return array Array holding the width and height of the canvas
	 */
	function getDimensions() {

		$this->checkMeta();
		return array($this->width,$this->height);

	}

	/**
	 * @brief Property overloading to get tag information and image properties.
	 *
	 * @param string $key The key to query
	 * @return any
	 */
	function __get($key) {

		$this->checkMeta();

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
			case 'alphablending':
				return $this->alphablending;
			default:
				throw new BadPropertyException("No property get ".$key." on Canvas");
		}

	}
	
	/**
	 * @brief Property set overloading.
	 *
	 * @param string $key The key to set
	 * @param mixed $value The value
	 */
	function __set($key,$value) {
	
		$this->checkImage();
	
		switch($key) {
			case 'alphablending':
				$this->alphablending = ($value == true);
				imagealphablending($this->himage, $this->alphablending);
				break;
			default:
				throw new BadPropertyException("No property set ".$key." on Canvas");
		
		}
	
	}

	/**
	 * Attempts to retrieve the Exif data from the image.
	 *
	 * @deprecated since 0.2.1 - In favor of exif property
	 * @return ImageExif The exif information object
	 */
	function getExif() {

		__deprecated('getExif()', '$i->exif');
		return new ImageExif($this->filename);

	}

	/**
	 * Attempts to retrieve the Iptc data from the image.
	 *
	 * @deprecated since 0.2.1 - In favor of iptc property
	 * @return ImageIptc The itpc information object
	 */
	function getIptc() {

		__deprecated('getIptc()', '$i->iptc');
		$iptc = new ImageIptc($this);
		return $iptc;

	}

	/**
	 * Load image from file
	 *
	 * @deprecated Since 1.0
	 * @throws GraphicsException
	 * @param string $filename The filename
	 */
	function loadImage($filename) {

		__deprecated('canvas::load() / canvas::loadImage()','new Image()');
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

	/**
 	 * @brief Duplicate the canvas.
 	 *
	 * This function will returning a new canvas object with the same content
	 * as the duplicated one.
	 *
	 * @return Canvas The new canvas
	 */
	function duplicate() {
		
		$copy = new Canvas($this->width,$this->height);
		$this->draw($copy);
		return $copy;
		
	}

	/**
	 * @brief Retrieves a CanvasPainter for the canvas. 
	 *
	 * This is a shorthand for using new CanvasPainter($image).
	 *
	 * @return CanvasPainter The painter for the canvas
	 */
	function getPainter() {
		return new CanvasPainter($this);
	}

	/**
	 * Create a new image, normally called from the Graphics::create() method
	 *
	 * @param integer $width The width of the canvas to create
	 * @param integer $height The height of the canvas to create
	 * @param Color $color The color to fill width
	 */
	function createImage($width,$height,Color $color = null) {

		$this->himage = imagecreatetruecolor($width,$height);
		$this->gotmeta = true; // We set this to true to avoid trying to read metadata
		$this->gotimage = true; // We created the image so we got it
		$this->width = $width;
		$this->height = $height;
		if ($color != null) {
			imagefilledrectangle($this->himage,0,0,$this->width,$this->height,$color->getColor($this->himage));
		}

	}

	function saveImage($filename=null) {
		__deprecated('Canvas->saveImage()','Canvas->save()');
		return $this->save($filename);
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
		//$fn = ($filename)?$filename:$this->filename;
		if ($filename == null) throw new GraphicsException("No filename specified for save operation");
		$fn = $filename;
		if (preg_match('/\.([a-z0-9]+?)$/', strtolower($fn), $ret)) {
			switch($ret[1]) {
				case 'jpg':
				case 'jpeg':
					$ret = imagejpeg($this->himage, $fn);
					break;
				case 'png':
					$ret = imagepng($this->himage, $fn);
					break;
				case 'gif':
					$ret = imagegif($this->himage, $fn);
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
	 * @brief Outputs the content to the client after setting the appropriate 
	 * content-type.
	 *
	 * @param string $contenttype The content type of the output
	 * @param integer $qualitycompression Quality/Compression (in percent)
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
	 *
	 * @return bool True if the image metadata is loaded
	 */
	private function checkMeta() {

		if ($this->gotmeta) return true;
		if ($this->filename) {
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
		} else {
			$this->width = imageSX($this->himage);
			$this->height = imageSY($this->himage);
			$this->imgtype = null;
			$this->imgsize = null;
			$this->imgmime = null;
		}
		$this->gotmeta = true;
		return true;

	}

	/**
	 * Private function to make sure that the image is loaded. Any method
	 * that tworks on the actual image handle should call on this function
	 * first as the himage handle may not be instantiated already.
	 *
	 * @throws GraphicsException
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
	 * @brief Get the width of the image
	 * @throws GraphicsException
	 *
	 * @return integer The width of the image
	 */
	function getWidth() {

		if ($this->checkMeta()) {
			return $this->width;
		}
		throw new GraphicsException("Couldn't read metadata", GraphicsException::ERR_GENERIC);

	}

	/**
	 * @brief Get the height of the image
	 * @throws GraphicsException
	 *
	 * @return integer The height of the image
	 */
	function getHeight() {

		if ($this->checkMeta()) {
			return $this->height;
		}
		throw new GraphicsException("Couldn't read metadata", GraphicsException::ERR_GENERIC);

	}

	/**
	 * @brief Retrieve the number of colors used in the canvas
	 *
	 * @return integer The total number of colors used
	 */
	function getColors() {

		$this->checkImage();
		$total = imagecolorstotal( $this->himage );

	}

	/**
	 * @brief Retrieve information on the image
	 *
	 * @return array image information
	 */
	function getImageInfo() {

		getimagesize($this->filename, $info);
		return $info;

	}

	/**
	 * @brief Resize the image to the specified width and height
	 *
	 * optionally keeping the aspect ratio and pads or crops the resulting 
	 * image to the desired dimensions.
	 *
	 * @param Integer $width The desired width
	 * @param Integer $height The desired height
	 * @param Integer $keepaspect One of KEEP_NONE, KEEP_CROP, and KEEP_FILL
	 * @param Color $fillcolor The color to fill with (if KEEP_FILL)
	 */
	function resize($width,$height,$keepaspect=Canvas::KEEP_NONE,Color $fillcolor=null) {

		$this->checkImage();
		$this->checkMeta();
		$cw = imageSX($this->himage);
		$ch = imageSY($this->himage);
		$nr = (float)($width/$height); // Get new aspect ratio
		$cr = (float)($cw/$ch); // Get current aspect ratio

		switch($keepaspect) {
			case Canvas::KEEP_NONE:
				$n = imagecreatetruecolor($width,$height);
				imagecopyresampled($n,$this->himage,0,0,0,0,$width,$height,$cw,$ch);
				break;
			case Canvas::KEEP_CROP:
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
			case Canvas::KEEP_FILL:
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
		$this->width = imageSX($this->himage);
		$this->height = imageSY($this->himage);

	}
	
	/**
	 * @brief Rotate the canvas by the specified number of degrees
	 *
	 * @param float $degrees The number of degrees to rotate the image by
	 * @param Color $color The new background color
	 */
	function rotate($degrees, Color $color = null) {
		if (function_exists('imagerotate')) {
			if (!$color) $color = rgb(255,255,255);
			$htmp = imagerotate($this->himage, $degrees, $color->getColor($this->himage));
			imagedestroy($this->himage);
			$this->himage = $htmp;
			$this->width = imageSX($this->himage);
			$this->height = imageSY($this->himage);
		} else {
			throw new FunctionNotSupportedException("imagerotate() not present");
		}
	}

	/**
	 * @brief Crop the image to the specified dimensions. 
	 *
	 * The image will be cropped from the center.
	 *
	 * @param integer $width The new width
	 * @param integer $height The new height
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

	/**
	 * @brief Crop the image to the specified rectangle.
	 *
	 * @param integer $x The left coordinate of the cropping rectangle
	 * @param integer $y The top coordinate of the cropping rectangle
	 * @param integer $width The new width
	 * @param integer $height The new height
	 */
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
	 * @brief Apply a filter or transformation to the image.
	 *
	 * @param ImageFilter $filter The filter to apply
	 * @todo Reread metadata after processing
	 */
	function apply(ImageFilter $filter) {

		$this->checkImage();
		$htemp = $filter->applyFilter($this);
		if ($htemp) {
			imagedestroy($this->himage);
			$this->himage = $htemp;
		}

	}

	/**
	 * @brief Shorthand for new ImageFont(name,size)
	 *
	 * @param string $fontname The name of the font to load
	 * @param integer $fontsize The size of the font to load
	 * @return Font The font
	 */
	function createFont($fontname, $fontsize) {
	
		__deprecated('canvas::createFont()','new TruetypeFont()');
		return new TruetypeFont($fontname,$fontsize);

	}

	/**
	 * Draw text onto the canvas
	 *
	 * @todo  This function need to be reworked since the actual drawing 
	 *		stuff is offloaded onto the Font class.
	 * @param Font $font The font to use
	 * @param Color $color The color to use
	 * @param integer $x Bottomleft X coordinate
	 * @param integer $y Bottomleft Y coordinate
	 * @param string $text The text to output
	 */
	function drawText(IFont $font, Color $color, $x, $y, $text) {

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
	 * @todo  This function need to be reworked since the actual drawing 
	 *		stuff is offloaded onto the Font class.
	 * @param Font $font The font to use
	 * @param Color $color The color to use
	 * @param integer $x1 Topleft X coordinate
	 * @param integer $y1 Topleft Y coordinate
	 * @param integer $x2 Bottomright X coordinate (or null)
	 * @param integer $y2 Bottomright Y coordinate (or null)
	 * @param string $text The text to output
	 */
	function drawTextRect(IFont $font, Color $color, $x1, $y1, $x2, $y2, $text) {

		$this->checkImage();

	}

	/**
	 * @brief Draw the canvas onto another canvas.
	 *
	 * @param Canvas $dest The destination canvas
	 * @param integer $x The left coordinate of the destination canvas
	 * @param integer $y The top coordinate of the destination canvas
	 * @param integer $width The width of the drawing
	 * @param integer $height The height of the drawing
	 */
	function draw(Canvas $dest,$x=null,$y=null,$width=null,$height=null) {

		$dstimage = $dest->getImage();
		if (!$x) $x = 0;
		if (!$y) $y = 0;
		if (!$width) $width = $this->width;
		if (!$height) $height = $this->height;

		imagecopy($dstimage, $this->himage, $x, $y, 0, 0, $width, $height);

	}

	function getColorAt($x,$y) {
		$rgb = imagecolorat($this->himage, $x, $y);
		$cv = imagecolorsforindex($this->himage, $rgb);	
		return rgb($cv['red'],$cv['green'],$cv['blue']);
	}

}

/**
 * @class Image
 *
 * Loads an image from file into a canvas. This is the replacement of the old
 * canvas::load() method.
 */
class Image extends Canvas {

	/**
	 * @overload Canvas::__construct()
	 * @param string $filename The file to load
	 * @throws GraphicsException
	 */
	function __construct($filename) {
		// Check if the file exist
		if (file_exists($filename)) {
			// On success, cache the filename for use with the save() function
			$this->filename = $filename;
			$img = @imagecreatefromstring(file_get_contents($this->filename));
			if ($img) {
				$this->setImage($img);
				$this->gotimage = true;
			} else {
				throw new GraphicsException("Failed to load the image.", GraphicsException::ERR_LOAD_FAILURE);
			}
		} else {
			throw new GraphicsException("File not found", GraphicsException::ERR_FILE_NOT_FOUND);
		}

	}

}
