<?php

using('lepton.net.url');
using('lepton.net.curl');

class HttpException extends Exception {
	const ERR_NOT_FOUND = 404;
	const ERR_SERVER_ERROR = 500;
	const ERR_BAD_REQUEST = 1;
}

class HttpRequest {

	private $url;
	private $args;
	private $ret = null;

	function __construct($url, $args=null) {
		$this->args = arr::apply(array(
			'returndom' => false,
			'useragent' => 'LeptonPHP/1.0 (+http://labs.noccy.com)'
		),(array)$args);
		$this->url = $url;

		if (function_exists('curl_init')) {
			$this->_curlDoRequest();
		} else {
			$this->_streamDoRequest();
		}
	}

	private function _streamDoRequest() {
		throw new Eception("Stream support not yet implemented.");
	}

	private function _curlDoRequest() {

		$options = $this->args;

		$ci = new CurlInstance($this->url);
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

	function responseText() {
		return $this->ret['content'];
	}

	function responseHtml() {
		$doc = new DOMDocument();
		$doc->loadHTML($this->ret['content']);

	}

}

class HttpDownload {

	private $url;
	private $args;
	private $target;
	private $ret = null;

    function __construct($url,$target,$args=null) {
		$this->args = arr::apply(array(
		    'method' => 'get',
			'useragent' => 'LeptonPHP/1.0 (+http://labs.noccy.com)'
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
