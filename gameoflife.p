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

// Create a new scene with black background
$sc = new Scene(800,600,rgb(0,0,0));

// Grab the scene graph and the root nodes
$sg = $sc->getSceneGraph();
$root = $sg->getRootNode();

// Create a new sprite object
$blob = $root->addActor('mysprite', new SpriteObject('blob.png'));
$blob->addAnimator('x', new LinearAnimator(0,800), 0, 1000);
$blob->addAnimator('y', new LinearAnimator(0,600), 0, 1000);

// Create a timeline of 1000 frames and add our scene to span the entire length
// of the timeline.
$tl = new Timeline(1000);
$tl->addScene($sc,0,1000);
$tl->renderTimeline();
