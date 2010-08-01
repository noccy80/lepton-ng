<?php

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

?>
