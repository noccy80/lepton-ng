<?php

/**
 * @package xsnp.p2p
 *
 * Server-to-Server communication extension. This module allows you to add
 * support for communication between two websites or rather web servers.
 *
 * These functions should not be addressed to a fully featured uid, but
 * rather just the servers hostname.
 *
 * @author Christopher Vagnetoft
 */

using('xsnp.core');

class XsnpStsHandler extends XsnpHandler {

	function identify(XsnpRequest $request) {

		if ($request->destination == xsnp::hostname()) {
			// This is a server-to-server message
			return true;
		}

		// Pass it on to the next handler
		return false;

	}

	function handleRequest(XsnpRequest $request) {
		
	}

}

XsnpHandler::registerHandler(new XsnpStsHandler());
