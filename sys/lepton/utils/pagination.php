<?php __fileinfo("Pagination classes");

/**
 *
 *
 *
 *
 */
class Paginator {

    private $numitems = 0;
	private $itemsperpage = 0;

    /**
     *
     * @param <type> $numitems
     * @param <type> $itemsperpage
     */
	function __construct($numitems,$itemsperpage) {
        if ($itemsperpage == 0) { throw new LogicException("Items per page can not be 0"); }
		$this->itemsperpage = $itemsperpage;
		$this->numitems = $numitems;
	}

    /**
     *
     * @return <type>
     */
	function getNumPages() {
		return ceil(($this->numitems - 1) / $this->itemsperpage) + 1;
	}

    /**
     *
     * @param <type> $page
     * @return <type>
     */
    function getSqlLimit($page) {
        return sprintf("LIMIT %d,%d", ($this->itemsperpage * ($page - 1)), $this->numitems);
    }

    /**
     *
     * @param array $array
     * @param <type> $page
     * @return <type>
     */
    function getPageFromArray(Array $array,$page) {
        return array_slice($array, ($this->itemsperpage * ($page - 1)), $this->numitems);
    }
    
}

