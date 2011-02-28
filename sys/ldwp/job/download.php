<?php

using('ldwp.job');

class DownloadJob extends LdwpJob {

	private $url = null;
	private $destination = null;

	function __construct($url,$destination) {
		$this->url = $url;
		$this->destination = $destination;
		parent::__construct();
	}

	function start() {
		$this->setState(LdwpJob::STATE_RUNNING,0,1,"Downloading ".$this->url);
		using('lepton.net.httprequest');
		$dl = new HttpRequest($this->url,array(
			'saveto' => $this->destination
		));
		$this->setState(LdwpJob::STATE_COMPLETED,1,1,"Saved ".$this->url);
	}

}
