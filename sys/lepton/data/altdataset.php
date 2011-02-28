<?php __fileinfo("Dataset");

interface IDataProvider {
	function getData();
}
abstract class DataProvider implements IDataProvider {
}

class DataSet implements IteratorAggregate {
	private $data;
	function count() {
		return count($this->data);
	}
	function item($index) {
		return $this->data[$index];
	}
	function __construct($init) {
		$this->data = $init;
	}
	function getIterator() {
		return new ArrayIterator($this->data);	
	}
	function addSeries() {
		$vals = func_get_args();
		$data[] = $vals;
	}
}

class ArrayDataProvider extends DataProvider {
	private $data;
	function __construct($data) {
		$this->data = $data;
	}
	function getData() {
		$ds = new DataSet($this->data);
		return $ds;
	}
}
