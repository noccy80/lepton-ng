<?php

ModuleManager::load('lepton.ui.curses.widget');

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
    private $_wh;
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
        $this->_itemsidx = array_values($items);
        $this->_selected = $selected;
        $this->_scroll = 0; // TODO: Calculate from height and selected
        $this->_wh = ncurses_newwin($this->_h, $this->_w, $this->_y, $this->_x); 
        ncurses_werase($this->_wh);
        Console::debug("Created window with handle %xd", $this->_wh);
    }

    function __destruct() {
        Console::debug("Deleting window with handle %xd", $this->_wh);
        ncurses_delwin($this->_wh);
    }

    function getSelection() {
        $keys = array_keys($this->_items);
        return $keys[$this->_selected];
    }

    /**
     * Draws the menu on the workspace.
     *
     * @param int $workspace The workspace window handle for curses
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
        $i = $this->_scroll;
        $sm = (count($this->_items) - ($this->_h - 2));
        if ($sm<0) $sm = 0;
        for($n = 0; $n < $this->_h - 2; $n++) {
            if (($n + $i) < count($this->_items)) {
                $str = " ".$this->_itemsidx[$n+$i];
            } else {
                $str = "";
            }
            $str.= str_repeat(' ',$this->_w - 2 - strlen($str));
            if ($n+$i == $this->_selected) ncurses_wattron($wh,NCURSES_A_REVERSE);
            $str = substr($str, 0, $this->_w - 2);
            ncurses_mvwaddstr($wh, $n + 1, 1, $str);
            ncurses_wattroff($wh,NCURSES_A_REVERSE);
        }
        ncurses_wcolor_set($wh,NCC_MORE);
        if ($i > 0) { ncurses_wmove($wh, 1, $this->_w - 1); ncurses_waddch($wh,NCURSES_ACS_UARROW | NCURSES_A_BOLD); }
        if ($sm-$i > 0) { ncurses_wmove($wh, $this->_h - 2, $this->_w - 1); ncurses_waddch($wh,NCURSES_ACS_DARROW | NCURSES_A_BOLD); }
        ncurses_wrefresh($wh);
        ncurses_wcolor_set($wh,0);
        ncurses_move(-1,1);
        ncurses_refresh();
    }

    /**
     *
     */
    function keypress($key) {
        if ($key == NCURSES_KEY_UP) {
            if ($this->_selected > 0) {
                $this->_selected--;
                if ($this->_selected - $this->_scroll < 0) $this->_scroll--;
            }
        } elseif ($key == NCURSES_KEY_DOWN) {
            if ($this->_selected < count($this->_items) - 1) {
                $this->_selected++;
                if ($this->_selected - $this->_scroll > ($this->_h - 3)) $this->_scroll++;
            }
        }
    }
}

?>
