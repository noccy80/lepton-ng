<?php module("HTTP Daemon", array(
    'depends' => array(
        'lepton.system.threading'
        )
    ));

class SocketException extends BaseException { }

class HttpDaemon {

    private $ls;
    private $exit = false;
    private $handler = null;

    function __construct($port=9090,$handler=null) {
        $this->ls = socket_create_listen($port);
        $this->handler = $handler;
        if (!$this->ls) {
            $err = socket_strerror(socket_last_error());
            throw new SocketException("Error when trying to open socket: ".$err);
        }
    }

    function run() {
        while(true != $this->exit) {
            $sock = socket_accept($this->ls);
            if ($sock) {
                $hs = new Thread(new HttpHandler($sock,$this->handler));
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
    private $handler;
    function __construct($sock,$handler) {
        $this->sock = $sock;
        $this->handler = $handler;
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
                break;
            } else {
                console::writeLn('%s', $buf);
            }
        }
        $db = explode("\r\n\r\n", $buf);
        $data = $db[0];
        // Put back the rest of the buffer for posts etc
        $buf = join('',array_slice($db,1));
        $request = new HttpRequest($data); // data
        $response = new HttpResponse();

        // Pop the header off the buffer
        $status = call_user_func_array($this->handler,array(&$request,&$response));
        if ($status == 0) $status = 200;

        $peer = ""; $port = 0;
        socket_getpeername($this->sock, $peer, $port);
        console::writeLn("%s %s:%d %d %s", $request->getMethod(), $peer, $port, $status, $request->getUri());

        $response->writeHeaders($this->sock,200);
        $response->writeContent($this->sock);
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
    private $status = array(
        200 => 'Content Follows'
    );
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
    function write($data) {
        $this->data .= $data;
        $this->setHeader('content-length', strlen($this->data));
    }
    function writeHeaders($sock,$status='200') {
        $headers = array();
        $statusmsg = $this->status[$status];
        $headers[] = "HTTP/1.0 ".$status." ".$statusmsg;
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
        $bw = socket_write($sock,$bd);
        if ($bw != strlen($bd)) {
            throw new BaseException("Couldn't write buffer");
        }
    }
}

interface IHttpRequestHandler {
    function handleRequest(HttpRequest $request, HttpResponse $reponse);
}

abstract class HttpRequestHandler implements IHttpRequestHandler {
}


