#!/usr/bin/php
<?php

include('sys/base.php');
using('lepton.graphics.canvas');
using('lepton.graphics.renderers.gradient');
using('lepton.graphics.renderers.code39');
using('lepton.graphics.filters.blur');

$grad1 = new GradientRenderer( new RgbColor(255,0,0), new RgbColor(0,255,0) );
$grad2 = new GradientRenderer( new RgbColor(0,255,0), new RgbColor(0,0,255) );

$code39 = new Code39Renderer("0123456789");

$i = new Canvas(400,400);
$grad1->draw( $i, 0,  0,  400, 400);
$grad2->draw( $i, 50, 50, 300, 300);
$code39->draw( $i, 100, 360, 200, 20);

$i->apply( new BlurImageFilter() );

$i->saveImage("test.png");
