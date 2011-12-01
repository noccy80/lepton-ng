<?php

abstract class NcursesObject { }

abstract class NcursesDrawable {
	abstract public function draw();
}

class NcursesDesktop extends NcursesDrawable {
	public function draw() {
		ncurses_bkgd("X");
		ncurses_erase();
		ncurses_refresh();
	}
}

abstract class NcursesApplication extends ConsoleApplication {

	static $instance = null;
	static $handlers = array();
	public $desktop = null;

	public function __construct() {

		self::$instance = $this;
		ncurses_init();

		// Get access to the mouose events
		$newmask = NCURSES_ALL_MOUSE_EVENTS;
		$oldmask = null;
		$mask = ncurses_mousemask($newmask, &$oldmask);

		// Create a new desktop
		$this->desktop = new NcursesDesktop();

		// Hide cursor
		ncurses_curs_set( 0 ); 
	}

	public function __destruct() {
		ncurses_end();
	}

	static function setHandler(NcursesObject $object=null,$event=null,Callback $callback=null) {
		self::$handlers[] = $callback;
	}

	static function loop() {

		self::$instance->desktop->draw();

		while(true) {
			switch (ncurses_getch()) {
				case NCURSES_KEY_MOUSE:
					if (ncurses_getmouse($mevent)) {
						$mask = $mevent['mmask'];
						// Get click position and do hittesting
						$evtx = $mevent['x'];
						$evty = $mevent['y'];
						// Figure out what event we are dealing with
						if ($mask & NCURSES_BUTTON1_CLICKED) {
							$evtb = 1;
							$evtn = 'onClick';
						} elseif ($mask & NCURSES_BUTTON1_DOUBLE_CLICKED) {
							$evtb = 1;
							$evtn = 'onDoubleClick';
						} elseif ($mask & NCURSES_BUTTON1_PRESSED) {
							$evtb = 1;
							$evtn = 'onOnMouseDown';
						} elseif ($mask & NCURSES_BUTTON1_RELEASED) {
							$evtb = 1;
							$evtn = 'onMouseUp';
						}
						// Resolve modifiers
						$evtm = 0;
						// Call event handler for $evtn with x, y, button and mod
						self::$handlers[0]->call($evtx,$evty,$evtb,$evtm);

					}
					break;
				case " ":
					return;
				default:
					/* .... */
			}
			usleep(10000);
		}

	}




}
