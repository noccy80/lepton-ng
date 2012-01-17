<?php

    class LegacyUrl {

        private $url;

        public function __construct($url='') {
            $this->setURL($url);
        }

        public function __toString() {
            return $this->getUrl();
        }

        public function getUrl() {
            $ret = $this->url['scheme'].'://';
            $ret .= (($this->url['user']!='')?($this->url['user']).(($this->url['pass']!='')?':'.($this->url['pass']):'').'@':'');
            $ret .= $this->url['host'];
            $ret .= (($this->url['port']!='')?':'.$this->url['port']:'');
            $ret .= $this->url['path'];
            $ret .= (($this->url['query']!='')?('?'.$this->url['query']):'');
            $ret .= (($this->url['fragment']!='')?('#'.$this->url['fragment']):'');
            return $ret;
        }
        public function setUrl($url) {
            $this->url = parse_url($url);
        }

        public function getScheme() {
            return $this->url['scheme'];
        }
        public function getHost() {
            return $this->url['host'];
        }
        public function getPort() {
            return $this->url['port'];
        }
        public function getUser() {
            return $this->url['user'];
        }
        public function getPass() {
            return $this->url['pass'];
        }
        public function getPath() {
            return $this->url['path'];
        }
        public function getQuery() {
            return $this->url['query'];
        }
        public function getFragment() {
            return $this->url['fragment'];
        }

        public function setScheme($scheme) {
            $this->url['scheme'] = $scheme;
        }
        public function setHost($host) {
            $this->url['host'] = $host;
        }
        public function setPort($port) {
            $this->url['port'] = $port;
        }
        public function setUser($user) {
            $this->url['user'] = $user;
        }
        public function setPass($pass) {
            $this->url['pass'] = $pass;
        }
        public function setPath($path) {
            if (($path != '') && (String::part(1,1,$path) != '/')) {
                $this->url['path'] = '/'.$path;
            } else {
                $this->url['path'] = $path;
            }
        }
        public function setQuery($query) {
            $this->url['query'] = $query;
        }
        public function setQueryArray($query) {
            $this->url['query'] = http_build_query($query);
        }
        public function setFragment($fragment) {
            $this->url['fragment'] = $fragment;
        }
        
        public function like($expression) {
            return preg_match($expression,(string)$this);
        }

    }
