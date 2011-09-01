<?php

interface ILpfObject {
    function getProperties();
    function render($frame, Array $properties);
}

abstract class LpfObject implements ILpfObject {

	protected $properties = array();

	public function __construct($object) {
		console::writeLn("New object created: %s", typeOf($object));
	}
	
	public function __toString() { return sprintf("Untitled Object"); }

	protected function registerProperty($property, $type, $value = null, $setter=null, $getter=null) {
		$this->properties[$property] = array(
			'type' => $type,
			'propset' => $setter,
			'propget' => $getter,
			'value' => $value
		);
	}
	
	public function __set($property,$value) {
		if (arr::hasKey($this->properties,$property)) {
			$propmeta = $this->properties[$property];
			switch($propmeta['type']) {
				case LpfProperty::PT_INTEGER:
					break;
				case LpfProperty::PT_FLOAT:
					break;
				case LpfProperty::PT_COLOR:
					break;
			}
			if ($propmeta['propset']) {
				$propmeta['propset']($property,$value);
			} else {
				$this->properties['value'] = $value;
			}
		} else {
			logger::warning("Ambient property %s applied to object %s", $property, $this);
		}
	}

	public function __get($property) {
		if (arr::hasKey($this->properties,$property)) {
			$propmeta = $this->properties[$property];
			if ($propmeta['propget']) {
				return $propmeta['propget']($property);
			} else {
				return $this->properties['value'];
			}
		} else {
			logger::warning("Ambient property %s requested object %s", $property, $this);
		}
	}

	/**
	 * @brief Retrieves all the properties supported by this object
	 *
	 * @return Array Properties
	 */
	public function getProperties() {
	    return $this->properties;
	}

	public function render($frame, Array $properties) {

	}

}

abstract class LpfProperty {
	const PT_INTEGER = 'int';
	const PT_FLOAT = 'float';
	const PT_COLOR = 'color';
}
