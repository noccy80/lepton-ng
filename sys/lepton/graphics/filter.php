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


?>
