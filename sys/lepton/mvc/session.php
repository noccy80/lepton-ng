<?php

    /**
     *
     */
    abstract class Session {

        const KEY_STRICT_SESSIONS = 'lepton.security.strictsessions';
        const KEY_VALIDATION = 'lepton.security.validationcookie';
        const KEY_SESSION_DOMAIN = 'lepton.mvc.session.domain';
        const KEY_SESSION_VALIDITY = 'lepton.mvc.session.validity';
        const KEY_BACKEND = 'lepton.mvc.session.backend';

        const FLASH_INITIAL = 2;
        const FLASH_EXPIRES = 1;
        const FLASH_EXPIRED = 0;

        static $id = null;
        static $flashvars = array();
        static $stickyvars = array();

        static function set($key,$value) {

            $_SESSION[$key] = $value;
            return new SessionKey($key);

        }

        static function get($key,$default=null) {

            if (isset($_SESSION[$key])) {
                return $_SESSION[$key];
            } else {
                return $default;
            }

        }

        static function has($key) {

            return isset($_SESSION[$key]);

        }

        static function clr($key) {

			$a = func_get_args();
			if (is_array($key)) {
				foreach($key as $k) session::clr($k);
			} elseif (count($a) > 1) {
				foreach($a as $k) session::clr($k);
			} else {
	            if (isset($_SESSION[$key])) {
	                unset($_SESSION[$key]);
	            }
	        }

        }

        static function setupSessionCookie() {
            $domain = (config::get(self::KEY_SESSION_DOMAIN));
            $validity = (config::get(self::KEY_SESSION_VALIDITY));
            if (!$domain) $domain = request::getDomain();
            if (!$validity) $validity = 3600;
            ini_set("session.cookie_domain", $domain);
            // session_set_cookie_params($validity, '/', $domain);
        }

        static function begin() {
            if (!headers_sent()) {
                // self::setupSessionCookie();
    	        session_start();
    	    }
            session::$id = session_id();
        }

        /**
         * @brief Validate the session.
         * Activated using the configuration key
         *   'lepton.security.strictsessions'. A session-bound validation
         *   cookie is matched against the information of the current
         *   request.
         *
         * Will stop execution if the details mismatch.
         */
        static function validate() {

            // Grab the validation cookie
            $vc = session::get(session::KEY_VALIDATION,null);
            if (!$vc) {
                $vc = array(
                    'ip' => request::getRemoteIp()
                );
                // 'ua' => request::getUserAgent()
                // session::refresh();
                session::set(session::KEY_VALIDATION,$vc);
            } else {
                if ($vc['ip'] != request::getRemoteIp()) {
                    session::abandon();
                    die("Session integrity compromised. Session abandoned.");
                }
            }

        }

        /**
         * @brief Refresh the session by regenerating a new ID
         */
        static function refresh() {

            session_regenerate_id();

        }

        /**
         * @brief Abandon the current session.
         */
        static function abandon() {

            session_destroy();

        }

		/**
		 * @brief Inspect the state of the request
		 */
		static function inspect() {
		    debug::inspect($_SESSION);
		}

    }

    class SessionKey {

        private $key;

        function __construct($key) {

            $this->key = $key;

        }

        function flash($flash=true) {

            if ($flash) {
                session::$flashvars[$this->key] = session::FLASH_INITIAL;
            } else {
                if (isset(session::$flashvars[$this->key])) {
                    unset(session::$flashvars[$this->key]);
                }
            }

        }

        function sticky($sticky=true) {

            session::$stickyvars[$this->key] = $flash;

        }

    }

    session::begin();
    // Strict session support to prevent hijacking
    if (config::get(session::KEY_STRICT_SESSIONS,true)==true) {
        session::validate();
    }
