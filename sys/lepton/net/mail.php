<?php

config::def('lepton.net.mail.from', 'Local Lepton Installation <lepton@localhost>');
config::def('lepton.net.mail.smtpserver','localhost');
config::def('lepton.net.mail.backend','smtp');

class MailMessage {

	private $message = null;
	private $subject = null;
	private $recipients = array();

	function __construct($recipients=null,$subject=null,$message=null) { 
		if ($this->recipients) $this->recipients = (array)$recipients;
		if ($this->subject) $this->subject = $subject;
		if ($this->message) $this->message = $message;
	}
	
	function addRecipient($recipient) {
		$this->recipients[] = $recipient;
	}

	function setSubject($subject) {
		$this->subject = $subject;
	}
	
	function setMessage($message) {
		$this->message = $message;
	}
	
	function send() {

		require_once('Mail.php');
	
		$headers = array(
			'From' => config::get('lepton.net.mail.from'),
			'To' => join(',',$this->recipients),
			'Subject' => $this->subject
		);
	
		$omail =& Mail::factory('smtp',)
		$omail->send($this->recipients,$headers,$this->message);
	
	}

}
