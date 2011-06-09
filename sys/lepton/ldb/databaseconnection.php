<?php

using('lepton.ldb.databaseconnectionpool');
using('lepton.ldb.drivers.*');

class DatabaseConnection {

	private $conn = null;
	private $driver = null;

    /**
     *
     * @param string $group The database group to connect to or the config array
     */
	public function __construct($connectionstring=null) {

		if ($connectionstring == null) {
			$connectionstring = 'default';
		}
		
		if (is_array($connectionstring)) {
			$config = $connectionstring;
	        Console::debugEx(LOG_DEBUG1,__CLASS__,"Initializing connection with %s.", $connectionstring['driver']);
		} else {
			$config = config::get('lepton.db.'.$connectionstring);
	        Console::debugEx(LOG_DEBUG1,__CLASS__,"Initializing connection for %s.", $connectionstring);
		}
	
		$this->conn = DatabaseConnectionPool::getPooledConnection($config);
		
	}

    /**
     *
     * @param string $pattern Sprintf-style pattern to query
     * @param string $vars Variables to assign to pattern
     * @return array The resulting records
     */
    function getRows($pattern,$vars=null) {

        $args = func_get_args();
        $sql = $this->conn->quote($args);
        $this->debug[] = $sql;
        Console::debugEx(LOG_DEBUG1,__CLASS__,"GetRows: %s", $sql);
        Database::$counter++;
        Database::$queries['QUERYING']++;
        $queryresult = $this->conn->query($sql);

        return (array)$queryresult['data'];

    }

    /**
     *
     * @param string $pattern Sprintf-style pattern to query
     * @param string $vars Variables to assign to pattern
     * @return array The fields of a single record
     */
    function getSingleRow($pattern,$vars=null) {

        $args = func_get_args();
        $sql = $this->conn->quote($args);
        $this->debug[] = $sql;
        Console::debugEx(LOG_DEBUG1,__CLASS__,"GetSingleRow: %s", $sql);
        Database::$counter++;
        Database::$queries['QUERYING']++;
        $queryresult = $this->conn->query($sql);

        // Only return first item
        if ($queryresult != null) {
            if ($queryresult['count'] > 0) {
                return $queryresult['data'][0];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     *
     * @param string $pattern Sprintf-style pattern to query
     * @param string $vars Variables to assign to pattern
     * @return mixed The first field of the first result row.
     */
    function getSingleValue($pattern,$vars=null) {

        $args = func_get_args();
        $sql = $this->conn->quote($args);
        $this->debug[] = $sql;
        Console::debugEx(LOG_DEBUG1,__CLASS__,"GetSingleValue: %s", $sql);
        Database::$counter++;
        Database::$queries['QUERYING']++;
        $queryresult = $this->conn->query($sql);

        // Only return first column of first item
        if ($queryresult->count > 0) {
            return $queryresult['data'][0][0];
        } else {
            return null;
        }

    }

    /**
     *
     * @param string $pattern Sprintf-style pattern to query
     * @param string $vars Variables to assign to pattern
     * @return int The first autonumber of the insert statement
     */
    function insertRow($pattern,$vars=null) {

        $args = func_get_args();
        $sql = $this->conn->quote($args);
        $this->debug[] = $sql;
        Console::debugEx(LOG_DEBUG1,__CLASS__,"InsertRow: %s", $sql);
        Database::$counter++;
        Database::$queries['UPDATING']++;
        $queryresult = $this->conn->query($sql);

        return $this->conn->autonumber;

    }

    /**
     *
     * @param string $pattern Sprintf-style pattern to query
     * @param string $vars Variables to assign to pattern
     * @return int The number of affected rows
     */
    function updateRow($pattern,$vars=null) {

        $args = func_get_args();
        $sql = $this->conn->quote($args);
        $this->debug[] = $sql;
        Console::debugEx(LOG_DEBUG1,__CLASS__,"UpdateRow: %s", $sql);
        Database::$counter++;
        Database::$queries['UPDATING']++;
        $queryresult = $this->conn->query($sql);
        $affected = $queryresult['count'];

        return $affected;

    }

    /**
     *
     * @param string $pattern Sprintf-style pattern to query
     * @param string $vars Variables to assign to pattern
     * @return null Nothing
     */
    function exec($pattern,$vars=null) {

        $args = func_get_args();
        $sql = $this->conn->quote($args);
        $this->debug[] = $sql;
        Console::debugEx(LOG_DEBUG1,__CLASS__,"Execute: %s", $sql);
        Database::$counter++;
        Database::$queries['EXECUTING']++;
        $queryresult = $this->conn->execute($sql);

        return null;

    }

    /**
     * Escapes a string with arguments without performing a query.
     *
     * @param string $pattern Sprintf-style pattern to query
     * @param string $vars Variables to assign to pattern
     * @return string The quoted and escaped string
     */
    function escape($pattern,$vars=null) {

        $args = func_get_args();
        $sql = $this->conn->quote($args);
        return $sql;
    }

    /**
     * Escapes a string.
     *
     * @param string $quote String to quote
     * @return string The quoted string
     */
    function quote($string) {
        return $this->conn->quote($string);
    }

    /**
     *
     * @return null Not implemented
     */
    function transactionBegin() {
        return false;
    }

    /**
     *
     * @return null Not implemented
     */
    function transactionEnd() {
        return false;
    }

    function getDebug() {
        $ret = $this->debug;
        $this->debug = array();
        return $ret;
    }
    
    /**
     * @brief Return query statistics as an associative array.
     *
     * @return Array Statistics for QUERYING, UPDATING and EXECUTING queries.
     */
    function getStatistics() {
    	return Database::$queries;
    }

}

class Database {
    static $counter = 0;
    static $queries = array(
        'QUERYING' => 0,
        'UPDATING' => 0,
        'EXECUTING' => 0
    );
}

