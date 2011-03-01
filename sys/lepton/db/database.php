<?php __fileinfo("Database Connection Manager");

ModuleManager::load('lepton.db.driver');
ModuleManager::load('lepton.db.drivers.*');

class DatabaseException extends BaseException { }

/**
 * @brief Database manager, takes care of pooling connections.
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 */
abstract class DatabaseManager {

    static $pool = array();

    /**
     *
     * @param string $group The group to access
     * @return DatabaseConnection The connection
     */
    static function getConnection($group='default') {

        if (!isset(self::$pool[$group])) {
            Console::debugEx(LOG_DEBUG1,__CLASS__,"PoolConnectGroup(%s)", $group);
            self::$pool[$group] = self::poolConnectGroup($group);
        }
        return self::$pool[$group];

    }

    /**
     *
     * @param string $group The group to access
     * @return DatabaseConnection The connection
     */
    static function poolConnectGroup($group) {
        $cfg = config::get('lepton.db.'.$group);
        $driver = explode('/',$cfg['driver']);
        if ($driver[0] != '') {
            $class = $driver[0].'DatabaseDriver';
            Console::debugEx(LOG_DEBUG1,__CLASS__,"Base DBM driver: %s (%s) - %s", $class, $driver[0], $cfg['driver']);
            return new $class($cfg);
        } else {
            throw new DatabaseException("No database driver configured");
        }
    }

    /**
     *
     */
    static function getPooledConnection() {

        // Mangle pool and return a connection

    }

}




/**
 * @brief Query a database using SQL statements
 *
 * @example database.php
 */
final class DatabaseConnection {

    private $db_conn;
    private $debug = array();

    /**
     *
     * @param string $group The database group to connect to
     */
    function __construct($group='default') {

        Console::debugEx(LOG_DEBUG1,__CLASS__,"Initializing connection for %s.", $group);
        $this->db_conn = DatabaseManager::getConnection($group);

    }

    /**
     *
     * @param string $pattern Sprintf-style pattern to query
     * @param string $vars Variables to assign to pattern
     * @return array The resulting records
     */
    function getRows($pattern,$vars=null) {

        $args = func_get_args();
        $sql = $this->db_conn->escapeString($args);
        $this->debug[] = $sql;
        Console::debugEx(LOG_DEBUG1,__CLASS__,"GetRows: %s", $sql);
        Database::$counter++;
        Database::$queries['QUERYING']++;
        $queryresult = $this->db_conn->query($sql);

        return $queryresult['data'];

    }

    /**
     *
     * @param string $pattern Sprintf-style pattern to query
     * @param string $vars Variables to assign to pattern
     * @return array The fields of a single record
     */
    function getSingleRow($pattern,$vars=null) {

        $args = func_get_args();
        $sql = $this->db_conn->escapeString($args);
        $this->debug[] = $sql;
        Console::debugEx(LOG_DEBUG1,__CLASS__,"GetSingleRow: %s", $sql);
        Database::$counter++;
        Database::$queries['QUERYING']++;
        $queryresult = $this->db_conn->query($sql);

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
        $sql = $this->db_conn->escapeString($args);
        $this->debug[] = $sql;
        Console::debugEx(LOG_DEBUG1,__CLASS__,"GetSingleValue: %s", $sql);
        Database::$counter++;
        Database::$queries['QUERYING']++;
        $queryresult = $this->db_conn->query($sql);

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
        $sql = $this->db_conn->escapeString($args);
        $this->debug[] = $sql;
        Console::debugEx(LOG_DEBUG1,__CLASS__,"InsertRow: %s", $sql);
        Database::$counter++;
        Database::$queries['UPDATING']++;
        $queryresult = $this->db_conn->query($sql);

        return $this->db_conn->autonumber;

    }

    /**
     *
     * @param string $pattern Sprintf-style pattern to query
     * @param string $vars Variables to assign to pattern
     * @return int The number of affected rows
     */
    function updateRow($pattern,$vars=null) {

        $args = func_get_args();
        $sql = $this->db_conn->escapeString($args);
        $this->debug[] = $sql;
        Console::debugEx(LOG_DEBUG1,__CLASS__,"UpdateRow: %s", $sql);
        Database::$counter++;
        Database::$queries['UPDATING']++;
        $queryresult = $this->db_conn->query($sql);
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
        $sql = $this->db_conn->escapeString($args);
        $this->debug[] = $sql;
        Database::$counter++;
        Database::$queries['EXECUTING']++;
        $queryresult = $this->db_conn->exec($sql);

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
        $sql = $this->db_conn->escapeString($args);
        return $sql;
    }

    /**
     * Escapes a string.
     *
     * @param string $quote String to quote
     * @return string The quoted string
     */
    function quote($string) {
        return $this->db_conn->escapeString($string);
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

class sqlstr {
    private $str;
    function __construct($str) { $this->str = $str; }
    function __toString() { return $this->str; }
}
function sqlstr($str) { return new sqlstr($str); }
