<?php module("Lepton EC: Shopping cart", array(
        'version' => '0.1.0'
));

/**
 * @brief Shopping cart implementation.
 * Automatically saved and loaded from the session as needed.
 */
class Cart implements IteratorAggregate, Countable, ArrayAccess {

    private $products = array();

    /* Constructor / Destructor */
    function __construct($cartid = null) {
        if (null == $cartid) {
            $cartid = 0;
        }
        $this->products = (array)session::get('lepton.ec.cart.'.$cartid);
        $this->cartid = $cartid;
    }

    function __destruct() {
        session::set('lepton.ec.cart.'.$this->cartid,$this->products);
    }

    /* IteratorAggregate */
    function getIterator() {
        return new ArrayIterator($products);
    }
    
    /* Countable */
    public function count() {
        return count($products);
    }
    
    /* ArrayAccess*/
    public function offsetExists( $offset) { }
    public function offsetGet($offset) { }
    public function offsetSet($offset, $value) { }
    public function offsetUnset($offset) { }

    /* Class methods */
    function addItem(IPurchasable $product, $amount = 1) {

        if (isset($this->products[$product->id])) {
            $this->products[$product->id]->count += $amount;
        } else {
            $this->products[$product->id] = new CartEntry($product,$amount);
        }

    }

    function updateItem($index, $amount) {

        $this->products[$index] = new CartEntry($product,$amount);

    }

    function removeItem($index) {

        unset($this->products[$index]);
        array_sort($this->products);

    }

    function getProducts() {

        return $this->products;

    }

}

class CartEntry {

    public $product;
    public $count = 0;

    function __construct($product,$count) {
        $this->product = $product;
        $this->count = $count;
    }

}
