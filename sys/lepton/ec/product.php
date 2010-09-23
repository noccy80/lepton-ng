<?php __fileinfo("Lepton EC: Product management",array(
	'version' => '0.1.0'
));

class Product {

	private $ambient = array(); ///< @var Ambient properties
	private $categories = array(); ///< @var Categories product belongs to
	private $productid = null;
	private $productname = null;
	
	public function __initialize($productid = null) {
		$db = new DatabaseConnection();
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

class ProductList { }

abstract class Products {

	const ST_FULLTEXT = 0;
	const ST_SOUNDEX = 1;

	static function get($productid) {
		return new Product($productid);
	}
	
	static function find($string,$search = Products::ST_FULLTEXT) {
	
	}

}
