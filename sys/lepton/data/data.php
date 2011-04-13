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

/*
$ds = new DataSet('Jan','Feb','Mar','Apr','May','Jun');
$ds->addSeries('Sales', new DataSeries(100, 150, 200, 250, 300, 350));
$ds->addSeries('Services', new DataSeries(50, 55, 60, 65, 70, 75));

$pc = new PieChart('Totals for Q1-Q2');
$pc->setData($ds);
*/
