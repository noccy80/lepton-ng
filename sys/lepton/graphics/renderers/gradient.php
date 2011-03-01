<?php

	using('lepton.graphics.drawable');

    // Gradient renderer
    class GradientRenderer extends Drawable {

        private $colors;
        private $direction;
        function __construct(Color $color1,Color $color2,$direction=0) {
            $this->direction = $direction;
            $cf = array($color1->r, $color1->g, $color1->b);
            $cl = array($color2->r, $color2->g, $color2->b);
            $cd = array($cl[0]-$cf[0], $cl[1]-$cf[1], $cl[2]-$cf[2]);
            $this->colors = array(
                'first' => $cf,
                'last' => $cl,
                'delta' => $cd
            );
        }

        function draw(Canvas $dest,$x,$y,$width=0,$height=0) {

			$image = new Canvas($width,$height);

            $grad = $height; // Top down
            $this->colors['step'] = array(
                (float)($this->colors['delta'][0] / $grad),
                (float)($this->colors['delta'][1] / $grad),
                (float)($this->colors['delta'][2] / $grad)
            );

			$w = $width;
            for($n = 0; $n < $grad; $n++) {
                $c = new RgbColor(
                    floor($this->colors['first'][0] + ($this->colors['step'][0] * $n)),
                    floor($this->colors['first'][1] + ($this->colors['step'][1] * $n)),
                    floor($this->colors['first'][2] + ($this->colors['step'][2] * $n))
                );
                // Console::debug("Row %d: rgb(%d,%d,%d)", $n, $c->r, $c->g, $c->b);
                $image->drawLine(0,$n,$w,$n,$c);
            }

			imagecopy($dest->getImage(), $image->getImage(), $x, $y, 0, 0, $width, $height);

        }

    }

?>
