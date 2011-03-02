#!/usr/bin/php
<?php

include('sys/base.php');
using('lepton.graphics.canvas');
using('lepton.graphics.renderers.gradient');
using('lepton.graphics.renderers.code39');

$grad1 = new GradientRenderer( new RgbColor(255,0,0), new RgbColor(0,255,0) );
$grad2 = new GradientRenderer( new RgbColor(0,255,0), new RgbColor(0,0,255) );

$code39 = new Code39Renderer("0123456789");

$i = new Canvas(400,400);
$grad1->draw( $i, 0,  0,  400, 400);
$grad2->draw( $i, 50, 50, 300, 300);
$code39->draw( $i, 50, 360, 300, 20);

$i->saveImage("test.png");
