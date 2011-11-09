<?php

/**
 * @brief Interface for Database Drivers
 */
interface IDatabaseDriver {

    function execute($query,$attr);
    function query($query,$attr);
    function transactionBegin();
    function transactionEnd();
    function quote($string);

}

abstract class DatabaseDriver implements IDatabaseDriver {
    
    private $identity = null;
    
    public static function getDriverClass($driverinfo) {
        if (strpos($driverinfo,'/') !== false) {
            $driver = substr($driverinfo,0,strpos($driverinfo,'/'));
        } elseif (strpos($driverinfo,'::') !== false) {
            $driver = substr($driverinfo,0,strpos($driverinfo,'::'));
        } else {
            $driver = $driverinfo;
        }
        if (!$driver) throw new DatabaseException("No driver specified");
        $driverclass = $driver.'DatabaseDriver';
        return $driverclass;
    }
    
    public function __destruct() {
        if (class_exists('DatabaseConnectionPool')) {
            DatabaseConnectionPool::freeConnection($this);
        }
    }
    
    public function setPoolIdentity($identity) {
        $this->identity = $identity;
    }
    
    public function getPoolIdentity() {
        return $this->identity;
    }
    
}


