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
		using('lepton.graphics.filters.*');
	}

	/**
	 * @description Creating canvas
	 */
	function canvascreate() {
		$this->canvas = new Canvas(640,480);
		$this->assertNotNull($this->canvas);
	}

	/**
	 * @description Creating canvas with background color
	 */
	function canvascreatecolor() {
		$c = new Canvas(640,480,rgb(0,0,0));
		$this->assertEquals($c->getColorAt(0,0),rgb(0,0,0));
		$c = new Canvas(640,480,rgb(255,255,255));
		$this->assertEquals($c->getColorAt(0,0),rgb(255,255,255,255));
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
	 * @description Rotating canvas
	 */
	function canvasrotate() {
		$this->canvas->rotate(90);
		$this->assertEquals($this->canvas->width,480);
		$this->assertEquals($this->canvas->height,640);
		$this->canvas->rotate(90);
		$this->assertEquals($this->canvas->width,640);
		$this->assertEquals($this->canvas->height,480);
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
		$this->canvas->drawText($this->font, rgb(255,0,0), 0, 0, 'Hello World!');
	}

	/**
	 * @description Drawing with bitmap fonts and effects
	 */
	function canvasbitmapfonts() {
		$this->font = new BitmapFont(3);
		$this->font->setTextEffect(BitmapFont::EFFECT_OUTLINE,new RgbColor(0,0,200));
		$this->canvas->drawText($this->font, new RgbColor(0,255,0), 50, 50, 'Hello Bitmapworld!');
		$this->font->setTextEffect(BitmapFont::EFFECT_SHADOW,new RgbColor(0,0,200));
		$this->canvas->drawText($this->font, new RgbColor(0,255,0), 50, 90, 'Hello Bitmapworld!');
		$this->assertNotNull($this->font);
	}

	/**
	 * @description Saving as common formats
	 */
	function canvassave() {
		$this->canvas->save($this->getTempFile('png'));
		$this->canvas->save($this->getTempFile('gif'));
		$this->canvas->save($this->getTempFile('jpg'));

	/**
	 * @description Load canvas from file
	 */
	function canvasload() {
		$tf = $this->getTempFile('png');
		$this->canvas->save($tf);
		$c = new Image($tf);
		$this->assertEquals($this->canvas->width, $c->width);
		$this->assertEquals($this->canvas->height, $c->height);
	}

	/**
	 * @description Canvas filtering
	 */
	function canvasfilter() {
		$f = new Canvas(100,100,rgb(0,255,0));
		$f->apply(new HueImageFilter(rgb(0,0,255)));
		$this->assertEquals($f->getColorAt(0,0),rgb(0,0,255));
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
