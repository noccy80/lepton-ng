<?php module("Pagination classes");

/**
 *
 *
 *
 *
 */
class Paginator {

    private $page = 0;
	private $itemsperpage = 0;

    /**
     *
     * @param <type> $numitems
     * @param <type> $itemsperpage
     */
	function __construct($page,$itemsperpage) {
        if ($itemsperpage == 0) { throw new LogicException("Items per page can not be 0"); }
		$this->itemsperpage = $itemsperpage;
		$this->page = $page;
	}

    /**
     *
     * @return <type>
     */
	function getNumPages($numitems) {
		return floor(($numitems - 1) / $this->itemsperpage) + 1;
	}

    /**
     *
     * @param <type> $page
     * @return <type>
     */
    function getSqlLimit() {
        return sprintf("LIMIT %d,%d", ($this->itemsperpage * ($this->page - 1)), $this->itemsperpage);
    }

    /**
     *
     * @param array $array
     * @param <type> $page
     * @return <type>
     */
    function getPageFromArray(Array $array) {
        return array_slice($array, ($this->itemsperpage * ($this->page - 1)), $this->itemsperpage);
    }
    
}

