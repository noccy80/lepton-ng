<?php

/**
 * @class ProductCategory
 * @brief A product category
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 */
class ProductCategory {

    private $categoryid = null;
    private $ambient = array();
    private $db;
    private $hasChildren = false;
    private $children = array();
    private $slug = null;
    private $category = null;
    private $parent = null;

    public function __construct($categoryid = null) {
        $this->categoryid = $categoryid;
        if ($categoryid) {
            // Grab the stuff we need from the database
            $this->db = new DatabaseConnection();
            $data = $this->db->getSingleRow("SELECT * FROM productcategories WHERE id=%d", $categoryid);
            $child = $this->db->getRows("SELECT id FROM productcategories WHERE parent=%d ORDER BY ordering,category ASC");
            // Organize children
            $childarr = array();
            foreach($cats as $child) {
                $childarr[] = ProductCategory($child['id']);
            }
            // Save properties
            $this->hasChildren = (count($child) > 0);
            $this->children = $childarr;
            if ($data) {
                $this->parent = $data['parent'];
                $this->category = $data['category'];
                $this->slug = $data['slug'];
            }
        }
    }

    public function __get($key) {

        switch($key) {
            case 'name':
                return $this->category;
            case 'id':
                return $this->categoryid;
            case 'parent':
                return ProductCategory($this->parent);
            case 'children':
                return $this->children;
            default:
                return $this->ambient[$key];
        }

    }

    public function getProductsByCategory(ProductCategory $category) {
        
    }

    public function getProducts() {

    }

    public function addProduct(Product $product) {

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

function ProductCategory($categoryid = null) {
    return new ProductCategory($categoryid);
}

