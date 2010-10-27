<?php

    if (!headers_sent()) {
    	session_start();
    }

    abstract class Session {

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

        static function clr($key) {

            if (isset($_SESSION[$key])) {

                unset($_SESSION[$key]);

            }

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

    Session::$id = session_id();
