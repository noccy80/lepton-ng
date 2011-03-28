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
		using('lepton.graphics.capture');
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
		$this->assertEquals($this->clone->width,400);
		$this->assertEquals($this->clone->height,300);
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
	
	/**
	 * @description Drawing text on the canvas
	 */
	function canvastext() {
		$this->assertNotNull($this->font);
		$this->font->drawText($this->canvas, 0, 0, new RgbColor(255,0,0), 'Hello World!');
	}
	
	/**
	 * @description Saving as common formats
	 */
	function canvassave() {
		$this->canvas->saveImage($this->getTempFile('png'));
		$this->canvas->saveImage($this->getTempFile('gif'));
		$this->canvas->saveImage($this->getTempFile('jpg'));
		$this->canvas->saveImage('test.png');
	}
	
	/**
	 * @description Load canvas from file
	 */
	function canvasload() {
		$tf = $this->getTempFile('png');
		$this->canvas->saveImage($tf);
		$c = new Image($tf);
		$this->assertEquals($this->canvas->width, $c->width);
		$this->assertEquals($this->canvas->height, $c->height);
	}

	/**
	 * @description Screen capture with Screenshot() class.
	 */
	function screenshot() {
		unset($this->canvas);
		$this->canvas = new Screenshot();
		$this->assertNotNull($this->canvas);
	}

	/**
	 * @description Verifying properties of canvas
	 */
	function screenshotprops() {
		$this->assertTrue($this->canvas->width>0);
		$this->assertTrue($this->canvas->height>0);
	}

}

Lunit::register('LeptonCanvasTests');
