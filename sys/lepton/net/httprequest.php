<?php

using('lepton.net.url');
using('lepton.net.curl');
using('lepton.web.exception');

class HttpRequest {

    private $url;
    private $args;
    protected $ret = null;
    
    const KEY_USERAGENT = 'lepton.net.httprequest.useragent';

    function __construct($url, $args=null) {
        $this->args = arr::apply(array(
            'returndom' => false,
            'method' => 'get',
            'useragent' => config::get(self::KEY_USERAGENT,'LeptonPHP/1.0 (+http://labs.noccy.com)')
        ),(array)$args);
        $this->url = $url;

        logger::debug('HTTPRequest() query for "%s"', $url);
        if (function_exists('curl_init')) {
            $this->_curlDoRequest();
        } else {
            $this->_streamDoRequest();
        }
    }

    private function _streamDoRequest() {

        $options = $this->args;

        $ctxparam = array('http' => array(
            'method' => (strtolower($options['method']) == 'post')?'POST':'GET',
        ));
        if (arr::hasKey($options,'parameters')) {
            $ctxparam['http']['content'] = http_build_query($options['parameters']);
        }

        $ctx = stream_context_create($ctxparam);
        $cfh = fopen($url, 'r', false, $ctx);
        $buf = '';
        while (!feof($cfh)) {
            $buf.= fread($cfh,8192);
        }
        fclose($cfh);

        $this->ret = array(
            'content' => $buf
        );

    }

    private function _curlDoRequest() {

        $options = $this->args;

        $ci = new CurlInstance($this->url);
        $ci->setOption(CURLOPT_HEADER, false);
        $ci->setOption(CURLOPT_RETURNTRANSFER, true);
        $ci->setOption(CURLOPT_FOLLOWLOCATION, true);
        $ci->setOption(CURLOPT_AUTOREFERER, true);
        $ci->setOption(CURLOPT_MAXREDIRS, 5);
        $ci->setHeader('Accept', 'text/xml,application,xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5'); /* */
        $ci->setHeader('Cache-Control', 'max-age=0');
        $ci->setUserAgent($options['useragent']);
        if (isset($options['username'])) {
            $ci->setOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            $ci->setOption(CURLOPT_USERPWD, $options['username'] . ':' . $options['password']);
        }
        if (isset($options['verifyssl'])) {
            $state = ($options['verifyssl'] == true);
            $ci->setOption(CURLOPT_SSL_VERIFYPEER, $state); 
            $ci->setOption(CURLOPT_SSL_VERIFYHOST, $state); 
        }
        if (isset($options['referer'])) {
            $ci->setReferer($co->get('referer'));
        }
        if (isset($options['parameters'])) {
            $params = $options['parameters'];
        } else {
            $params = null;
        }
        $ci->setParams($params);

        // If we are posting, set the appropriate data
        if (strtolower($options['method']) == 'post') {
            if (isset($options['content-type'])) {
                $ci->setHeader('content-type', $options['content-type']);
            }
                        if (isset($options['body'])) {
                $ci->setParams($options['body']);
                        }
            $ret = $ci->exec(CurlInstance::METHOD_POST);
        } elseif (strtolower($options['method'] == 'head')) {
            $ret = $ci->exec(CurlInstance::METHOD_HEAD);
        } else {
            $ret = $ci->exec(CurlInstance::METHOD_GET);
        }
        if ($ret['code'] == 200) {
            $this->ret = $ret;
        } else {
            switch($ret['ce']) {
                case 60:
                    throw new HttpException("SSL Certificate Problem, set verifyssl=>false in options");
                    break;	
                default:
                    throw new HttpException("Error ".$ret['code'].': '.$ret['ce']);
                    break;
            }
        }
    }

    function __toString() {
        return $this->getResponse();
    }

    function getResponse() {
        return $this->ret['content'];
    }

    function responseText() {
        // __deprecated('HttpRequest->responseText', 'HttpRequest->getResponse');
        return $this->ret['content'];
    }

    function responseHtml() {
        $doc = new DOMDocument();
        $doc->loadHTML($this->ret['content']);

    }

    function headers() {
        return $this->ret['headers'];
    }

    function status() {
        return $this->ret['code'];
    }

    function error() {
        return $this->ret['ce'];
    }

}

class HttpDownload extends HttpRequest {

    private $url;
    private $args;
    private $target;

    function __construct($url,$target,$args=null) {
        $this->args = arr::apply(array(
            'method' => 'get',
            'useragent' => config::get(self::KEY_USERAGENT,'LeptonPHP/1.0 (+http://labs.noccy.com)')
        ),(array)$args);
        $this->url = $url;
        $this->target = $target;

        if (function_exists('curl_init')) {
            $this->_curlDoRequest();
        } else {
            $this->_streamDoRequest();
        }
   
    }

    
    private function _curlDoRequest() {

        $options = $this->args;

        $ci = new CurlInstance($this->url);

        if (isset($options['onprogress'])) $ci->setProgressCallback($options['onprogress']);

        $ci->setOption(CURLOPT_BINARYTRANSFER,true);
        $ci->setTarget($this->target);
        $ci->setOption(CURLOPT_HEADER, false);
        $ci->setOption(CURLOPT_RETURNTRANSFER, true);
        $ci->setOption(CURLOPT_FOLLOWLOCATION, true);
        $ci->setOption(CURLOPT_AUTOREFERER, true);
        $ci->setOption(CURLOPT_MAXREDIRS, 5);
        $ci->setHeader('Accept', 'text/xml,application,xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5');
        $ci->setHeader('Cache-Control', 'max-age=0');
        $ci->setUserAgent($options['useragent']);
        if (isset($options['username'])) {
            $ci->setOption(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            $ci->setOption(CURLOPT_USERPWD, $options['username'] . ':' . $options['password']);
        }
        if (isset($options['verifyssl'])) {
            $state = ($options['verifyssl'] == true);
            $ci->setOption(CURLOPT_SSL_VERIFYPEER, $state); 
            $ci->setOption(CURLOPT_SSL_VERIFYHOST, $state); 
        }
        if (isset($options['referer'])) {
            $ci->setReferer($co->get('referer'));
        }
        if (isset($options['parameters'])) {
            $params = $options['parameters'];
        } else {
            $params = null;
        }
        $ci->setParams($params);

        // If we are posting, set the appropriate data
        if (strtolower($options['method']) == 'post') {
            if (isset($options['content-type'])) {
                $ci->setHeader('content-type', $options['content-type']);
            }
            $ret = $ci->exec(CurlInstance::METHOD_POST);
        } else {
            $ret = $ci->exec(CurlInstance::METHOD_GET);
        }
        if ($ret['code'] == 200) {
            $this->ret = $ret;
        } else {
            switch($ret['ce']) {
                case 60:
                    throw new HttpException("SSL Certificate Problem, set verifyssl=>false in options");
                    break;	
                default:
                    throw new HttpException("Error ".$ret['code'].': '.$ret['ce']);
                    break;
            }
        }
    }

}
