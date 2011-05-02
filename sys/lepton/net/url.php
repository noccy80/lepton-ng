<?php

	class LegacyUrl {

		private $url;

		function __construct($url='') {
			$this->setURL($url);
		}

		function __toString() {
			return $this->getUrl();
		}

		function getUrl() {
			$ret = $this->url['scheme'].'://';
			$ret .= (($this->url['user']!='')?($this->url['user']).(($this->url['pass']!='')?':'.($this->url['pass']):'').'@':'');
			$ret .= $this->url['host'];
			$ret .= (($this->url['port']!='')?':'.$this->url['port']:'');
			$ret .= $this->url['path'];
			$ret .= (($this->url['query']!='')?('?'.$this->url['query']):'');
			$ret .= (($this->url['fragment']!='')?('#'.$this->url['fragment']):'');
			return $ret;
		}
		function setUrl($url) {
			$this->url = parse_url($url);
		}

		function getScheme() {
			return $this->url['scheme'];
		}
		function getHost() {
			return $this->url['host'];
		}
		function getPort() {
			return $this->url['port'];
		}
		function getUser() {
			return $this->url['user'];
		}
		function getPass() {
			return $this->url['pass'];
		}
		function getPath() {
			return $this->url['path'];
		}
		function getQuery() {
			return $this->url['query'];
		}
		function getFragment() {
			return $this->url['fragment'];
		}

		function setScheme($scheme) {
			$this->url['scheme'] = $scheme;
		}
		function setHost($host) {
			$this->url['host'] = $host;
		}
		function setPort($port) {
			$this->url['port'] = $port;
		}
		function setUser($user) {
			$this->url['user'] = $user;
		}
		function setPass($pass) {
			$this->url['pass'] = $pass;
		}
		function setPath($path) {
			if (($path != '') && (String::part(1,1,$path) != '/')) {
				$this->url['path'] = '/'.$path;
			} else {
				$this->url['path'] = $path;
			}
		}
		function setQuery($query) {
			$this->url['query'] = $query;
		}
		function setQueryArray($query) {
			$this->url['query'] = http_build_query($query);
		}
		function setFragment($fragment) {
			$this->url['fragment'] = $fragment;
		}

	}
