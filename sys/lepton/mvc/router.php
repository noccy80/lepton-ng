<?php

	interface IRouter {
		function routeRequest();
	}

	/**
	 * Base class for a router. If you need to create your own router (f.ex.
	 * if your site uses an advanced URI scheme or the included stock routers
	 * don't do it for you) you should derive it from this class.
	 *
	 * You need to implement the routeRequest() method (from the IRouter
	 * interface) in order to handle requests.
	 *
	 * @author Christopher Vagnetoft <noccy@chillat.net>
	 * @since 0.2
	 */
	abstract class Router implements IRouter {

		private $_domain = null;
		private $_uri = null;
		private $_urisegments = array();
		private $_secure = false;

		/**
		 * Constructor
		 *
		 * @param string $uri The URI to route
		 * @param string $domain The domain to route
		 * @param bool $secure True if connection made via SSL
		 */
		public function __construct() {

			$uri = $_SERVER['REQUEST_URI'];
			$domain = strtolower($_SERVER['SERVER_NAME']);
			$secure = false;
			// Parse query string
			if (strpos($uri,'?')) {
				$base = substr($uri,0,strpos($uri,'?'));
				$rest = substr($uri,strpos($uri,'?') + 1);
				// Apache mod_rewrite workaround for existing folders - requests
				// are routed as /uri/?/uri if the folder exists.
				if (($base != '/') && (file_exists('.'.$base)) && ((substr($rest,0,strlen($base)-1).'/' == $base))) {
					// folder match, reroute the $rest.
					// TODO: Query string arguments should be passed on
					if (strpos($rest,'&')>0) {
						$params = substr($rest,strpos($rest,'&')+1);
					}
					response::redirect($base.'?'.$params);
				} else {
					// Parse the querystring
					$qsl = explode('&',$rest);
					foreach($qsl as $qsi) {
						if (preg_match('/^(.*)=(.*)$/', $qsi, $keys)) {
							$_GET[$keys[1]] = urldecode($keys[2]);
							$_REQUEST[$keys[1]] = urldecode($keys[2]);
						}
					}
					$uri = $base;
				}
			}

			// Assign the URI and start parsing
			$this->_uri = $uri;
			$this->_domain = $domain;
			$this->_secure = $secure;
			foreach(explode('/',$this->_uri) as $segment) {
				if ($segment != '') $this->_urisegments[] = $segment;
			}
		}

		/**
		 *
		 *
		 *
		 */
		public function route() {
			// Invoke the router
			return $this->routeRequest($this->_uri);
		}

		/**
		 * Chain your request to another router. If the segments are provided
		 * they will form the URI of the sub request. If not, the URI used
		 * to invoke the current router will be used.
		 *
		 * @param Router $router The router to chain
		 * @param array $segments The URI segments to pass on
		 * @return
		 */
		protected function chain($router, $segments=null) {
			if ($segments) {
				$uri = '/'.join('/',$segments);
			} else {
				$uri = $this->_uri;
			}
			$r = new $router($uri, $this->_domain, $this->_secure);
			$r->route();
		}

		protected function setBase($base=null) {
			config::set(Controller::KEY_CONTROLLER_BASE, $base);
		}

		protected function getURI() {
			return $this->_uri;
		}

		protected function getSegmentCount() {
			return count($this->_urisegments);
		}

		protected function getSegment($index) {
			return $this->_urisegments[$index];
		}

		protected function getSegmentSlice($start,$length = null) {
			return array_slice($this->_urisegments, $start, $length);
		}

		protected function getDomain() {
			return $this->_domain;
		}

		function getFullDomain() { 

		}
		function getFullUri() { 

		}

		protected function isSecure() {
			return $this->_secure;
		}

		protected function hasSegment($index) {
			return ($index < count($this->_urisegments));
		}

	}

?>
