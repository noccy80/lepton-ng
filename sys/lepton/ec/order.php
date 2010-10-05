<?php __fileinfo("Lepton EC: Order management",array(
	'version' => '0.1.0'
));

class Order {

	private $products;
	private $provider;

	function __construct(Cart $cart) {
		
		$this->products = $cart->getProducts();

	}
	
	function getPaymentAlternatives() {
	
		// Do the magic here, ask class per configuration about what payment alternatives are
		// available for this product set.

	}
	
	function setPaymentProvider(PaymentProvider $provider) {

		// Assign the payment provider here
		$this->provider = $provider;
	
	}
	
	/**
	 * Prepare to check if payment was ok
	 */
	function prepare() {
	
		$this->provider->prepare();

	}
	
	function save() {
	
		// Save the order state.

	}
	
}

class Orders {

}
