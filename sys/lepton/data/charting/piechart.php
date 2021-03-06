<?php

using('lepton.math');
using('lepton.data.charting');
using('lepton.data.charting.renderers.*');

/**
 * @class PieChart
 * @brief Render pie charts
 * 
 * This class renders pie charts from the provided dataset. The various series
 * will be summed and used for each of the slices. Hence this class expects
 * one or more dataseries in the set to function properly.
 * 
 * The charting engine is to be able to support both 2D as well as 3D pie
 * charts, but at this time only the 3D charts are supported.
 * 
 * The attributes supported are:
 * 
 *   - style: One of PieChart::STYLE_2D or PieChart::STYLE_3D
 *   - palette: Colors for each of the slices
 *   - explode: the number of pixels to offset the slices from the middle
 * 
 * @todo Restructure the rendering so that the sum of the series are used
 * @todo Add attributes for the thickness of the 3D chart
 * @todo Implement 2D charts
 * @author Christopher Vagnetoft
 */
class PieChart extends Chart {

    const STYLE_2D = '2d';
    const STYLE_3D = '3d';

    /**
     * Constructor; creates a new pie chart
     * 
     * @param type $width
     * @param type $height 
     */
    function __construct($width,$height) {
        $this->setProperties(array(
            'legend' => true,
            'style' => PieChart::STYLE_3D,
            'palette' => array(
                '#FF7777',
                '#77FF77',
                '#7777FF',
                '#FFFF77',
                '#77FFFF',
                '#FF77FF',
                '#FFAAFF'
            )
        ));
        parent::__construct($width,$height);
    }

    /**
     * @brief Render the chart
     * 
     * @return type 
     */
    public function render() {
        return $this->render3D();
    }
    
    /**
     * @brief Render the chart in 3D
     * 
     * @return Canvas 
     */
    private function render3D() {

        $c = new Canvas($this->width, $this->height, rgb($this->getProperty('background','#FFFFFF')));

        $radiusx = 180;
        $radiusy = 90;

        $cx = $c->getWidth() / 2;
        $cy = $c->getHeight() / 2;
        $explode = $this->getProperty('explode',0);

        $palette = $this->getProperty('palette');

        list($label,$vals) = $this->dataset->getSeries(0);
        $labels = $this->dataset->getLabels();
        $sum = $vals->getSum();
        $ci = 0;
        $sa = 0;
        for($n = 0; $n < $vals->getCount(); $n++) {
            list($val,$key) = $vals->getValue($n);
            $a = (360/$sum)*$val; // Get angle
            $ea = $sa + $a;
            $ch = rgb($palette[$ci]);
            $cs = hsv($ch);
            $cs->value = $cs->value - 30;
            if (arr::hasKey($labels,$n))
                $l = $labels[$n];
                else $l = $n;
            $data[] = array(
                'key' => $key,
                'label' => $l,
                'c1' => $ch,
                'c2' => $cs,
                'sa' => $sa,
                'ea' => $ea
            );
            $sa=$ea;
            $ci++;
        }

        $offs = array();
        foreach($data as $id=>$slice) {
            $avg = ($slice['sa'] + $slice['ea']) / 2;
            $data[$id]['ox'] = cos(((($avg-90)%360) * PI) / 180) * $explode;
            $data[$id]['oy'] = sin(((($avg-90)%360) * PI) / 180) * $explode;
            $data[$id]['dx'] = cos(((($avg-180)%360) * PI) / 180);
            $data[$id]['dy'] = sin(((($avg-180)%360) * PI) / 180);
        }

        $f = new BitmapFont(3);
        $f->setTextEffect(BitmapFont::EFFECT_OUTLINE,rgb(255,255,255));
        $p = $c->getPainter();
        for ($yp = 20; $yp >= 0; $yp--) {
            foreach($data as $slice) {
                $ox = $slice['ox'];
                $oy = $slice['oy'];
                $p->drawFilledArc($cx+$ox,$cy+$oy+$yp,$radiusx*2,$radiusy*2,$slice['sa'],$slice['ea'],($yp==0)?$slice['c1']:$slice['c2']);
            }
        }
        for ($yp = 20; $yp >= 0; $yp--) {
            foreach($data as $slice) {
                // TODO: Labels
                $m = $f->measure($slice['label']);
                $dx = $slice['dx'];
                $dy = $slice['dy'];
                $c->drawText($f, rgb(0,0,0), $cx+($dx*$radiusx/1.5)-($m['width']/2),$cx+($dy*$radiusy/1.5)-($m['height']/2),$slice['label']);
            }
        }
        $this->renderObjects($c);
        // Return the canvas
        return $c;
    
    }

}


