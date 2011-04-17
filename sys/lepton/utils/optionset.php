<?php

/**
 * @class OptionSet
 *
 * Assists in parsing options from an array of values by providing default
 * options and properties.
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @license GPL v3
 */
class OptionSet {

	private $options = array();
	private $defaults = null;

	/**
	 * @brief Constructor
	 *
	 * @param array $options The options
	 * @param array $defaults Default options to apply
	 */
	function __construct(array $options, array $defaults = null) {
		$this->options = $options;
		$this->defaults = $defaults;
	}

	/**
	 * @brief Property getter
	 *
	 * @param string $key The key to access
	 * @return Mixed The proeprty
	 */
	function __get($key) {
		if (isset($this->options[$key])) {
			return $this->options[$key];
		} else {
			if (isset($this->defaults[$key])) {
				return($this->defaults[$key]);
			} else {
				return null;
			}
		}
	}

	/**
	 * @brief Property checker
	 *
	 * @param string $key The key to check
	 * @return bool State of the key
	 */
	function __isset($key) {
		return (isset($this->options[$key]));
	}

	/**
	 * @brief Get method with explicit default
	 *
	 * @param string $key The key to access
	 * @param Mixed $default The default value to use if not set
	 * @return Mixed The proeprty
	 */
	function get($key,$default=null) {
		if (isset($this->options[$key])) {
			return $this->options[$key];
		} else {
			if ($default != null) {
				return $default;
			} else {
				if (isset($this->defaults[$key])) {
					return($this->defaults[$key]);
				} else {
					return null;
				}
			}
		}
	}

	/**
	 * @brief Property checker
	 *
	 * @param string $key The key to check
	 * @return bool State of the key
	 */
	function has($key) {
		return (isset($this->options[$key]));
	}

}
