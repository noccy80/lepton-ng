<?php

class FruitsExample {

    function __construct() {
        $this->db = new DatabaseConnection();
    }

    function find($fruit) {
        try {
            $result = $this->db->getRows("SELECT * FROM fruit WHERE fruit=%s", $fruit);
            if (count($result) > 0) {
               // Do something with the data
               $fruitlist = array();
               foreach($results as $result) {
                   $fruitlist[] = $result['color'].' '.$result['fruit'];
               }
               return $fruitlist;
            } else {
                // No results
                return null;
            }
        } catch (DatabaseException $e) {
            // Handle errors here
        }
    }

    function addFruit($fruit,$color,$price,$stock) {
        // Data should be checked here to make sure everything is included
        $id = $this->db->insertRow("INSERT INTO fruit (fruit,color,price,stock) VALUES (%s,%s,%s,%d)",
            $fruit, $color, $price, $stock);
        // $id now contains the autonumber/index field
    }

    function updateFruit($id,$price,$stock) {
        $this->db->updateRow("UPDATE fruit SET price=%s, stock=%d WHERE id=%d", $id, $price, $stock);
    }

}
