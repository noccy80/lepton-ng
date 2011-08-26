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

	function __construct($start,$end) {
	    console::writeLn("LinearAnimator: Cosntructed [%d-%d]", $start, $end);
	}
	
	function getValue($frame,$total) {
	
	}

}
