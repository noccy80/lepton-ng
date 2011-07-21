<?php

/**
 * @class Cookies
 * 
 * Cookie management class that mades use of a jar for storing the cookies that
 * can not be saved at the requested time.
 * 
 * 
 * 
 */

class Cookies {
    
    const COOKIE_SECURE = 0x01;
    const COOKIE_HTTPONLY = 0x02;
    
    private static $cookies = array();
    private static $jar = array();
    
    static function initialize() {
        self::$cookies = $_COOKIE;
        if (class_exists('session')) {
            // Grab the cookie jar and set cookies as needed
            self::$jar = (array)session::get('__cookiejar');
            // Add the cookies from the jar to the cookies collection so
            // we can access them.
            foreach(self::$jar as $cookie) {
                self::$cookies[$cookie[0]] = $cookie[1];
            }
            if (!headers_sent()) {
                foreach(self::$jar as $cookie) {
                    call_user_func_array('setcookie', $cookie);
                    session::clr('__cookiejar');
                }
                // Then empty the jar
                self::$jar = array();
                session::clr('__cookiejar');
            } else {
                throw new BaseException("Cookie jar for delayed cookies loaded but output already started");
            }
        }
    }
    
    static function has($name) {
        return (arr::hasKey(self::$cookies,$name));
    }
    
    static function set($name, $value, $expire = 0, $flags = 0) {
        $secure = ($flags & self::COOKIE_SECURE);
        $httponly = ($flags & self::COOKIE_HTTPONLY);
        if (headers_sent()) {
            // If headers already sent, put them into the cookie jar
            self::$jar[] = array($name,$value,$expire,null,null,$secure,$httponly);
            session::set('__cookiejar',self::$jar);
        } else {
            // Otherwise set the cookie
            setcookie($name, $value, $expire, null, null, $secure, $httponly);
        }
    }
    
    static function get($name, $default=null) {
        if (arr::hasKey(self::$cookies,$name)) {
            return self::$cookies[$name];
        } else {
            return $default;
        }
    }
    
    static function clr($name) {
        if (headers_sent()) {
            // Put the deletion cookie in the jar if the headers have been sent
            self::$jar[] = array($name,'',time()-3600);
            session::set('__cookiejar',self::$jar);
        } else {
            // Otherwise clear the cookie immediately
            setcookie($name, '', time() - 3600);
        }
    }
    
}

Cookies::initialize();