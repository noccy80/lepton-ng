<?php

using('lepton.net.sockets');

class SmtpSocketException extends SocketException { }

class SmtpConnection {

	private $ssmtp = null;
	private $smtpserver = null;
	private $server = null;
	private $port = 25;

	function __construct($smtpserver,$smtpport=25) {
		$this->smtpserver = $smtpserver.':'.$smtpport;
		$this->server = $smtpserver;
		$this->port = $smtpport;
	}
	
	function __destruct() {
		logger::debug("Destructor for SmtpConnection invoked");
		$this->sendCommand('QUIT');
		unset($this->ssmtp);
	}
	
	function sendMessage($from,$to,$body) {
		$this->ssmtp = new TcpSocket();
		if ($this->ssmtp->connect($this->server,$this->port)) {
			$this->wait();
			$localhost = config::get('lepton.net.mail.localhost','localhost');
			logger::debug("Constructing SMTP connection. Identifying as '%s'", $localhost);
			$this->sendCommand('EHLO '.$localhost);
		} else {
			throw new SmtpSocketException("Could not connect to SMTP server");
		}
		$this->sendCommand('MAIL FROM: '.$from);
		$this->sendCommand('RCPT TO: '.$to);
		$this->sendData($body);
	}
	
	private function sendData($data) {
		// Make sure that no dots are alone!
		$sdata = $data."\r\n.";
		$this->sendCommand('DATA');
		$this->sendCommand($sdata);
	}
	
	private function sendCommand($cmd) {
		foreach(explode("\r\n", $cmd) as $cs) {
			logger::debug('SMTP %s > %s', $this->smtpserver, $cs);
		}
		$this->ssmtp->write($cmd."\r\n");
		// Read response and check if error
		return $this->wait();
	}
	
	private function wait() {
		$br = 0;
		while ($br == 0) { $ret= $this->ssmtp->read(1024,$br); }
		$ret = explode("\r\n", $ret);
		foreach($ret as $rs) {
			if (strlen($rs)>0)logger::debug('SMTP %s < %s', $this->smtpserver, $rs);
		}
		$status = explode(" ",$ret[0]);
		// Check status of the command
		$sv = array(
			intval($status[0][0]),
			intval($status[0][1]),
			intval($status[0][2])
		);
		if (($sv[0] == 1) || ($sv[0] == 2) || ($sv[0] == 3)) {
			// Positive response
			logger::debug('SMTP %s : Server indicated success', $this->smtpserver);
			return intval($status[0]);
		} 
		elseif (($sv[0] == 4) || ($sv[0] == 5)) {
			// Negative response
			logger::debug('SMTP %s : Server indicated error!', $this->smtpserver);
			throw new SmtpSocketException("Server indicated error: ".$ret[0], $status[0]);
		}
		else {
			logger::debug('SMTP %s : Server does not seem to speak SMTP', $this->smtpserver);
			throw new SmtpSocketException("Server does not seem to speak SMTP: ".$ret[0]);
		}
		return false;
	}

}
