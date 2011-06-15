<?php

using('xsnp.utils');
using('lepton.utils.prefs');
using('lepton.web.xmlrpc');
using('lepton.crypto.uuid');

class XsnpServer {

	private $extensions;

	function __construct() {
		$this->loadExtensions();
	}

	private function loadExtensions() {
		$desc = getDescendants('XsnpServerExtension');
		foreach($desc as $dc) {
			$this->extensions[] = new $dc($this);
		}
	}

	public function handleRequest($requestdata) {
		// Go through the extensions and decide which one is to handle it
	}

	function xmlrpcRequest() {
		$xr = new XmlrpcServer();
		if ($this->conf->interactenable != true) {
			$xr->sendError(0,'Function Disabled');
			return;
		}
		switch($xr->getMethod()) {
			case 'vs.siteinfo':
				$resp = array(
					'uid' => $this->conf->siteuid,
					'url' => $this->conf->siteurl,
					'name' => $this->conf->sitename
				);
				break;
			default:
				$xr->sendError(100,'Bad Request ('.$xr->getMethod().')');
				return;
		}
		$xr->sendResponse($resp);

	}

}






