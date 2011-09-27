<?php

class MailException extends Exception { }

config::def('lepton.net.mail.smtpserver','localhost');
config::def('lepton.net.mail.localhost','localhost');
config::def('lepton.net.mail.smtpport',25);
config::def('lepton.net.mail.backend','PearMailBackend');
config::def('lepton.net.mail.pear.backend','smtp');

interface IMailerBackend {
	public function sendMessage(MailMessage $message);
}

abstract class MailerBackend implements IMailerBackend {

	static function send(MailMessage $message) {
	
		$bc = config::get('lepton.net.mail.backend');
		$b = new $bc();
		return $b->sendMessage($message);
		
	}
	
}

class LeptonSmtpBackend extends MailerBackend {

	public function sendMessage(MailMessage $message) {

		$smtphost = config::get('lepton.net.mail.smtpserver','localhost');
		$smtpport = config::get('lepton.net.mail.smtpport',25);
		$from = $message->getFrom();
		$rcpt = $message->getRecipients();

		$s = new SmtpConnection($smtphost,$smtpport);
		$s->sendMessage($from, $rcpt, (string)$message);
		unset($s);
	
	}

}

class PearMailBackend extends MailerBackend {

	public function sendMessage(MailMessage $message) {

		@require_once('Mail.php');
	
		$headers = array(
			'From' => config::get('lepton.net.mail.from'),
			'To' => join(',',$message->getRecipients()),
			'Subject' => $message->getSubject()
		);
		$params = array(
			'host' => config::get('lepton.net.mail.smtpserver','localhost'),
			'port' => config::get('lepton.net.mail.smtpport',25),
			'localhost' => config::get('lepton.net.mail.localhost','localhost')
		);

		try {
			$omail =& Mail::factory('smtp', $params);
			$omail->send($message->getRecipients(),$headers,$message->getMessage());
		} catch (Exception $e) {
			throw new MailException("Sending of mail failed");
		}

	}

}

class Mail {

    static function send(MailMessage $message) {
    
    	MailerBackend::send($message);
    	
    }
    
}
