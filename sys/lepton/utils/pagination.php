<?php __fileinfo("Pagination classes");

class Paginator {
	private $itemsperpage;
	function __construct($itemsperpage) {
		$this->itemsperpage = $itemsperpage;
		$this->numitems = $numitems;
	}
	function getNumPages($numitems) {
		return ceil(($numitems - 1) / $this->itemsperpage) + 1;
	}
}

