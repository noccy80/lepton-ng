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
        try {
            $this->sendCommand('QUIT');
        } catch (SocketException $e) { }
		unset($this->ssmtp);
	}
	
	function sendMessage($from,$to,$body) {
	
		// Create the socket and connect to the server
		$this->ssmtp = new TcpSocket();
		if ($this->ssmtp->connect($this->server,$this->port)) {
			// If we are connected, wait for the banner
			$this->wait();
			$localhost = config::get('lepton.net.mail.localhost','localhost');
			logger::debug("Constructing SMTP connection. Identifying as '%s'", $localhost);
			$this->sendCommand('EHLO '.$localhost);
		} else {
			throw new SmtpSocketException("Could not connect to SMTP server");
		}

		// Make the body conform to RFC specifications
		$body = str_replace("\r\n","\n",$body);
		$body = str_replace("\n.","\n..",$body);

		// Send the commands
		$this->sendCommand('MAIL FROM: '.$from);
		foreach((array)$to as $top) 
			$this->sendCommand('RCPT TO: '.$top);
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

        return true;
        
        $br = 0;
		while ($br == 0) { $ret= $this->ssmtp->read(1024,$br); }
		$ret = explode("\r\n", $ret);
		foreach($ret as $rs) {
			if (strlen($rs)>0)logger::debug('SMTP %s < %s', $this->smtpserver, $rs);
		}
		$status = $ret[0];
		// Check status of the command
		$sv = array(
			intval($status[0]),
			intval($status[1]),
			intval($status[2])
		);
		if (($sv[0] == 1) || ($sv[0] == 2) || ($sv[0] == 3)) {
			// Positive response
			logger::debug('SMTP %s : Server indicated success', $this->smtpserver);
			return intval($status[0]);
		} 
		elseif (($sv[0] == 4) || ($sv[0] == 5)) {
			// Negative response
			logger::debug('SMTP %s : Server indicated error!', $this->smtpserver);
			throw new SmtpSocketException("Server indicated error: ".$status);
		}
		else {
			logger::debug('SMTP %s : Server does not seem to speak SMTP', $this->smtpserver);
			throw new SmtpSocketException("Server does not seem to speak SMTP: ".$status);
		}
		return false;
	}

}
