<?php __fileinfo("Data import support for databases");

class DatabaseSeed {

    function  __construct($filename=null) {

    }

    function insertPrepared($db,$statement) {
        $ds = $db->prepareStatement($statement);
        // Insert all the data now
        
    }


}
