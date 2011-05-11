<?php

class DataSet {

	private $labels = array();
	private $series = array();

	function __construct() {
		$args = func_get_args();
		// This is labels
		$this->labels = $args;
	}

	function addSeries($label, DataSeries $s) {
		$this->series[] = array($label, $s);
	}

	function getSeries($index) {
		return $this->series[$index];
	}

	function getLabels() {
		return $this->labels;
	}

	function getCount() {
		return count($this->series);
	}

}

class DataSeries {

	private $data = array();

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

	function getCount() {
		return count($this->data);
	}

	function getSum() {
		$sum = 0;
		for($n = 0; $n < count($this->data); $n++) {
			$sum += $this->data[$n][0];
		}
		return $sum;
	}

	function addValue($value,$label=null) {
		$this->data[] = array($value, $label);
	}

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
