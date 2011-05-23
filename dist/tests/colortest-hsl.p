#!/usr/bin/php
<?php
/**
 * @file colortest.p
 * @brief Renders a sample image showing off the HSL color space
 *
 * The image will be saved as colortest.png in the current folder.
 *
 * @license GPL v3 or later
 * @todo Conform to an application using the ConsoleApplication class
 * @author Christopher Vagnetoft
 */

// Import all the necessary classes
require('sys/base.php');
using('lepton.graphics.*');
using('lepton.graphics.colorspaces.*');

// Create a canvas and acquire a painter
$i = new Canvas(360+512,512,rgb(255,255,255));
$p = $i->getPainter();

$i->alphablending = false;

// Draw the hue swatches
for ($x = 0; $x < 359; $x++) {
	for ($y = 0; $y < 255; $y++) {
		$c = hsl($x,$y,128);
		$p->setPixel($x, $y, $c);
		$c = hsl($x,128,$y);
		$p->setPixel($x, $y+256, $c);
	}
}

// Draw the RGB color swatches
for ($x = 0; $x < 255; $x++) {
	for ($y = 0; $y < 255; $y++) {
		$c = hsl(0,$x,$y); $p->setPixel(360+$x,$y,$c);
		$c = hsl(120,$x,$y); $p->setPixel(360+$x,256+$y,$c);
		$c = hsl(240,$x,$y); $p->setPixel(360+256+$x,$y,$c);
	}
}

// Create the font, and assign it a black outline
$f = new BitmapFont(2);
$f->setTextEffect(BitmapFont::EFFECT_OUTLINE,rgb(0,0,0));

// Draw labels on the various swatches
$i->drawText($f,rgb(255,255,255),5,5,"L=128");
$i->drawText($f,rgb(255,255,255),5,256+5,"S=128");
$i->drawText($f,rgb(255,255,255),365,5,"H=0");
$i->drawText($f,rgb(255,255,255),365+256,5,"H=120");
$i->drawText($f,rgb(255,255,255),365,256+5,"H=240");

// Save the image
echo "Saving colortest-hsl.png\n";
$i->save('colortest-hsl.png');
