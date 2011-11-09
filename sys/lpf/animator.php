<?php

interface ILpfAnimator {
    function getValue($frame,$total);
}

abstract class Animator implements ILpfAnimator {


}

/**
 * @class LinearAnimator
 * @brief Animate a property using a basic linear animation between two values
 *
 *
 */
class LinearAnimator extends Animator {

    private $_start = null;
    private $_end = null;
    private $_span = null;

    function __construct($start,$end) {
        console::writeLn("LinearAnimator: Cosntructed [%d-%d]", $start, $end);
        $this->_start = $start;
        $this->_end = $end;
        $this->_span = $end - $start;
    }
    
    function getValue($frame,$total) {
        return ($this->_start + ($this->_span / $total) * $frame);
    }

}
