<?php

module("Pagination classes");

/**
 * @class Paginator
 * @brief Utility class to help with paginating of data sets
 *
 * The constructor takes two arguments representing the requested page and the
 * number of items to display per page. The class is then to be passed to a
 * method being in charge of retrieving or arranging data to be returned.
 * 
 * The instance can then be used to determine the number of pages needed to 
 * display the complete resultset, either by calling getNumPages() without a
 * parameter in which case the setNumItems() method must have been called prior
 * with the total number of items, or by calling getNumPages() with a parameter
 * holding the total number of items. The helper functions getCurrentPage(),
 * getNumItems() and getSummary() are also available to provide information on
 * the set.
 *
 * @author Christopher Vagnetoft <noccy.com>
 */
class Paginator {

    private $_gpage = 0;
    private $_gitemsperpage = 0;
    private $_gnumitems = 0;

    /**
     * @brief Constructor, to be called with the requested page and the number of items per page.
     * 
     * @param Integer $page The page to display, starting at 1
     * @param Integer $itemsperpage The number of items to display per page
     */
    function __construct($page, $itemsperpage) {
        if ($itemsperpage == 0) {
            throw new LogicException("Items per page can not be 0");
        }
        $this->_gitemsperpage = $itemsperpage;
        $this->_gpage = ($page < 1) ? 1 : $page;
    }

    /**
     * @brief Set the number of items in the data set.
     *
     * @param Integer $numitems The number of items in the resultset
     */
    function setNumItems($numitems) {
        $this->_gnumitems = intval($numitems);
    }

    /**
     * @brief Return the number of items in the data set.
     * 
     * This value is updated either by a manual call to setNumItems() or from
     * the getPageFromArray() method.
     * 
     * @return Integer The number of items in the set
     */
    function getNumItems() {
        return $this->_gnumitems;
    }

    /**
     * @brief Return the current page number.
     *
     * This number is set from within the constructor and can not be modified.
     * 
     * @return Integer The current page as specified in the constructor
     */
    function getCurrentPage() {
        return $this->_gpage;
    }

    /**
     * @brief Return summary information of the pagination.
     * 
     * This method will return the first and last item referenced by the
     * paginator as well as the current page and the total number of items.
     * It can be used to assemble a summary such as "Showing 1-15 of 250".
     *
     * @return Array Summary, holding page, total plus the first and last item displayed
     */
    function getSummary() {
        $first = ($this->_gitemsperpage * ($this->_gpage - 1)) + 1;
        $last = $first + $this->_gitemsperpage - 1;
        if ($last > $this->getNumItems())
            $last = $this->getNumItems();
        return array(
            'page' => $this->getCurrentPage(),
            'first' => $first,
            'last' => $last,
            'total' => $this->getNumItems()
        );
    }

    /**
     * @brief Return the number of pages in the set.
     *
     * Returns the total page count as determined by the number of items and
     * the number of items per page.
     * 
     * @param Integer $numitems The number of items to use for calculation (Optional)
     * @return Integer The number of pages needed to cover the set.
     */
    function getNumPages($numitems=null) {
        if (!$numitems)
            $numitems = $this->_gnumitems;
        return floor(($numitems - 1) / $this->_gitemsperpage) + 1;
    }

    /**
     * @brief Return a SQL LIMIT statement to use for database filtering.
     *
     * @return String The SQL LIMIT statement for MySQL statements
     */
    function getSqlLimit() {
        return sprintf("LIMIT %d,%d", ($this->_gitemsperpage * ($this->_gpage - 1)), $this->_gitemsperpage);
    }

    /**
     * @brief Return a slice of an array based on the pagination info.
     * 
     * This function will also update the total number of items based on the
     * array provided.
     *
     * @param Array $array The input array
     * @return Array The slice of the array representing the desired page
     */
    function getPageFromArray(Array $array) {
        $this->_gnumitems = count($array);
        return array_slice($array, ($this->_gitemsperpage * ($this->_gpage - 1)), $this->_gitemsperpage);
    }

}

