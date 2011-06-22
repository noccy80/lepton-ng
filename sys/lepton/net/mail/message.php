<?php

config::def('lepton.net.mail.from', '"Local Lepton Installation" <lepton@localhost>');

using('lepton.mvc.response'); // has content type code for now

/**
 * @class MailMessage
 * @example mimemessage.p
 * @brief Constructs a MIME encapsulated message
 *
 * The created message is compliant with RFC2045 and RFC822 and can be sent
 * using any SMTP backend.
 */
class MailMessage {
    const KEY_MAIL_FROM = 'lepton.net.mail.from';
    const NEW_LINE = "\n";
    private $recipients;
    private $subject;
    private $body;
    /**
     * @brief Constructor
     *
     * @param Mixed $recipient Recipient(s)
     * @param String $subject The subject of the message
     * @param Mixed $body The message body as a string or a IMimeEntity class
     */
    public function __construct($recipients,$subject,IMimeEntity $body=null) {
        $this->recipients = (array)$recipients;
        $this->subject = $subject;
        $this->body = $body;
    }
    
    public function __toString() {
    	return $this->getMessage();
    }

    /**
     *
     *
     */
    public function getMessage() {
        $headers = $this->buildHeaders();
        $headerstr = '';
        foreach($headers as $k=>$v) {
            if ($k) 
                $headerstr.=sprintf("%s: %s\r\n",$k,$v);
            else
                $headerstr.=sprintf("%s\r\n",$v);
        }
        $message = (string)$this->body;
        return $headerstr.$message;
    }
    
    public function getSubject() {
    	return $this->subject;
    }
    
    public function getRecipients() {
    	return $this->recipients;
    }
    
    /**
     *
     *
     */
    private function buildRecipientList() {
        $rep = array();
        foreach($this->recipients as $v) {
            $rep[] = sprintf("To: %s",$v);
		}
        return $rep;
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
            'From' => config::get(self::KEY_MAIL_FROM),
            'Subject' => $this->subject
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
    protected $contenttype = null;
    protected $boundary = null;
    protected $parts = array();
    function __construct() {
        // Create a unique boundary
        $this->boundary = sprintf('%08x%06x',time(),rand()*0xFFFFFF);
        $this->contenttype = sprintf('multipart/mixed; boundary="%s"',$this->boundary);
        $args = func_get_args();
        $this->parts = $args;
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
        $head = 'Content-Type: '.$this->contenttype."\r\n";
        $body = '';
        foreach((array)$this->parts as $part) {
            $body.= "\r\n--".$this->boundary."\r\n";
            $body.= (string)$part;
        }
        $body.= "--".$this->boundary."--\r\n";
	    return $head.$body;
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

class MimeAlternativeEntity extends MimeMultipartEntity {
    function __construct() {
        // Create a unique boundary
        $this->boundary = sprintf('%08x%06x',time(),rand()*0xFFFFFF);
        $this->contenttype = sprintf('multipart/alternative; boundary="%s"',$this->boundary);
        $args = func_get_args();
        $this->parts = $args;
    }
}


/**
 *
 *
 */
class MimeEntity implements IMimeEntity {
    private $mimetype;
    private $content;
    private $options;

    /**
     *
     *
     */
    function __construct($content,$mimetype=null,Array $options=null) {
        // TODO: Encode with QuotedPrintable as needed
        $this->options = (array)$options;
        $this->mimetype = $mimetype;
        $this->content = $content;
    }

    /**
     *
     *
     */
    function __toString() {
        $headers = array();
        $extra = (arr::hasKey($this->options,'charset'))?';charset='.$this->options['charset']:'';
        $headers["Content-Type"] = $this->mimetype.$extra;

        $headersstr = '';
        foreach($headers as $k=>$v) { $headersstr.=$k.': '.$v."\r\n"; }
        return $headersstr."\r\n".$this->content."\r\n";
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

    /**
     *
     *
     */
    function __construct($filename,Array $options=null) {
        $this->filename = $filename;
        $this->options = (array)$options;
        if (!file_exists($filename)) throw new FileNotFoundException();
    }

    /**
     *
     *
     */
    function __toString() {
    
    	// Resolve the content disposition as per RFC 2183
    	if (arr::hasKey($this->options,'inline') && ($this->options['inline'] == true)) {
    		$disposition = 'inline';
    	} else {
    		$disposition = 'attachment; filename="'.basename($this->filename).'"';
    	}
    	
    	// Assemble the headers
        $headers = array(
            'Content-Type' => response::contentTypeFromFile($this->filename),
            'Content-Transfer-Encoding' => 'base64',
            'Content-Disposition' => $disposition
        );
        $headersstr = '';
        $content = chunk_split(base64_encode(file_get_contents($this->filename)));
        foreach($headers as $k=>$v) { $headersstr.=$k.': '.$v."\r\n"; }
        return $headersstr."\r\n".$content."\r\n";
    }
}

