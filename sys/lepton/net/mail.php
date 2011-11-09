<?php

/**
 * WARNNING! This implementation will be removed shortly to be replaced by 
 * the full mime implementation at lepton.net.mail.*!
 *
 * The APIs should be somewhat identical, but you should still be careful
 * when using this code.
 */

config::def('lepton.net.mail.from', 'Local Lepton Installation <lepton@localhost>');
config::def('lepton.net.mail.smtpserver','localhost');
config::def('lepton.net.mail.localhost','localhost');
config::def('lepton.net.mail.smtpport',25);
config::def('lepton.net.mail.backend','PearMailerBackend');
config::def('lepton.net.mail.pear.backend','smtp');

class MailException extends Exception { }

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

class PearMailerBackend extends MailerBackend {
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

class MailMessage {

    private $message = null;
    private $subject = null;
    private $recipients = array();

    function __construct($recipients=null,$subject=null,$message=null) { 
        if ($this->recipients) $this->recipients = (array)$recipients;
        if ($this->subject) $this->subject = $subject;
        if ($this->message) $this->message = $message;
    }
    
    function getRecipients() {
        return $this->recipients;
    }
    
    function getSubject() {
        return $this->subject;
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
    
    function getMessage() {
        return $this->message;
    }
    
    function send() {
        return MailerBackend::send($this);
    }

}

class Mail {
    static function send(MailMessage $message) {
        MailerBackend::send($message);
    }
}
