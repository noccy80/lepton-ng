<?php module("Lepton EC: Product management",array(
        'version' => '0.1.0'
));

/**
 * @brief Interface for objects that can be purchased
 * @interface
 */
interface IPurchaseable {
    function __toString();
    function getUnitPrice();
    function getItemId();
    function getDescription();
}

/**
 * @brief Base class for objects that can be purcahsed
 * 
 * @author Christopher Vagnetoft
 */
abstract class Purchasable implements IPurchasable {

    protected $_properties = array();
    
    function __toString() {
        return $this->getDescription();
    }
    
    function __construct($itemid=null,$description=null,$unitprice=null) {
        $this->itemid = $itemid;
        $this->description = $description;
        $this->unitprice = $unitprice;
    }
    
    function __get($key) {
        if (arr::has($this->_properties, $key)) {
            return $this->_properties[$key];
        }
        return null;
    }
    
    function __set($key,$value) {
        $this->_properties[$key] = $value;
    }
     
    function getUnitPrice() {
        if ($this->amount == null) {
            return $this->unitprice;
        }
    }
    
    function getDescription() {
        return $this->description;
    }
    
    function getItemId() {
        return $this->itemid;
    }

}

// ex: new Billable('WORK','Work for Consulting',300,'Hours',399,'SEK');

class Billable extends Purchasable {
    
    function __construct($itemid) {
        parent::__construct($itemid);
    }
    
    function __toString() {
        
    }
    
    function getPrice() {
        
    }
    
    function getAmount() {
        
    }
    
    function getObjectId() { 
        
    }
    
}

class Discount extends Purchasable {
    
}

class Product {

    private $ambient = array(); ///< @var Ambient properties
    private $categories = array(); ///< @var Categories product belongs to
    private $productid = null;
    private $productname = null;
    private $db;

    public function __construct($productid = null) {
        $this->db = new DatabaseConnection();
        if (null != $productid) {
            $prod = $this->db->getSingleRow("SELECT * FROM products WHERE id=%s", $productid);
            $pcat = $this->db->getRows("SELECT p.* FROM productcategories p,productcategorylinks l WHERE l.productid=%s AND l.categoryid=p.it", $productid);
            // Parse product data and categories
        }
    }

    public function __get($key) {

    }

    public function __set($key,$value) {

    }

    public function __isset($key) {

    }

    public function __unset($key) {

    }


    /**
     * @brief Save changes to the database.
     * This method must be called after making any changes to any of the
     * products properties.
     */
    public function save() {

    }

}

function Product($productid=null) {
    return new Product($productid);
}

class ProductList extends BasicList {

}

abstract class Products {

    const ST_FULLTEXT = 0;
    const ST_SOUNDEX = 1;

    static function get($productid) {
        return new Product($productid);
    }

    static function find($string,$search = Products::ST_FULLTEXT) {

    }

}


