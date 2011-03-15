<?php

using('lunit.*');

/**
 * @description Canvas/Graphics tests
 */
class LeptonCanvasTests extends LunitCase {

	private $canvas;
	private $clone;
	private $font;

	function __construct() {
		using('lepton.graphics.canvas');
	}
	
	/**
	 * @description Creating canvas
	 */
	function canvascreate() {
		$this->canvas = new Canvas(640,480);
		$this->assertNotNull($this->canvas);
	}
	
	/**
	 * @description Testing canvas properties
	 */
	function canvasprops() {
		$this->assertEquals($this->canvas->width,640);
		$this->assertEquals($this->canvas->height,480);
	}
	
	/**
	 * @description Painting on the canvas
	 */
	function canvaspainter() {
		$p = $this->canvas->getPainter();
		$p->drawrect(0,0,639,479,new RgbColor(255,0,0));
		$p->drawline(0,0,639,479,new RgbColor(0,255,0));
	}

	/**
	 * @description Duplicating a canvas
	 */
	function canvasdupe() {
		$this->clone = $this->canvas->duplicate();
		$this->assertNotNull($this->clone);
	}
	
	/**
	 * @description Resizing the canvas
	 */
	function canvasresize() {
		$this->clone->resize(400,300);	
	}
	
	/**
	 * @description Testing of IDrawable and ICanvas
	 */
	function canvasdrawable() {
		$this->clone->draw($this->canvas,100,100);
	}
	
	/**
	 * @description Loading fonts
	 */
	function canvasfonts() {
		$this->font = new TruetypeFont('arial.ttf',24);
		$this->assertNotNull($this->font);
	}
	
	function canvastext() {
		$this->canvas->drawText($this->font, new RgbColor(0,0,255), 10, 30, 'Hello World');
	}
	
	/**
	 * @description Saving as common formats
	 */
	function canvassave() {
		$this->canvas->saveImage('/tmp/canvas.png');
		$this->canvas->saveImage('/tmp/canvas.gif');
		$this->canvas->saveImage('/tmp/canvas.jpg');
	}
	
	function __destruct() {
	/*
		unlink('/tmp/canvas.png');
		unlink('/tmp/canvas.gif');
		unlink('/tmp/canvas.jpg');
	*/
	}

}

Lunit::register('LeptonCanvasTests');
