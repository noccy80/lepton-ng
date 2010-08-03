<?php

define('SOCKPROTO_NONE', 0);
define('SOCKPROTO_TCP', 1);
define('SOCKPROTO_UDP', 2);

define('SOCKSTATE_CLOSED', 0);
define('SOCKSTATE_CONNECTING', 1);
define('SOCKSTATE_CONNECTED', 2);
define('SOCKSTATE_ERROR', 3);

class NetworkResolver {
	static function resolve($hostname) {
		return gethostbyname($hostname);
	}
}

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

abstract class Socket implements ISocket {
	public function __set($prop,$value) {
		if ((isset($this->properties[$prop])) && (!isset($this->propertiesprotect[$prop]))) {
			$this->properties[$prop] = $value;
		} else {
			Console::warn("Attempt to set unknown socket property %s", $prop);
		}
	}
	public function __get($prop) {
		if (isset($this->properties[$prop])) {
			return $this->properties[$prop];
		} else {
			Console::warn("Attempt to get unknown socket property %s", $prop);
		}
	}
}

class TcpSocket extends Socket {
	var $properties = array(
		'localip' => '',
		'remoteip' => '',
		'localport' => 0,
		'remoteport' => 0,
		'protocol' => SOCKPROTO_TCP,
		'state' => SOCKSTATE_CLOSED
	);
	var $propertiesprotect = array(
		'protocol',
		'state'
	);
	private $fsh;

	public function __destruct() {
		if ($this->fsh) $this->close();
	}

	public function connect($ip, $port) {
		$this->remoteip = $ip;
		$this->remoteport = $port;
		$errno = 0; $errstr = '';
		Console::debug("Connecting to %s:%d", $ip, $port);
		$this->state = SOCKSTATE_CONNECTING;
		$this->fsh = fsockopen($ip,$port,$errno,$errstr);
		if ($errno) {
			Console::warn("Socket error: %d %s (%s:%d)", $errno, $errstr, $ip, $port);
			$this->state = SOCKSTATE_ERROR;
			return false;
		} else {
			Console::debug("Socket connected to %s:%d", $ip, $port);
			stream_set_timeout($this->fsh,0,200);
			$this->state = SOCKSTATE_CONNECTED;
			return true;
		}
	}

	public function close() {
		Console::debug("Socket disconnecting");
		if ($this->state == SOCKSTATE_CONNECTED) {
			@socket_close($this->fsh);
			@fclose($this->fsh);
			unset($this->fsh);
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
			Console::warn('Could not bind to address'); 
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
			Console::warn("Can't accept on non-listening socket");
		}
	}

	public function write($data) {
		if ($this->state == SOCKSTATE_CONNECTED) {
			fwrite($this->fsh,$data);
		} else {
			Console::warn("Writing to unavailable socket!");
		}
	}

	public function read($bytes,&$read) {
		if ($this->state == SOCKSTATE_CONNECTED) {
			$data = fread($this->fsh,$bytes);
			$read = strlen($data);
			return $data;
		} else {
			Console::warn("Reading from unavailable socket!");
		}
	}
}

abstract class UdpSocket extends Socket {

}

?>
