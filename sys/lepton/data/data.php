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
		$this->series[] = array(
			'label' => $label,
			'data' => $s
		);
	}
	
	function getSeries($index) {
		return $this->serieß[$index];
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
				$this->data[] = $arg[0];
				$this->label[] = $arg[1];
			} else {
				$this->data[] = $arg;
				$this->label[] = null;
			}
		}
	}
	
	function getCount() {
		return count($this->data);
	}
	
	function addValue($value,$label=null) {
		$this->data[] = $value;
		$this->label[] = $label;
	}
	
	function getValue($index) {
		return array($this->data[$index], $this->label[$index]);
	}
	
}

/*
$ds = new DataSet('Jan','Feb','Mar','Apr','May','Jun');
$ds->addSeries('Sales', new DataSeries(100, 150, 200, 250, 300, 350));
$ds->addSeries('Services', new DataSeries(50, 55, 60, 65, 70, 75));

$pc = new PieChart('Totals for Q1-Q2');
$pc->setData($ds);
*/
