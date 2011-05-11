<?php

/**
 * @class DataSet
 * @brief Create a dataset with labels, holding the series to render.
 *
 * @author Christopher Vagnetoft
 */
class DataSet {

	private $labels = array();
	private $series = array();

	/**
	 * @brief Constructor.
	 *
	 * Call on the constructor with the labels to use as arguments.
	 *
	 * @param String .. Label
	 */
	function __construct() {
		$args = func_get_args();
		// This is labels
		$this->labels = $args;
	}

	/**
	 * @brief Add a series to the set
	 *
	 * @param String $label The name of the series to add
	 * @param DataSeries $s The series
	 */
	function addSeries($label, DataSeries $s) {
		$this->series[] = array($label, $s);
	}

	/**
	 * @brief Get a specific series from the set
	 *
	 * @return DataSeries The series
	 */
	function getSeries($index) {
		return $this->series[$index];
	}

	/**
	 * @brief Get the labels of the series
	 *
	 * @return Array The labels
	 */
	function getLabels() {
		return $this->labels;
	}

	/**
	 * @brief Return the number of series
	 *
	 * @return Int The number of series
	 */
	function getCount() {
		return count($this->series);
	}

}

/**
 * @class DataSeries
 * @brief Holds a data series for a set
 */
class DataSeries {

	private $data = array();

	/**
	 * @brief Constructor
	 *
	 * Call with the values to create the set from.
	 *
	 * @param Mixed .. The values
	 */
	function __construct() {
		$args = func_get_args();
		// This is values
		foreach($args as $arg) {
			if (is_array($arg)) {
				$value = $arg[0];
				$label = $arg[1];
			} else {
				$value = $arg;
				$label = null;
			}
			$this->data[] = array($value, $label);
		}
	}

	/**
	 * @brief Return the number of values in the series
	 *
	 * @reeturn Int The number of values
	 */
	function getCount() {
		return count($this->data);
	}

	/**
	 * @brief Return the sum of all the values
	 *
	 * @return Mixed The sum
	 */
	function getSum() {
		$sum = 0;
		for($n = 0; $n < count($this->data); $n++) {
			$sum += $this->data[$n][0];
		}
		return $sum;
	}

	/**
	 * @brief Add a value with an optional label
	 *
	 * @param Mixed $value The value
	 * @param String $label The label
	 */
	function addValue($value,$label=null) {
		$this->data[] = array($value, $label);
	}

	/**
	 * @brief Get a specific value
	 *
	 * @param Integer $index The index of the value to get
	 * @return Array The value and label (can be null)
	 */
	function getValue($index) {
		return $this->data[$index];
	}

}

class FormulaSeries extends DataSeries {

	private $numValues = null;
	private $values = null;
	private $callback = null;
	private $min = -1;
	private $max = 1;
	private $step = 0.1;

	function __construct($callback) {
		$this->callback = $callback;
	}

	function setBoundaries($min,$max,$step) {
		// Calculate how many values we will store
		$this->numValues = floor(($max - $min) / $step);
		$this->values = null;
		$this->min = $min;
		$this->max = $max;
		$this->step = $step;
	}

	function getValue($index) {
		if (!$this->values) {
			for($n = 0; $n < $this->values; $n++) {
				$val = $this->min + ($n * $this->step);
				$this->values[$n] = array($this->callback($val), $val);
			}
		}
		return $this->values[$n];
	}

	function getCount() {
		return $this->numValues;
	}

}
