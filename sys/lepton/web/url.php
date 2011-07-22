<?php

/**
 * @brief URL wrapper
 *
 * Parses and builds URLs and facilitates a way of manipulating the different
 * components of them.
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 */
class url {

	private $scheme = null;
	private $host = null;
	private $port = null;
	private $user = null;
	private $pass = null;
	private $path = null;
	private $query = array();
	private $fragment = null;

	/**
	 * @brief Constructor
	 *
	 * @param
	 */
	function __construct($url = null) {

		// Parse the URL if we got any
		if ($url) {
			$c = parse_url($url);
			if (arr::hasKey($c,'scheme')) $this->scheme = $c['scheme'];
			if (arr::hasKey($c,'host')) $this->host = $c['host'];
			if (arr::hasKey($c,'port')) $this->port = $c['port'];
			if (arr::hasKey($c,'user')) $this->user = $c['user'];
			if (arr::hasKey($c,'pass')) $this->pass = $c['pass'];
			if (arr::hasKey($c,'path')) $this->path = $c['path'];
			if (arr::hasKey($c,'query')) $this->query = $this->qs_parse($c['query']);
			if (arr::hasKey($c,'fragment')) $this->fragment = $c['fragment'];
		}

	}

	/**
	 * @brief Parse query string and return array
	 *
	 * @param
	 * @return
	 */
	private function qs_parse($query) {

		$queries = array();
		parse_str($query,$queries);
		return $queries;

	}

	/**
	 * @brief Property getter
	 *
	 * @param
	 * @return
	 */
	public function __get($key) {
		switch($key) {
		case 'scheme':
			return $this->scheme;
			break;
		case 'host':
			return $this->host;
			break;
		case 'port':
			return $this->port;
			break;
		case 'user':
			return $this->user;
			break;
		case 'pass':
			return $this->pass;
			break;
		case 'path':
			return $this->path;
			break;
		case 'query':
			return http_build_query($this->query);
			break;
		case 'fragment':
			return $this->fragment;
			break;
		default:
			throw new BadPropertyException("No property ".$key." on URL");
		}
	}

	/**
	 * @brief Property setter
	 *
	 * @param
	 * @param
	 */
	public function __set($key,$value) {
		switch($key) {
		case 'scheme':
			$this->scheme = $value;
			break;
		case 'host':
			$this->host = $value;
			break;
		case 'port':
			$this->port = $value;
			break;
		case 'user':
			$this->user = $value;
			break;
		case 'pass':
			$this->pass = $value;
			break;
		case 'path':
			$this->path = $value;
			break;
		case 'query':
			$this->query = $this->qs_parse($value);
			break;
		case 'fragment':
			$this->fragment = $value;
			break;
		default:
			throw new BadPropertyException("No property ".$key." on URL");
		}
	}

	/**
	 * @brief Assign a component of the query string
	 *
	 * @param
	 * @param
	 * @return
	 */
	public function setParameter($key,$value) {
		$this->query[$key] = $value;
	}

	/**
	 * @brief Retrieve a component of the query string
	 *
	 * @param
	 * @return
	 */
	public function getParameter($key) {
		if (arr::hasKey($this->query,$key)) return $this->query[$key];
		return null;
	}

	/**
	 * @brief Cast the URL back into a string (magic method)
	 *
	 * @return
	 */
	public function __toString() {
		return $this->toString();
	}

	/**
	 * @brief Cast the URL back into a string
	 *
	 * @return
	 */
	public function toString() {
		if ($this->scheme != null) { $scheme = $this->scheme . '://'; }
			else { $scheme = ''; }
		if ($this->host != null) {
			if ($this->port != null) {
				$host = $this->host.':'.$this->port;
			} else {
				$host = $this->host;
			}
		}
		if ($this->user != null) {
			if ($ths->password != null) {
				$auth = $this->user.':'.$this->pass.'@';
			} else {
				$auth = $this->user.'@';
			}
		} else {
			$auth = '';
		}
		if ($this->path != null) {
			$path = $this->path;
		} else {
			$path = '';
		}
		if (count($this->query)>0) {
			$query = '?'.http_build_query($this->query);
		} else {
			$query = '';
		}
		if ($this->fragment != null) {
			$fragment = '#'.$this->fragment;
		} else {
			$fragment = '';
		}
		$url = $scheme.$auth.$host.$path.$query.$fragment;
		return $url;
	}
    
    /**
     * @brief Helper function to return a URL object from the current URL
     *
     * Will return an empty URL if the request object is not present.
     * 
     * @static
     * @return Url The URL object
     */
    static function createFromCurrent() {
        if (class_exists('request')) {
            return url(request::getURL());
        } else {
            return url();
        }
    }

}

function url($url) { return new url($url); }
