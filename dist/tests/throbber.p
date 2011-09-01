#!/usr/bin/php
<?php require('lepton/1.0');

/**
 * Conway's Game of Life implemented on top of Lepton Presentation Framework.
 *
 * The rules are simple:
 *  - Any live cell with fewer than two live neighbours dies, as if caused by
 *    under-population.
 *  - Any live cell with two or three live neighbours lives on to the next
 *    generation.
 *  - Any live cell with more than three live neighbours dies, as if by over-
 *    crowding.
 *  - Any dead cell with exactly three live neighbours becomes a live cell, as
 *    if by reproduction.
 *
 * @author Christopher Vagnetoft <noccy.com>
 * @license GNU General Public License (GPL) Version 3
 */

using('lpf.*');
using('lpf.objects.*');
using('lepton.graphics.*');
using('lepton.graphics.colorspaces.*');

// Create a new scene with transparent background
$sc = new Scene(32,32,null);
$sc->transparent = true;

// Grab the scene graph and the root nodes
$sg = $sc->getSceneGraph();
$root = $sg->getRootNode();

class RadialThrobberObject extends LpfObject {
	public function __construct($color1=null, $color2=null) {
		$this->registerProperty('degrees', 
			LpfProperty::PT_FLOAT, 
			0, 
			callback($this,'getdegrees'), callback($this,'setdegrees'));
		$this->registerProperty('radius', 
			LpfProperty::PT_FLOAT, 10);
		$this->registerProperty('trail', 
			LpfProperty::PT_FLOAT, 50);
		$this->registerProperty('width', 
			LpfProperty::PT_FLOAT, 5);
		$this->registerProperty('colorfrom', 
			LpfProperty::PT_COLOR, 
			$color1, 
			callback($this,'getcolorfrom'), callback($this,'setcolorfrom'));
		$this->registerProperty('colorto', 
			LpfProperty::PT_COLOR, 
			$color2, 
			callback($this,'getcolorto'), callback($this,'setcolorto'));
	}
	public function render($frame, Array $properties) {
		$width = $frame['width'];
		$height = $frame['height'];
		$c = new Canvas($width,$height);
		$cx = $width / 2;
		$cy = $height / 2;
		// Do the match and draw the lines
		$deg = $properties['degrees'];
		$cof = $properties['colorfrom'];
		$cot = $properties['colorto'];
		$tra = $properties['trail'];
		$rad = $properties['radius'];
		$wid = $properties['width'];
		$ful = $rad + $wid;
		$pt = $c->getPainter();
		for ($n = 0; $n < $tra; $n+=.5) {
			$d = ($deg + $n) % 360;
			$dd = ($d / 2) * PI;
			$cos = cos($dd);
			$sin = sin($dd);
			$lx1 = $cx + ($cos * $rad);
			$ly1 = $cy + ($sin * $rad);
			$lx2 = $cx + ($cos * $ful);
			$ly2 = $cy + ($sin * $ful);
			$pt->drawLine($lx1,$ly1,$lx2,$ly2,$cof);
		}
		return $c;
	}
}

// Create a new radial throbber
$thr = new RadialThrobberObject(rgb('#404040ff'), rgb('#40404000'));
$thr->radius = 15;
$thr->width = 15;
$thr->trail = 50;
$blob = $root->addActor('mysprite', $thr);
$blob->addAnimator('degrees', new LinearAnimator(0,359), 0, 1000);

// Create a timeline of 1000 frames and add our scene to span the entire length
// of the timeline.
$tl = new Timeline(1000);
$tl->addScene($sc,0,1000);

// Render the timeline
$tl->renderTimeline();
