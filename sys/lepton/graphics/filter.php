<?php

    /**
     * ImageFilter Interface
     */
    interface IImageFilter {
        function applyFilter($himage);
    }
    /**
     * Abstract class for ImageFilter implementations. Enforces the IImageFilter
     * interface.
     */
    abstract class ImageFilter implements IImageFilter {
        public function apply($himage) {
            $htemp = $this->applyFilter($himage);
            return $htemp;
        }
        protected function transform($himage) {
            $tmp = imagecreatetruecolor(imagesx($himage),imagesy($himage));
            for ( $x = 0; $x < imagesx($himage); $x++ ) {
                for ( $y = 0; $y < imagesy($himage); $y++) {
                    imagesetpixel($tmp, $x, $y, $this->applyTransformation($himage, $x, $y));
                }
            }
            imagecopy($himage, $tmp, 0, 0, 0, 0, imagesx($tmp), imagesy($tmp));
            imagedestroy($tmp);
        }
    }


    /**
     * PNG ALPHA CHANNEL SUPPORT for imagecopymerge();
     * @author Sina Salek <http://sina.salek.ws/en/contact>
     * @todo Merge with applyfilter function
     */
    function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
        $opacity=$pct;
        $w = imagesx($src_im);
        $h = imagesy($src_im);

        $cut = imagecreatetruecolor($src_w, $src_h);
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        $opacity = 100 - $opacity;

        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
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
