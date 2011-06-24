<?php

using('lepton.net.httprequest');

class WowApiQuery {

	function __construct($region) {

		$this->region = $region;

	}

	public function getRealmStatus($realm = null) {

		$url = sprintf('http://%s.battle.net/api/wow/realm/status', $this->region);
		if ($realm != null) $url.= '?realm='.$realm;
		$request = new HttpRequest($url);
		$ret = json_decode((string)$request);

		return $ret;

	}

}

