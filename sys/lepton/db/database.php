<?php

    ModuleManager::load('lepton.db.driver');
    ModuleManager::load('lepton.db.drivers.*');

    class DatabaseException extends BaseException { }

    abstract class DatabaseManager {

        static $pool = array();
        static function getConnection($group='default') {

            if (!isset(self::$pool[$group])) {
                Console::debugEx(LOG_DEBUG1,__CLASS__,"PoolConnectGroup(%s)", $group);        
                self::$pool[$group] = self::poolConnectGroup($group);
            }
            return self::$pool[$group];
            
        }
        
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

        static function getPooledConnection() {

            // Mangle pool and return a connection

        }

    }




    final class DatabaseConnection {
    
        private $db_conn;
        
        function __construct($set='default') {

            Console::debugEx(LOG_DEBUG1,__CLASS__,"Initializing connection for %s.", $set);        
            $this->db_conn = DatabaseManager::getConnection($set);
            
        }

        function getRows($pattern,$vars=null) {

            $args = func_get_args();
            $sql = $this->db_conn->escapeString($args);
            Console::debugEx(LOG_DEBUG1,__CLASS__,"GetRows: %s", $sql);
            Database::$counter++;
            Database::$queries['QUERYING']++;
            $queryresult = $this->db_conn->query($sql);
            
            return $queryresult['data'];
        
        }
        
        function getSingleRow($pattern,$vars=null) {

            $args = func_get_args();
            $sql = $this->db_conn->escapeString($args);
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
        
        function getSingleValue($pattern,$vars=null) {

            $args = func_get_args();
            $sql = $this->db_conn->escapeString($args);
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
        
        function insertRow($pattern,$vars=null) {

            $args = func_get_args();
            $sql = $this->db_conn->escapeString($args);
            Console::debugEx(LOG_DEBUG1,__CLASS__,"InsertRow: %s", $sql);
            Database::$counter++;
            Database::$queries['UPDATING']++;
            $queryresult = $this->db_conn->query($sql);

            return $this->db_conn->autonumber;
        
        }
        
        function updateRow($pattern,$vars=null) {

            $args = func_get_args();
            $sql = $this->db_conn->escapeString($args);
            Console::debugEx(LOG_DEBUG1,__CLASS__,"UpdateRow: %s", $sql);
            Database::$counter++;
            Database::$queries['UPDATING']++;
            $queryresult = $this->db_conn->query($sql);
            $affected = $queryresult['count'];

            return $affected;
        
        }
        
        function exec($pattern,$vars=null) {

            $args = func_get_args();
            $sql = $this->db_conn->escapeString($args);
            Database::$counter++;
            Database::$queries['EXECUTING']++;
            $queryresult = $this->db_conn->exec($sql);

            return null;
        
        }
        
        function transactionBegin() {
            return false;
        }
        
        function transactionEnd() {
            return false;
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
