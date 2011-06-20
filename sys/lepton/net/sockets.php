<?php

define('SOCKPROTO_NONE', 0);
define('SOCKPROTO_TCP', 1);
define('SOCKPROTO_UDP', 2);

define('SOCKSTATE_CLOSED', 0);
define('SOCKSTATE_CONNECTING', 1);
define('SOCKSTATE_CONNECTED', 2);
define('SOCKSTATE_ERROR', 3);

interface ISocket {
    // connection state
    function connect($hostname, $port);
    function close();
    function listen();
    function accept();
    // communcation
    function write($data);
    function read($bytes,&$read);
}

class SocketException extends BaseException { }

abstract class Socket implements ISocket {
	const STATE_CLOSED = 0;
	const STATE_CONNECTING = 1;
	const STATE_CONNECTED = 2;
	const STATE_ERROR = 3;

	const PROTO_TCP = 1;
	const PROTO_UDP = 2;
	const PROTO_NONE = 0;
}

class TcpSocket extends Socket {

    private $localip = null;
    private $remoteip = null;
    private $localport = 0;
    private $remoteport = 0;
    private $protocol = self::PROTO_TCP;
    private $state = self::STATE_CLOSED;
    private $blocking = true; // All sockets block as default
    private $fsh = null;
    
    public function __destruct() {
	    logger::debug('Destroying socket');
        if ($this->fsh) $this->close();
    }

    public function connect($ip, $port) {
        $this->remoteip = $ip;
        $this->remoteport = $port;
        $errno = 0; $errstr = '';
        logger::debug("Connecting to %s:%d", $ip, $port);
        $this->state = SOCKSTATE_CONNECTING;
        $this->fsh = fsockopen($ip,$port,$errno,$errstr);
        if ($errno) {
	        logger::warning("Socket error: %d %s (%s:%d)", $errno, $errstr, $ip, $port);
            $this->state = SOCKSTATE_ERROR;
            return false;
        } else {
        	if (!$this->fsh) {
        		$this->state = SOCKSTATE_ERROR;
        		logger::warning("No socket handle returned but no error indicated");
        		return false;
        	}
	        logger::debug("Socket connected to %s:%d", $ip, $port);
            stream_set_timeout($this->fsh,0,200);
            $this->state = SOCKSTATE_CONNECTED;
            return true;
        }
    }

    public function close() {
        logger::debug("Socket disconnecting");
        if ($this->state == SOCKSTATE_CONNECTED) {
            @socket_close($this->fsh);
            @fclose($this->fsh);
            $this->fsh = 0;
        }
        $this->state = SOCKSTATE_CLOSED;
    }

    public function listen() {
        // Create a TCP Stream socket 
        $sock->fsh = socket_create(AF_INET, SOCK_STREAM, 0); 
        // Bind the socket to an address/port 
        if (socket_bind($this->fsh, $this->localip, $this->localport)) {
             // Start listening for connections 
            socket_listen($this->fsh); 
            $this->state = SOCKSTATE_LISTENING;
            return true;
        } else {
	        logger::warn('Could not bind to address'); 
            $this->state = SOCKSTATE_ERROR;
            return false;
        }

    }


    public function accept() {
        if ($this->sockstate == SOCKSTATE_LISTENING) {
            $sh = socket_accept($this->fsh);
            if ($sh) {
                $sock = new TcpSocket($sh);
                return $sh;
            } else {
                return false;
            }
        } else {
        logger::warn("Can't accept on non-listening socket");
        }
    }
    
    public function __set($key,$val) {
    	switch($key) {
    		case 'blocking':
    			$val = ($val == true);
    			logger::debug('Socket->%s set to %s', $key, $val);
    			stream_set_blocking($this->fsh,$val);
    			break;
    		default:
    			throw new BadPropertyException(__CLASS__,$key);
    	}
    }

    public function __get($key) {
    	switch($key) {
    		case 'blocking':
    			return $this->blocking;
    			break;
    		default:
    			throw new BadPropertyException(__CLASS__,$key);
    	}
    }

    public function write($data) {
        if ($this->state == SOCKSTATE_CONNECTED) {
            if (fwrite($this->fsh,$data) === false) {
				$errno = socket_last_error($this->fsh);
				$errstr = socket_strerror($errno);
				throw new SocketException(sprintf("Error while writing to socket: %s (%d)", $errstr, $errno));
            }
        } else {
        	throw new SocketException(sprintf("Writing to unavailable socket! (state:%d)", $this->state));
        }
    }

    public function read($bytes,&$read) {
        if ($this->state == SOCKSTATE_CONNECTED) {
            $data = fread($this->fsh,$bytes);
            $read = strlen($data);
            return $data;
        } else {
        	throw new SocketException(sprintf("Reading from unavailable socket! (state:%d)", $this->state));
        }
    }
}

abstract class UdpSocket extends Socket {

}

