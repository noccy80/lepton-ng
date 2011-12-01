#!/usr/bin/php
<?php
require('lepton-ng');
using('lepton.ui.ncurses.*');

class StringEditor extends NcursesApplication {

	function onClick($x,$y,$button,$mod) {
		ncurses_mvaddstr($y,$x,"*"); // sprintf("Event at %d:%d (%d) %d\n", $x, $y, $button,$mod));
	}

	function main($argv,$argc) {
		$this->setHandler(null,'onClick',new Callback($this,'onClick'));
		self::loop();
	}

}

lepton::run('StringEditor');
