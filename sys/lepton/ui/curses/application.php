<?php

define('NCC_FRAME', 1);
define('NCC_TEXT', 2);
define('NCC_TITLE', 3);

/**
 * @interface ICursesWidget
 * @brief Interface for CursesWidget components
 */
interface ICursesWidget {
	function draw($workspace);
	function keypress($key);
}

/**
 * @class CursesWidget
 * @brief Abstract baseclass for CurseWidget components
 */
abstract class CursesWidget implements ICursesWidget {
}

/**
 * @class CursesMenu
 * @brief A simple menu window
 *
 */
class CursesMenu extends CursesWidget {
	private $_x, $_y, $_w, $_h;
	private $_title;
	private $_items;
	private $_selected;
	private $_scroll;
	/**
	 * Constructor accepts the placement (x, y, width and height), the title,
	 * the available options, and the currently selected index.
	 *
	 * @param int $x The topmost row
	 * @param int $y The leftmost column
	 * @param int $w The width
	 * @param int $h The height
	 * @param string $title The title
	 * @param array $items The available options
	 * @param int $selected The currently selected index
	 */
	function __construct($x, $y, $w, $h, $title, Array $items, $selected = 0) {
		$this->_x = $x; $this->_y = $y;
		$this->_w = $w; $this->_h = $h;
		$this->_title = $title;
		$this->_items = $items;
		$this->_selected = $selected;
		$this->_scroll = 0; // TODO: Calculate from height and selected
	}

	/**
	 * Draws the menu on the workspace.
	 *
	 * @param int $workspace The workspace window handle for curses
	 */
	function draw($workspace) {
		$wh = ncurses_newwin($this->_h, $this->_w, $this->_y, $this->_x); 
		ncurses_wcolor_set($wh, NCC_FRAME);
		ncurses_wborder($wh,0,0, 0,0, 0,0, 0,0); 
		ncurses_wcolor_set($wh, NCC_TITLE);
		ncurses_wattron($wh,NCURSES_A_BOLD); 
		$left = floor(($this->_w - 2) / 2 - (strlen($this->_title) + 2) / 2);
		ncurses_mvwaddstr($wh, 0, $left, ' '.$this->_title.' ');
		ncurses_wattroff($wh,NCURSES_A_BOLD); 
		ncurses_wcolor_set($wh, NCC_TEXT);
		$i = 0;
		for($n = 0; $n < $this->_h - 2; $n++) {
			if (($n + $i) < count($this->_items)) {
				$str = " ".$this->_items[$n+$i];
			} else {
				$str = "";
			}
			$str.= str_repeat(' ',$this->_w - 2 - strlen($str));
			if ($n+$i == $this->_selected) ncurses_wattron($wh,NCURSES_A_REVERSE); 
			ncurses_mvwaddstr($wh, $n + 1, 1, $str); 
			ncurses_wattroff($wh,NCURSES_A_REVERSE); 
		}
		ncurses_wrefresh($wh); 
		ncurses_wcolor_set($wh,0);
	}

	/**
	 *
	 */
	function keypress($key) {
		if ($key == NCURSES_KEY_UP) {
			if ($this->_selected > 0) {
				$this->_selected--;
			}
		} elseif ($key == NCURSES_KEY_DOWN) {
			if ($this->_selected < count($this->_items) - 1) {
				$this->_selected++;
			}
		}
	}
}

/**
 *
 *
 */
class TestWidget extends CursesWidget {

	private $placement;

	/**
	 *
	 */
	function __construct($x, $y, $h, $w, $text) {
		$this->placement = array(
			'x' => $x, 'y' => $y, 'w' => $w, 'h' => $h
		);
		$this->caption = $text;
	}

	/**
	 *
	 */
	function draw($workspace) {
		ncurses_color_set(1);
		// now lets create a small window 
		$wh = ncurses_newwin(
			$this->placement['h'], 
			$this->placement['w'], 
			$this->placement['y'], 
			$this->placement['x']
		); 
		// border our small window. 
		ncurses_wborder($wh,0,0, 0,0, 0,0, 0,0); 
		// move into the small window and write a string 
		$str = $this->caption;
		ncurses_mvwaddstr($wh, floor($this->placement['h']/2), floor(($this->placement['w']/2)-(strlen($str)/2)),  $str); 
		// show our handiwork and refresh our small window 
		ncurses_wrefresh($wh); 
		ncurses_color_set(0);
	}

	/**
	 *
	 */
	function keypress($key) { }
}

/**
 *
 *
 */
abstract class CursesApplication extends ConsoleApplication {

	protected $workspace;
	protected $children;

	/**
	 *
	 */
	function __construct() {
		ncurses_init();
		if (ncurses_has_colors()) {
			ncurses_start_color();
			ncurses_init_pair(NCC_FRAME, 	NCURSES_COLOR_BLACK, 	NCURSES_COLOR_BLUE);
			ncurses_init_pair(NCC_TEXT, 	NCURSES_COLOR_WHITE, 	NCURSES_COLOR_BLUE);		
			ncurses_init_pair(NCC_TITLE, 	NCURSES_COLOR_YELLOW, 	NCURSES_COLOR_BLUE);		
		}
	}

	/**
	 *
	 */
	function __destruct() {
		ncurses_end();
	}

	/**
	 *
	 */
	function addChild(CursesWidget $widget) {
		$this->children[] = $widget;
	}

	/**
	 *
	 */
	function removeChild(CursesWidget $widget = null) {
		if ($widget == null) {
			if (count($this->children)>0) {
				unset($this->children[count($this->children)-1]);
				ncurses_erase();
				return true;
			}
		}
		foreach($this->children as $index=>$child) {
			if ($child == $widget) {
				unset($this->children[$index]);
				ncurses_erase();
				return true;
			}
		}
		return false;
	}

	/**
	 *
	 */
	function refresh() {
		$this->workspace = ncurses_newwin ( 0, 0, 0, 0);  
		ncurses_refresh();// paint both windows 
		foreach((array)$this->children as $child) {
			$child->draw($this->workspace);
		}
		ncurses_move(-1,1);
		$kp = ncurses_getch();// wait for a user keypress
		if ($kp == NCURSES_KEY_MOUSE) {
			if (!ncurses_getmouse($mevent)){
				if ($mevent["mmask"] & NCURSES_MOUSE_BUTTON1_PRESSED){
					$mouse_x = $mevent["x"]; // Save mouse position
					$mouse_y = $mevent["y"];
				}
			}
		}
		if (count($this->children) > 0) {
			// Pass it on to topmost window
			@$this->children[count($this->children) - 1]->keypress($kp);
		}
		return $kp;
	}

	/**
	 *
	 */
	function moveCursorXY($x,$y) {
		ncurses_move($y,$x);
	}

}

?>
