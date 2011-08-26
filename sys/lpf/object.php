<?php

interface ILpfObject {
    function getProperties();
    function render($frame, Array $properties);
}

abstract class LpfObject implements ILpfObject {
	
	protected $properties = array(
	    'x' => 0,
	    'y' => 0,
	    'width' => 0,
	    
	);

	public function __construct($object) {
	    console::writeLn("New object created: %s", typeOf($object));
	}

    /**
     * @brief Retrieves all the properties supported by this object
     *
     * @return Array Properties
     */
	public function getProperties() { 
	    return clone($this->properties);
	}
	
    public function render($frame, Array $properties) {
    
    }

}

