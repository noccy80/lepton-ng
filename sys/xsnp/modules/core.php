<?php

class XsnpBondingRequest extends XsnpRequest { }

class XsnpCoreExtension extends XsnpExtension {

	// This module is set to handle all the core requests
	function _getNameSpace() { return "core"; }

	/**
	 * core:bonding/request
	 *    suid - source uid
	 *    duid - dest uid
	 */
	function bonding_request(XsnpRequest $request) {
		// Check if user is bonded already

		// Generate a new prime and a shared secret
		$p = math::generatePrime();
		$pn = $p[rand(0,count($p)-1];
		$ps = math::random(0,500000);
		// Store private key and send public key
		$ret['pubkey'] = $ps;
		// Save all the information to the database
	}
	
	/**
	 * core:bonding/response
	 *    suid - source uid
	 *    duid - dest uid
	 *    nonce - 
	 */
	function bonding_response(XsnpRequest $request) {
		// Handle response
	}
	
	/** 
	 * core:pubsub/subscribe
	 *
	 *   event - the event to subscribe
	 */
	function pubsub_subscribe(XsnpRequest $request) {
	}
	
	/**
	 * core:pubsub/unsubscribe
	 *
	 *   event - the event to unsubscribe
	 */
	function pubsub_unsubscribe(XsnpRequest $request) {
	}

	/**
	 * core:pubsub/receive
	 *
	 *   event - name of event
	 *   data - the data of the event
	 */
	function pubsub_receive(XsnpRequest $request) {
	
	}

}
