<?php

ModuleManager::checkExtension('ncurses', true);
ModuleManager::load('lepton.ui.curses.application');

class CursesUi {
	private $height;
	private $width;
	public function __construct() {
		ncurses_init();
		ncurses_erase();
	}

	public function __destruct() {
		ncurses_end();
	}


}

?>
