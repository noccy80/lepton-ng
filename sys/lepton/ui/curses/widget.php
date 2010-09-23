<?php

/**
 * @interface ICursesWidget
 * @brief Interface for CursesWidget components
 */
interface ICursesWidget {
    function draw($workspace);
    function keypress($key);
}

interface ICursesContainer {
    function addChild(CursesWidget $widget);
    function removeChild(CursesWidget $widget = null);
}

/**
 * @class CursesWidget
 * @brief Abstract baseclass for CurseWidget components
 */
abstract class CursesWidget implements ICursesWidget {

}

abstract class CursesContainer extends CursesWidget implements ICursesContainer {
    protected $children;
    function childCount() {
        return count($this->children);
    }
}

?>
