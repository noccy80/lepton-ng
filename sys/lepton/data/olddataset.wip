<?php __fileinfo("DataSet Classes");

class DataSet {

	private $data;

	public function __construct($data = null) {
		if ($data != null) {
			if (is_array($data)) {
				// Validate each of the rows
				$last = null;
				foreach($data as $k=>$r) {
					if (is_array($r)) {
						throw new DataException("DataSets can not contain arrays.");
					}
					if ($last == null) $last = count($r);
					if (count($r) != $last) {
						throw new DataException("All rows must contain the same amount of columns");
					}
				}
				// All is good
				$this->data = $data;
				return;
			}
		}
		$this->data = null;
	}
	
	public function getValue($row,$column) {
		if (($row < 0) || ($row > count($this->data))) {
			throw new DataException("Invalid row");
		}
		if (($column < 0) || ($column > count($this->data[$row]))) {
			throw new DataException("Invalid column");
		}
	}
	
	public function applyRow($row,$func) {
		foreach($this->data[$row] as $k=>$r) {
			$this->data[$k] = $func($row,$this->data[$row][$k]);
		}
	}

}
