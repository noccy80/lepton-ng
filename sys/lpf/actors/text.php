<?php

using('lpf.actor');

class Text_Actor extends LpfActor {

	function create() {
		$this->addProperty('font', null, lpf::string());
		$this->addProperty('size', 1, lpf::int(1,200));
		$this->addProperty('color', '#FFFFFF', lpf::string());
		$this->addProperty('background','#000000', lpf::color());
		$this->addProperty('text', '', lpf::string());
	}

	function render(SceneState $ss, ActorState $as, Canvas $c) {
		$font = new TrueTypeFont($this->font, $this->size);
		$p = $c->getPainter();
		$p->drawFilledRect(0,0,$c->width,$c->height,rgb($this->background),rgb($this->background));
		$c->drawText($font,rgb($this->color),0,0,$this->text);
	}

}

