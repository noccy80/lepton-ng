#!/usr/bin/php
<?php

require('sys/base.php');
using('lepton.graphics.*');
using('lepton.graphics.colorspaces.*');

$i = new Canvas(360+512,512,rgb(255,255,255));
$p = $i->getPainter();

$i->alphablending = false;
$f = new BitmapFont(3);
$f->setTextEffect(BitmapFont::EFFECT_OUTLINE,rgb(0,0,0));

for ($x = 0; $x < 359; $x++) {
	console::write(".");
	for ($y = 0; $y < 255; $y++) {
		$c = hsv($x,$y,128);
		$p->setPixel($x, $y, $c);
		$c = hsv($x,128,$y);
		$p->setPixel($x, $y+256, $c);
	}
}

for ($x = 0; $x < 255; $x++) {
	console::write(".");
	for ($y = 0; $y < 255; $y++) {
		$c = hsv(0,$x,$y); $p->setPixel(360+$x,$y,$c);
		$c = hsv(120,$x,$y); $p->setPixel(360+$x,256+$y,$c);
		$c = hsv(240,$x,$y); $p->setPixel(360+256+$x,$y,$c);
	}
}
console::writeLn();

$i->drawText($f,rgb(255,255,255),5,5,"L=128");
$i->drawText($f,rgb(255,255,255),5,256+5,"S=128");
$i->drawText($f,rgb(255,255,255),365,5,"H=0");
$i->drawText($f,rgb(255,255,255),365+256,5,"H=120");
$i->drawText($f,rgb(255,255,255),365,256+5,"H=240");

echo "Saving colorscale.png\n";
$i->save('colorscale.png');
