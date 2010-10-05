<?php __fileinfo("Lepton EC: Product management",array(
	'version' => '0.1.0'
));

class ProductCategory {

	private $categoryid;
	private $ambient = array();
	private $db;
	private $name;

	public function __construct($categoryid = null) {
		$this->categoryid = $categoryid;
		$this->db = new DatabaseConnection();
		$data = $this->db->getSingleRow("SELECT * FROM productcategories WHERE id=%d", $categoryid);
	}
	
	public function __get($key) {
		
		switch($key) {
			case 'name':
				return $this->name;
			case 'id':
				return $this->id;
			default:
				return $this->ambient[$key];
		}
		
	}

	static function find($categoryslug) {
		$db = new DatabaseConnection();
		try {
			$cat = $db->getRows("SELECT * FROM productcategories WHERE slug=%s", $categoryslug);
			if ($cat) {
				$id = $cat['id'];
				return new ProductCategory($id);
			} else {
				return null;
			}
		} catch(Exception $e) {
			return null;
		}
	}

}

function ProductCategory($categoryid = null) { return new ProductCategory($categoryid); }

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

function Product($productid=null) { return new Product($productid); }

class ProductList extends BasicList { }

abstract class Products {

	const ST_FULLTEXT = 0;
	const ST_SOUNDEX = 1;

	static function get($productid) {
		return new Product($productid);
	}
	
	static function find($string,$search = Products::ST_FULLTEXT) {
	
	}

}
