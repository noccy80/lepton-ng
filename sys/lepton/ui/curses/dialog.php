<?php

ModuleManager::load('lepton.ui.curses.widget');

/**
 *
 *
 */
class CursesDialog extends CursesContainer {

	private $_x, $_y, $_w, $_h;
	private $_title;
	private $_text;
	private $_wh;

	/**
	 *
	 */
	function __construct($x, $y, $w, $h, $title, $text) {
		$this->_x = $x; $this->_y = $y;
		$this->_w = $w; $this->_h = $h;
		$this->_title = $title;
		$this->_text = $text;
		$this->_wh = ncurses_newwin($this->_h, $this->_w, $this->_y, $this->_x); 
		Console::debug("Created window with handle %xd", $this->_wh);
	}

	function __destruct() {
		Console::debug("Deleting window with handle %xd", $this->_wh);
		ncurses_delwin($this->_wh);
	}

	/**
	 *
	 */
	function draw($workspace) {
		$wh = $this->_wh;
		ncurses_wcolor_set($wh, NCC_FRAME);
		ncurses_wborder($wh,0,0, 0,0, 0,0, 0,0); 
		ncurses_wcolor_set($wh, NCC_TITLE);
		ncurses_wattron($wh,NCURSES_A_BOLD); 
		$left = floor(($this->_w - 2) / 2 - (strlen($this->_title) + 2) / 2);
		ncurses_mvwaddstr($wh, 0, $left, ' '.$this->_title.' ');
		ncurses_wattroff($wh,NCURSES_A_BOLD); 
		ncurses_wcolor_set($wh, NCC_TEXT);

		ncurses_wrefresh($wh);
		ncurses_wcolor_set($wh,0);
		ncurses_move(-1,1);
		ncurses_refresh();
	}

	/**
	 *
	 */
	function keypress($key) { }

	function addChild(CursesWidget $widget) {
		$this->children[] = $widget;
		$this->topmost = $this->children[count($this->children)-1];
	}

	/**
	 *
	 */
	function removeChild(CursesWidget $widget = null) {
		if ($widget == null) {
			if (count($this->children)>0) {
				unset($this->children[count($this->children)-1]);
				$this->children = array_values($this->children);
				$this->topmost = $this->children[count($this->children)-1];
				ncurses_erase();
				return true;
			}
		}
		foreach($this->children as $index=>$child) {
			if ($child == $widget) {
				unset($this->children[$index]);
				$this->children = array_values($this->children);
				$this->topmost = $this->children[count($this->children)-1];
				ncurses_erase();
				return true;
			}
		}
		return false;
	}

}
?>
