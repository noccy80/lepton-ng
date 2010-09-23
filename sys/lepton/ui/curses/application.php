<?php

define('NCC_FRAME', 1);
define('NCC_TEXT', 2);
define('NCC_TITLE', 3);
define('NCC_MORE', 4);

ModuleManager::load('lepton.ui.curses.*');

/**
 *
 *
 */
abstract class CursesApplication extends ConsoleApplication {

    protected $workspace;
    protected $children;
    protected $topmost;

    /**
     *
     */
    function __construct() {
        ncurses_init();
        if (ncurses_has_colors()) {
            ncurses_start_color();
            ncurses_init_pair(NCC_FRAME,    NCURSES_COLOR_BLACK,     NCURSES_COLOR_BLUE);
            ncurses_init_pair(NCC_TEXT,    NCURSES_COLOR_WHITE,     NCURSES_COLOR_BLUE);
            ncurses_init_pair(NCC_TITLE,    NCURSES_COLOR_YELLOW,     NCURSES_COLOR_BLUE);
            ncurses_init_pair(NCC_MORE,     NCURSES_COLOR_WHITE,     NCURSES_COLOR_BLUE);
            ncurses_curs_set(0);
        }
        $this->workspace = ncurses_newwin ( 0, 0, 0, 0);  
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
        $this->topmost = $this->children[count($this->children)-1];
    }

    /**
     *
     */
    function childCount() {
        return count($this->children);
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

    /**
     *
     */
    function refresh() {
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
