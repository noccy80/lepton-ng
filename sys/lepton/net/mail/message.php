<?php

config::def('lepton.net.mail.from', 'Local Lepton Installation <lepton@localhost>');
config::def('lepton.net.mail.smtpserver','localhost');
config::def('lepton.net.mail.backend','smtp');

/**
 * @class MailMessage
 * @brief Constructs a MIME encapsulated message
 *
 * Compliant with RFC2045 and RFC822
 */
class MailMessage {
    const KEY_MAIL_FROM = 'lepton.net.mail.from';
    private $recipient;
    private $subject;
    /**
     * @brief Constructor
     *
     * @param Mixed $recipient Recipient(s)
     * @param String $subject The subject of the message
     * @param Mixed $body The message body as a string or a IMimeEntity class
     */
    public function __construct($recipient,$subject,IMimeEntity $body=null) {
        $this->recipient = (array)$recipient;
        $this->subject = $subject;
    }
    
    private function buildRecipientList() {
        
    }
    
    /**
     * @brief Return the headers needed to prefix the MIME content
     * @internal
     *
     * Headers:  From, Sender - Sender of message
     *           To, cc, bcc - Recipient, carbon copy, blind cc
     *           Subject - Subject
     *           In-Reply-To, References - Original message
     *           Date - Message date
     *
     * @return Array The headers
     */
    private function buildHeaders() {
        $headers = array(
            'MIME-Version' => '1.0 (Lepton Mail)',
            'From' => config::get(self::KEY_MAIL_FROM)
        );
        $address = $this->buildRecipientList();
        $headers = array_merge($headers,$address);

        return $headers;
    }
    
    /**
     * @brief Assign a new body to the message
     *
     * This method will discard the existing main entity and assign a
     * new one in its place.
     *
     * @param Mixed $body The new main entity
     */
    public function setBody($body) {
        $this->body = $body;
    }
    
    /**
     * @brief Add an attachment to the message
     *
     * This method will convert the main entity of the body to be a
     * MimeMultipartEntity instance if it is not already. The existing
     * body will be added to the multipart entity.
     *
     * @param String $filename The file to attach
     */
    public function addAttachment($filename) {
        if (vartype($this->body) != 'MimeMultipartEntity') {
            $this->body = new MimeMultipartEntity($this->body);
        }
        $this->body->addMessagePart(new MimeAttachment($file));
    }

    /**
     * @brief Add a new part to the message
     *
     * This method will convert the main entity of the body to be a
     * MimeMultipartEntity instance if it is not already. The existing
     * body will be added to the multipart entity.
     *
     * @param Mixed $part The part to add
     */
    public function addPart($part) {
        if (vartype($this->body) != 'MimeMultipartEntity') {
            $this->body = new MimeMultipartEntity($this->body);
        }
        $this->body->addMessagePart($part);
    }
}

interface IMimeEntity {
    function __toString();
}

/**
 * @class MimeMultipartEntity
 *
 *
 * Compliant with RFC2046
 */
class MimeMultipartEntity implements IMimeEntity {
    private $contenttype = null;
    private $boundary = null;
    private $parts = array();
    function __construct() {
        // Create a unique boundary
        $this->boundary = sprintf('%08x%06x',time(),rand()*0xFFFFFF);
        $this->contenttype = sprintf('multipart/alternative; boundary="%s"'),$this->boundary);
    }
    
    /**
     * @brief Cast the multipart entity to a string
     *
     * Will recursively cast any of its child entities into strings and
     * add the boundaries between the items. Each child entity is expected
     * to add its own MIME headers as needed.
     *
     * @return String The entity body
     */
    function __toString() {
        // Assemble headers and encode the body parts
        $body = "\r\n--".$this->boundary."--\r\n";
        foreach((array)$this->parts as $part) {
            $body.= (string)$part;
            $body.= "\r\n--".$this->boundary."--\r\n";
        }
        return $body;
    }
    
    /**
     * @brief Add a message part to the message
     *
     * @param IMimeEntity $part The part to append to the message
     */
    function addMessagePart(IMimeEntity $part) {
        $this->parts[] = $part;
    }
}


class MimeEntity implements IMimeEntity { 
    private $mimetype;
    function __construct($content,$mimetype=null,Array $options=null) {
        // TODO: Encode with QuotedPrintable as needed
    }
    function __toString() {
    }
}

/**
 * @class MimeAttachment
 * @brief Mime Attachment class
 *
 */
class MimeAttachment implements IMimeEntity {
    private $filename;
    private $options;
    
    function __construct($filename,Array $options=null) {
        $this->filename = $filename;
        $this->options = $options;
        if (!file_exists($filename)) throw new FileNotFoundException();
    }
    function __toString() {
        $headers = array(
            'content-type' => 'foo/bar',
            'content-transfer-encoding' => 'base64'
        );
        $headersstr = '';
        $content = base64_encode(file_get_contents($filename));
        foreach($headers as $k=>$v) { $headersstr.=$k.'='.$v."\r\n"; }
        return "\r\n".$headersstr."\r\n".$content;
    }
}

