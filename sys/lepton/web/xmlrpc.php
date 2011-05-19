<?php

/**
 * Xmlrpc utility class, loaded into controllers and libraries as xmlrpc
 * and provides shorthand functions for creating a new server and client.
 *
 * @since 0.2
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @see XmlrpcServer
 * @see XmlrpcClient
 */
class Xmlrpc {

	/**
	 * Create and return a new XmlrpcClient object.
	 *
	 * @param string $url The API url
	 * @return XmlrpcClient The client instance
	 */
	function createClient($url) {
		return new XmlrpcClient($url);
	}

	/**
	 * Create and return a new XmlrpcServer object.
	 *
	 * @return XmlrpcServer The server instance
	 */
	function createServer() {
		return new XmlrpcServer();
	}
}

/**
 * Class that wraps the functionality of an XMLRPC server. Create an
 * instance of the XmlrpcServer class and query the $method property in
 * order to resolve the method to invoke. All the method calling is to
 * be done in the controller, and the class itself doesn't do anything
 * but parsing and encoding of the resulting data or error message.
 *
 * The request data is contained in the $data property.
 *
 * @see XmlrpcClient
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @since 0.2
 * @todo Native parsing of requests; currently uses the php xmlrpc lib
 */
class XmlrpcServer {

	private $method;
	private $data;

	/**
	 * The constructor parses the request and populates the method and
	 * data properties.
	 */
	function __construct() {
		$req = request::getInput();
		$mtd = null;
		$data = xmlrpc_decode_request($req, $mtd);
		$this->method = $mtd;
		$this->data = $data;
	}

	/**
	 * Send a response to the client.
	 *
	 * @param array $data The data to return
	 */
	function sendResponse($data) {
		$ret = xmlrpc_encode_request(null, $data);
		response::contentType('text/xml');
		echo $ret;
	}

	/**
	 * Construct and send error response.
	 *
	 * @param int $code The error code
	 * @param string $reason The error reason
	 */
	function sendError($code,$reason) {

		// Create an empty DOM document for the error message
		$x = new DOMDocument('1.0','utf-8');
		$root = $x->createElement('methodResponse');
		$fault = $x->createElement('fault');
		$val = $x->createElement('value');
		$str = $x->createElement('struct');

		// Compile the fault code node
		$m1 = $x->createElement('member');
			$m1n = $x->createElement('name','faultCode');
			$m1v = $x->createElement('value');
			$m1va = $x->createElement('i4', $code);
			$m1v->appendChild($m1va);
			$m1->appendChild($m1n);
			$m1->appendChild($m1v);

		// Compile the fault string node
		$m2 = $x->createElement('member');
			$m2n = $x->createElement('name','faultString');
			$m2v = $x->createElement('value');
			$m2va = $x->createElement('string',$reason);
			$m2v->appendChild($m2va);
			$m2->appendChild($m2n);
			$m2->appendChild($m2v);

		$str->appendChild($m1);
		$str->appendChild($m2);
		$val->appendChild($str);
		$fault->appendChild($val);
		$root->appendChild($fault);
		$x->appendChild($root);

		// Send the response to the client
		response::contentType('text/xml');
		echo $x->saveXML();
	}

	function getMethod() {
		return $this->method;
	}

	function getData() {
		return $this->data;
	}

}

/**
 * This class wraps the functionality of a XMLRPC client.
 *
 * @see XmlrpcServer
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @since 0.2
 * @todo Native encoding of requests; currently uses the php xmlrpc lib
 */
class XmlrpcClient {

	private $url = null;

	/**
	 * Constructor, creates an instance of the XmlrpcClient class bound
	 * to the specific API url.
	 *
	 * @todo Handle username/password through arguments
	 * @param string $url The URL to query
	 */
	function __construct($url) {
		$this->url = $url;
	}

	/**
	 * Call a method on the server. The result is returned decoded as
	 * native PHP data.
	 *
	 * @param string $method The method to call
	 * @param any $data The data
	 * @return any The result data
	 */
	function call($method,$args=null) {

		$req = xmlrpc_encode_request($method, $args);
		$ret = http::post($this->url, $req, array(
			'content-type' => 'text/xml'
		));
		$mtd = null;
		$dec = xmlrpc_decode_request($ret,$mtd);
		return $dec;

	/*
		// Encode the request
		$xml = xmlrpc_encode_request( $method, $args );

		// Send it to the server
		$sparams = array('http' => array(
			'method' => 'POST',
			'content' => $xml,
			'header' => array(
				'content-type' => 'text/xml'
			)
		));
		$ctx = stream_context_create($params);
		$fp = @fopen($this->url, 'rb', false, $ctx);
		if (!$fp) {
			throw new Exception("Problem with $this->url, $php_errormsg");
		}
		$response = @stream_get_contents($fp);
		if ($response === false) {
			throw new Exception("Problem reading data from $url, $php_errormsg");
		}

		// Parse the output
		$ret = xmlrpc_decode_request($response,$mtd);
		return $ret;
	*/

	}

}



