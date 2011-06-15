<?php

using('lepton.net.httprequest');
using('xsnp.utils');

class XsnpClient {

	function __construct($identity) {

		list($host,$user,$meta) = XsnpUtils::parseIdentity($identity);
		$url = XsnpUtils::resolve($identity);
		if ($url) {
			$req = new HttpRequest($url,array(
				'method' => 'post'
			));
		}

	}

	

	function siteinfo() {
		$xc = new XmlrpcClient('http://ebooks.noccylabs.info/api/xmlrpc');
		debug::inspect($xc->call('vs.siteinfo'));
	}


}
