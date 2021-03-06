<?php

using('lepton.graphics.drawable');

// Gradient renderer
class GradientRenderer extends Drawable {

    private $colors;
    private $direction;

    function __construct(Color $color1,Color $color2,$direction=0) {
        $this->direction = $direction;
        $cs = $color1->getRGBA();
        $cd = $color2->getRGBA();
        $cf = array($cs[0],$cs[1],$cs[2]);
        $cl = array($cd[0],$cd[1],$cd[2]);
        $cd = array($cl[0]-$cf[0], $cl[1]-$cf[1], $cl[2]-$cf[2]);
        $this->colors = array(
            'first' => $cf,
            'last' => $cl,
            'delta' => $cd
        );
     }

    function draw(Canvas $dest,$x=null,$y=null,$width=null,$height=null) {

        $image = new Canvas($width,$height);
        $p = $image->getPainter();

        if (!$x) $x = 0;
        if (!$y) $y = 0;
        if (!$width) $width = $dest->width;
        if (!$height) $height = $dest->height;
        if ($width && $height) {
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
                $p->drawLine(0,$n,$w,$n,$c);
            }

            imagecopy($dest->getImage(), $image->getImage(), $x, $y, 0, 0, $width, $height);
        } else {
            throw new BadArgumentException();
        }
    }

}
