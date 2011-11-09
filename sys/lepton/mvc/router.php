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
    static $_staticroutes = array();

    /**
     * @brief Hooks a specific request URI
     *
     * @param $uripat The pattern to hook
     * @param $hook The hook which is to accept the diverted request
     */
    static function hookRequestUri($uripat,$hook) {
        Router::$_staticroutes[] = array(
            'match' => $uripat,
            'hook' => $hook
        );
    }

    /**
     * Constructor
     *
     * @param string $uri The URI to route
     * @param string $domain The domain to route
     * @param bool $secure True if connection made via SSL
     */
    public function __construct($buri = '/') {

        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        } else {
            $uri = $buri;
        }
        if (isset($_SERVER['HTTP_HOST'])) {
            $domain = strtolower($_SERVER['HTTP_HOST']);
        } else {
            if (isset($_SERVER['SERVER_NAME'])) {
                $domain = strtolower($_SERVER['SERVER_NAME']);
            } else {
                $domain = 'localhost';
            }
         }
        $secure = true;
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
        
        // Quick fix for first index being '/index_php' when invoked via
        // apache - hopefully sorts bug with php oauth.
        if (arr::hasKey($_GET,'/index_php')) {
            array_shift($_GET); array_shift($_GET);
        }

        // Assign the URI and start parsing
        $this->_uri = $uri;
        $this->_domain = $domain;
        $this->_secure = request::isSecure();
        foreach(explode('/',$this->_uri) as $segment) {
            if ($segment != '') $this->_urisegments[] = $segment;
        }
    }

    /**
     * @brief Main router entry point
     *
     * Override this in your router to get full control over the way the
     * request is being fed to the router class.
     *
     * @return Mixed The result from the routerequest call
     */
    public function route() {

        Console::debugEx(LOG_VERBOSE,__CLASS__,'Looking for event handlers before routing');
        // Invoke events first to see if anything is registered
        if (event::invoke(MvcEvent::EVENT_BEFORE_ROUTING,array(
            'uri' => $this->_uri,
            'segments' => $this->_urisegments,
            'domain' => $this->_domain,
            'secure' => $this->_secure
        )) == true) return 0;

        Console::debugEx(LOG_VERBOSE,__CLASS__,'Examining static routes');
        // Determine if this is a hooked uri
        foreach(Router::$_staticroutes as $sr) {
            if (@preg_match('/'.$sr['match'].'/', $this->_uri, $ret)) {
                call_user_func_array($sr['hook'],array_slice($ret,1));
                return 0;
            }
        }

        Console::debugEx(LOG_VERBOSE,__CLASS__,'Invoking the router');

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

    /**
     * @brief Update the application base path.
     *
     * Set to null to reset it to the default value.
     *
     * @param String $base The new base path (or null)
     */
    protected function setBase($base=null) {
        // config::set(Controller::KEY_CONTROLLER_BASE, $base);
        base::appPath($base);
    }

    /**
     * @brief Return the invoked URI in full.
     *
     * This will not include query string arguments or domain components etc.
     *
     * @return String The URI
     */
    protected function getURI() {
        return $this->_uri;
    }

    /**
     * @brief Return the number of segments in the URI
     *
     * @return Integer The segment count
     */
    protected function getSegmentCount() {
        return count($this->_urisegments);
    }

    /**
     * @brief Return a single URI segment by index
     *
     * @param Integer $index The segment to return
     * @return String The segment, null if not set
     */
    protected function getSegment($index) {
        if ($index < count($this->_urisegments))
            return $this->_urisegments[$index];
        return null;
    }

    /**
     * Return the topmost sgment from the query and remove it from the segment
     * stack.
     *
     * @return Mixed The segment, null if not set
     */
    protected function popSegment() {
        if (count($this->_urisegments) > 0) {
            $seg = $this->_urisegments[0];
            $this->_urisegments = array_slice($this->_urisegments,1);
            return $seg;
        } else {
            return null;
        }
    }

    /**
     * @brief Get a slice of the URI
     *
     * @param integer $start The start segment
     * @param integer $length The numer of segments to return or null for all
     * @return array The requested segments
     *
     */
    protected function getSegmentSlice($start,$length = null) {
        return array_slice($this->_urisegments, $start, $length);
    }

    /**
     * @brief Return the domain being requested
     *
     * @return string The domain name of the request
     */
    protected function getDomain() {
        return $this->_domain;
    }

    /**
     * @brief Return the components of the domain in reverse order
     *
     * The domain "foo.test.com" would return an array consisting of "com",
     * "test" and "foo".
     *
     * @param $normal bool Return in normal order (not reverse)
     * @return array The domain components in reverse order
     */
    protected function getDomainComponents($normal=false) {
        $comp = explode('.',strtolower($this->_domain));
        if (!$normal) return array_reverse($comp);
        return $comp;
    }

    /**
     * @todo Remove
     *
     *
     */
    function getFullDomain() {

    }

    /**
     * @todo Remove
     *
     *
     */
    function getFullUri() {

    }

    /**
     * @brief Check if the connection is performed over HTTPS
     *
     * @return Boolean True if the connection is encrypted and secured
     */
    protected function isSecure() {
        return $this->_secure;
    }

    /**
     * @brief Check if a segment exists in the request
     *
     * @param $index The index of the segment to check for
     * @return Boolean True if the segment exists
     */
    protected function hasSegment($index) {
        return ($index < count($this->_urisegments));
    }

}

abstract class MvcEvent {
    const EVENT_BEFORE_ROUTING = 'lepton.mvc.routing.pre';
    const EVENT_AFTER_ROUTING = 'lepton.mvc.routing.post';
}
