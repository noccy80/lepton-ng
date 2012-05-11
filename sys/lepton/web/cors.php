<?php

/**
 * @class cors
 * @brief Cross Origin Resource Sharing (CORS) Helper
 * 
 * 
 * @author Christopher Vagnetoft <noccylabs.info>
 * @license GPL v3
 */
class cors {

    /// @const Headers used by prototype
    const AHL_PROTOTYPE = 'X-Prototype-Version, X-Requested-With';

    /// @var Access-Control headers to send
    static $acheader = array();
    
    /**
     * @brief Allow origin for requests
     * 
     * @param string $origin The origin (including protocol) or '*'
     */
    static function allowOrigin($origin) {
        self::$acheader['Access-Control-Allow-Origin'] = $origin;
    }
    
    /**
     * @brief Set whether credentials are accepted by the api.
     *
     * @param bool $state True if credentials are accepted
     */
    static function allowCredentials($state) {
        self::$acheader['Access-Control-Allow-Credentials'] = ($state)?'true':'false';
    }
    
    /**
     * @brief Set the list of allowed methods.
     *
     * @param string $methods Comma-separated list of methods supported (default: GET, POST)
     */
    static function allowMethods($methods) {
        self::$acheader['Access-Control-Allow-Methods'] = $methods;
    }
    
    /**
     * @brief Return the request origin
     * 
     * Note that the origin can be an empty string, which might be useful in
     * some cases (for example if the source is a data url).
     * 
     * @return string The request origin
     */
    static function getOrigin() {
        return request::getHeader('Origin');
    }
    
    /**
     * @brief Set the headers to allow
     * 
     * @param stromg $headers Comma-separated list of headers
     */
    static function allowHeaders($headers) {
        self::$acheader['Access-Control-Allow-Headers'] = $headers;
    }

    /**
     * @brief Whitelist headers that the browser may access
     * 
     * @param stromg $headers Comma-separated list of headers
     */
    static function exposeHeaders($headers) {
        self::$acheader['Access-Control-Expose-Headers'] = $headers;
    }

    /**
     * @brief Check the data and set the headers.
     * 
     * @return boolean If true, the request was a preflight request and no data should be returned.
     */
    static function check() {
        
        // Apply some defaults
        $hdr = arr::defaults(self::$acheader,array(
            'Access-Control-Max-Age' => (3 * 60 * 60),
            'Access-Control-Allow-Methods' => 'GET, POST'
        ));
        response::setHeaders($hdr);
    
        // Check if the request is a pre-flight OPTIONS request
        if (request::getRequestMethod() == 'OPTIONS') {
            // If so, make sure to flush the output buffer to ensure the headers
            // are sent.
            response::flush();
            return true;
        }
        
        return false;
        
    }
    
}