<?php __fileinfo("HTTP Daemon", array(
	'depends' => array(
		'lepton.system.threading'
		)
	));


class HttpDaemon {

	private $ls;
	private $exit = false;

	function __construct($port=9090) {
		$this->ls = socket_create_listen($port);
		if (!$this->ls) {
			$err = socket_strerror(socket_last_error());
			throw new BaseException("Error when trying to open socket: ".$err);
		}
	}

	function run() {
		while(true != $this->exit) {
			$sock = socket_accept($this->ls);
			if ($sock) {
				$hs = new Thread(new HttpHandler($sock));
				$hs->start();
			}
		}
	}

	function stop() {
		$this->exit = true;
		socket_close($this->ls);
	}

}

class HttpHandler extends Runnable {
	private $sock;
	private $buffer;
	function __construct($sock) {
		$this->sock = $sock;
	}
	function threadmain() {
		// Read request block
		$buf = "";
		while(true) {
			$rl = socket_read( $this->sock, 4096, PHP_BINARY_READ );
			if (false == $rl) {
				socket_close($this->sock);
				return;
			} else {
				$buf = $buf . $rl;
			}
			if (strpos($buf, "\r\n\r\n")) {
				console::writeLn("Got what I need");
				break;
			} else {
				console::writeLn('%s', $buf);
			}
		}
		$db = explode("\r\n\r\n", $buf);
		$data = $db[0];
		$request = new HttpRequest($data); // data
		$response = new HttpResponse();
		// Pop the header off the buffer
		$d = new DaemonHandler();
		$d->handleRequest(&$request,&$response);
		printf("Writing headers\n");
		$response->writeHeaders($this->sock);
		printf("Writing content\n");
		$response->writeContent($this->sock);
		printf("Closing socket\n");
		socket_shutdown($this->sock,2);
		usleep(50000);
		socket_close($this->sock);
	}
}

class HttpRequest {
	private $method;
	private $protocol;
	private $uri;
	private $headers;
	function __construct($data) {
		$d = explode("\r\n", $data);
		$req = $d[0];
		$hdra = array_slice($d,1);
		$reqa = explode(" ",$req);
		$this->method = $reqa[0];
		$this->uri = $reqa[1];
		$this->protocol = $reqa[2];
		foreach($hdra as $row) {
			$h = explode(': ',$row);
			$this->headers[$h[0]] = $h[1];
		}
	}
	function getMethod() {
		return $this->method;
	}
	function getUri() {
		return $this->uri;
	}
	function getProtocol() {
		return $this->protocol;
	}
	function getHeader($header) {
		if (isset($this->headers[$header])) {
			return $this->headers[$header];
		} else {
			return null;
		}
	}
	function getAllHeaders() {
		return $this->headers;
	}
}

class HttpResponse {
	private $data = "";
	private $ds = "";
	private $contenttype;
	private $headers_sent = false;
	private $headers = array();
	function __construct() {
		$this->headers = array(
			'content-type' => 'text/php',
			'server' => 'Lepton Application Server/1.0',
			'content-length' => 0
		);
	}
	function setContentType($type) {
		$this->headers['content-type'] = $type;
	}
	function getContentType() {
		return $this->headers['content-type'];
	}
	function setHeader($header,$value) {
		$this->headers[$header] = $value;
	}
	function getHeader($header) {
		return $this->headers[$header];
	}
	function write($data) {
		$this->data .= $data;
		$this->setHeader('content-length', strlen($this->data));
	}
	function writeHeaders($sock) {
		foreach($this->headers as $hk=>$hd) {
			$headers[] = $hk.': '.$hd;
		}
		$this->ds = $this->data;
		$this->data = "";
		$buf = join("\r\n", $headers) . "\r\n\r\n";
		$bw = socket_write($sock,$buf);
		if ($bw != strlen($buf)) {
			throw new BaseException("Couldn't write buffer");
		}
		$this->headers_sent = true;
	}
	function writeContent($sock) {
		$bd = $this->ds;
		printf("Writing: ");
		$bw = socket_write($sock,$bd);
		printf("Done\n");
		if ($bw != strlen($bd)) {
			throw new BaseException("Couldn't write buffer");
		}
	}
}

abstract class HttpRequestHandler {
	function handleRequest(HttpRequest &$request, HttpResponse &$response) {
		$response->setContentType("text/html");
		$response->write("<h1>Website!</h1><p>This is awesome.</p>");
	}
}

class DaemonHandler extends HttpRequestHandler { }
