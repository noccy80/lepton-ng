<?php

class StreamContext {
    
    const SOCKET_BINDTO = 'bindto';
    const SOCKET_BACKLOG = 'backlog';
    const HTTP_METHOD = 'method';
    const HTTP_HEADER = 'header';
    const HTTP_USER_AGENT = 'user_agent';
    const HTTP_CONTENT = 'content';
    const HTTP_PROXY = 'proxy';
    const HTTP_REQUEST_FULLURI = 'request_fulluri';
    const HTTP_FOLLOW_LOCATION = 'follow_location';
    const HTTP_MAX_REDIRECTS = 'max_redirects';
    const HTTP_PROTOCOL_VERSION = 'protocol_version';
    const HTTP_TIMEOUT = 'timeout';
    const HTTP_IGNORE_ERRORS = 'ignore_errors';
    const FTP_OVERWRITE = 'overwrite';
    const FTP_RESUME_POS = 'resume_pos';
    const FTP_PROXY = 'proxy';
    const SSL_VERIFY_PEER = 'verify_peer';
    const SSL_ALLOW_SELF_SIGNED = 'allow_self_signed';
    const SSL_CAFILE = 'cafile';
    const SSL_CAPATH = 'capath';
    const SSL_LOCAL_CERT = 'local_cert';
    const SSL_PASSPHRASE = 'passphrase';
    const SSL_CN_MATCH = 'CN_match';
    const SSL_VERIFY_DEPTH = 'verify_depth';
    const SSL_CIPHERS = 'ciphers';
    const SSL_CAPTURE_PEER_CERT = 'capture_peer_cert';
    const SSL_CAPTURE_PEER_CERT_CHAIN = 'capture_peer_cert_chain';
    const SSL_SNI_ENABLED = 'SNI_enabled';
    const SSL_SNI_SERVER_NAME = 'SNI_server_name';

    private $ctx = null;
    
    public function __construct(array $options=null,array $params=null) {
        $this->ctx = stream_context_create($options,$param);
    }
    
    public function setOption($option,$value) {
        stream_context_set_option($this->ctx,array($option,$value));
    }
    
    public function getContext() {
        return $this->ctx;
    }
    
}

class Stream {
    
    private $uri = null;
    private $fh = null;
    
    public function __construct($streamuri,$mode='r',$context=null) {
        $this->uri = $streamuri;
        if (typeof($context) == 'StreamContext') {
            $ctx = $context->getContext();
        } else {
            $ctx = $context;
        }
        if ($ctx) {
            $this->fh = fopen($streamuri,$mode,null,$ctx);
        } else {
            $this->fh = fopen($streamuri,$mode);
        }
    }
    
    public function __destruct() {
        if ($this->fh) fclose($this->fh);
    }
    
    public function seek($offset,$whence=null) {
        return fseek($this->fh, $offset, $whence);
    }
    
    public function tell() {
        return ftell($this->fh);
    }
    
    public function puts($data) {
        fputs($this->fh,$data);
    }
    
    public function gets() {
        return fgets($this->fh);
    }
    
    public function write($data) {
        fwrite($this->fh,$data);
    }
    
    public function read($length) {
        return fread($this->fh, $length);
    }
    
}