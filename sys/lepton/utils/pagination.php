<?php module("Pagination classes");

/**
 *
 *
 *
 *
 */
class Paginator {

    private $_gpage = 0;
	private $_gitemsperpage = 0;
    private $_gnumitems = 0;

    /**
     *
     * @param <type> $numitems
     * @param <type> $itemsperpage
     */
	function __construct($page,$itemsperpage) {
        if ($itemsperpage == 0) { throw new LogicException("Items per page can not be 0"); }
		$this->_gitemsperpage = $itemsperpage;
		$this->_gpage = $page;
	}

    function setNumItems($numitems) {
        $this->_gnumitems = intval($numitems);
    }
    
    /**
     *
     * @return <type>
     */
	function getNumPages($numitems=null) {
        if (!$numitems) $numitems = $this->_gnumitems;
		return floor(($numitems - 1) / $this->_gitemsperpage) + 1;
	}

    /**
     *
     * @param <type> $page
     * @return <type>
     */
    function getSqlLimit() {
        return sprintf("LIMIT %d,%d", ($this->_gitemsperpage * ($this->_gpage - 1)), $this->_gitemsperpage);
    }

    /**
     *
     * @param array $array
     * @param <type> $page
     * @return <type>
     */
    function getPageFromArray(Array $array) {
        return array_slice($array, ($this->_gitemsperpage * ($this->_gpage - 1)), $this->_gitemsperpage);
    }
    
}

