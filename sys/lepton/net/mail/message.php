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
    const KEY_MAIL_FROMNAME = 'lepton.net.mail.fromname';
    
    const FROM_ADDRONLY = 1;
    const FROM_NAMEONLY = 2;
    const FROM_FULL = 3;
    
    // These values are used with the getRecipients and getReceipientList
    // methods.
    const ADDR_TO = 1;
    const ADDR_CC = 2;
    const ADDR_BCC = 4;
    const ADDR_MIME = 3; // to + cc
    const ADDR_ALL = 255; // to + cc + bcc
    
    const RCPT_TO = 'to';
    const RCPT_CC = 'cc';
    const RCPT_BCC = 'bcc';
    
    const NEW_LINE = "\n";
    private $recipients = array();
    private $subject = null;
    private $body = null;
    private $headers = array();
    /**
     * @brief Constructor
     *
     * @param Mixed $recipient Recipient(s)
     * @param String $subject The subject of the message
     * @param Mixed $body The message body as a string or a IMimeEntity class
     */
    public function __construct($recipients,$subject,IMimeEntity $body=null) {
        $this->recipients = array(
            'to' => array(),
            'cc' => array(),
            'bcc' => array()
        );
        $this->addRecipients($recipients);
        $this->subject = $subject;
        $this->body = $body;
    }

    public function addRecipients($recipients, $as = self::RCPT_TO) {
        if (!$recipients) return;
        if (!$as) throw new BadArgumentException("Recipient scope must be one of RCPT_TO, RCPT_CC or RCPT_BCC!");
        if (typeof($recipients) == 'array') {
            $this->recipients[$as] = array_merge((array)$this->recipients[$as], $recipients);
        } else {
            $this->recipients[$as][] = $recipients;
        }
    }

    /**
     * @brief String cast using getMessage()
     * 
     * @return string The message
     */
    public function __toString() {
        return $this->getMessage();
    }

    /**
     * @brief Turns the message into a string suitable for sending.
     * 
     * This will process all the message parts added, and recursively
     * assemble the full message content.
     *
     * @return string The message
     */
    public function getMessage() {
        $headers = $this->buildHeaders();
        $headerstr = '';
        foreach($headers as $k=>$v) {
            if ($k) 
                if (typeof($v) == 'array') {
                    foreach($v as $vv) {
                        $headerstr.=sprintf("%s\r\n", $vv);
                    } 
                } else {
                    $headerstr.=sprintf("%s: %s\r\n",$k,$v);
                }
            else
                $headerstr.=sprintf("%s\r\n",$v);
        }
        $message = (string)$this->body;
        return $headerstr.$message;
    }
    
    /**
     * @brief Adds a header to the message.
     * 
     * Note that in order to add two of the same header you will have to pass
     * them as strings in a sub-array. This is nasty but unavoidable as two of
     * the same would overwrite each other in PHP. When an array is found as
     * the second parameter it will be joined as text, so make sure to include
     * the actual header for each of the items in the array.
     * 
     * @param string $header The header to assign
     * @param Mixed $value The value (or array)
     */
    function addHeader($header,$value) {
        $this->headers[$header] = $value;
    }
    
    /**
     * @brief Returns the headers 
     * 
     * @return array The header collection
     */
    function getHeaders() {
        return $this->headers;
    }

    /**
     * @brief Return the subject
     * 
     * @return string The subject
     */
    public function getSubject() {
        return $this->subject;
    }
    
    /**
     * @brief Returns a list of all the recipients of the message
     * 
     * The default scope for this recipient list is self::ADDR_ALL as this is
     * what is to be handed off to the SMTP server in order to assure delivery
     * even to BCC addresses. Per the RFC, all recipients should be included
     * with no difference between them, as this is used for the routing and
     * delivery information.
     *
     * @param int $addrs The Address scope to return
     * @reutrn array Array of the recipients
     */
    public function getRecipients($addrs = self::ADDR_ALL) {
        $rep = array();
        
        if ($addrs & self::ADDR_TO)
            foreach((array)$this->recipients[self::RCPT_TO] as $v) {
                $rep[] = $v;
            }
            
        if ($addrs & self::ADDR_CC) 
            foreach((array)$this->recipients[self::RCPT_CC] as $v) {
                $rep[] = $v;
            }

        if ($addrs & self::ADDR_BCC)
            foreach((array)$this->recipients[self::RCPT_BCC] as $v) {
                $rep[] = $v;
            }
            
        return $rep;
    }
    
    /**
     * @brief Builds the list of the recipients.
     * 
     * The default scope for this recipient list is self::ADDR_MIME and in all
     * fairness nothing else should ever be needed. The BCC addresses should
     * not be included in the headers, as this would kind of remove the whole
     * "blind" idea from them. ADDR_MIME will return the TO and CC addresses
     * only, neatly formatted to be used as headers.
     *
     * @param int $addrs The Address scope to return
     * @reutrn array Array of the headers
     */
    private function buildRecipientList($addrs = self::ADDR_MIME) {
        $rep = array();
        
        if ($addrs & self::ADDR_TO)
            foreach((array)$this->recipients[self::RCPT_TO] as $v) {
                $rep[] = sprintf("To: %s",$v);
            }
            
        if ($addrs & self::ADDR_CC) 
            foreach((array)$this->recipients[self::RCPT_CC] as $v) {
                $rep[] = sprintf("Cc: %s",$v);
            }

        if ($addrs & self::ADDR_BCC)
            foreach((array)$this->recipients[self::RCPT_BCC] as $v) {
                $rep[] = sprintf("Bcc: %s",$v);
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

        $address = array(
            'To' => $this->buildRecipientList(self::ADDR_MIME)
        );
        $headers = array_merge($headers,$address,$this->headers);

        return $headers;
    }
    
    /**
     * @brief Get the from address
     * 
     * @param int $get The scope as one of FROM_FULL, FROM_ADDRONLY or FROM_NAMEONLY
     * @return string The from address
     */
    public function getFrom($get = self::FROM_FULL) {
        switch($get) {
            case self::FROM_ADDRONLY:
                return config::get(self::KEY_MAIL_FROM);
            case self::FROM_NAMEONLY:
                return config::get(self::KEY_MAIL_FROMNAME);
            default:
                return sprintf('"%s" <%s>', 
                    config::get(self::KEY_MAIL_FROMNAME),
                    config::get(self::KEY_MAIL_FROM));
        }
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
 * @brief Defines a set of entities that are part of the message.
 *
 * The MimeMultipartEntity defines a message consisting of several parts, each
 * which is an addition to the message. The MimeAlternativeEntity works in a
 * similar way but it consist of the same message wrapped in different entitys,
 * of which the recipients client will chose the best one it can handle.
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

/**
 * @class MimeAlternativeEntity
 * @brief 
 * 
 * The MimeAlternativeEntity contains several different versions of the same
 * message body of which the mail client will pick one to display based on
 * the users' preferences and compatibility.
 * 
 */
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
        if (!file_exists($filename)) throw new FileNotFoundException("Unable to attach file since it does not exist", $filename);
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
        if (arr::hasKey($this->options,'contentid')) {
            $headers['Content-ID'] = $this->options['contentid'];
        }
        $headersstr = '';
        $content = chunk_split(base64_encode(file_get_contents($this->filename)));
        foreach($headers as $k=>$v) { $headersstr.=$k.': '.$v."\r\n"; }
        return $headersstr."\r\n".$content."\r\n";
    }
}

