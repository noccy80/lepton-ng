<?php

using('ldwp.job');

/**
 * DownloadJob for LDWP
 *
 * Purpose:   Download a file from a specific URL while providing progress in
 *            any open LDWP consoles. This class is to be used as an example
 *            of how jobs are managed in LDWP.
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @license GPL v3
 */
class DownloadJob extends LdwpJob {

	private $url = null;
	private $destination = null;

	/**
	 * Constructor, accepts the URL and the destination as the only parameters.
	 *
	 * @param String $url The URL to download
	 * @param String $destination The destination where the file is to be saved
	 */
	function __construct($url,$destination) {
		$this->url = $url;
		$this->destination = $destination;
		parent::__construct();
	}

	/**
	 * Magic Start method. When this method is invoked the job is to pick up
	 * where it left off and resume or start the processing of the job.
	 */
	function start() {
		$this->setState(LdwpJob::STATE_RUNNING,0,1,"Downloading ".$this->url);
		using('lepton.net.httprequest');
		$dl = new HttpRequest($this->url,array(
			'saveto' => $this->destination
		));
		$this->setState(LdwpJob::STATE_COMPLETED,1,1,"Saved ".$this->url);
	}

}
