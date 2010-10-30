<?php

    if (!headers_sent()) {
    	session_start();
    }

    abstract class Session {

        const KEY_STRICT_SESSIONS = 'lepton.security.strictsessions';
        const KEY_VALIDATION = 'lepton.security.validationcookie';

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

        static function validate() {

            // Grab the validation cookie
            $vc = session::get(session::KEY_VALIDATION,null);
            if (!$vc) {
                $vc = array(
                    'ip' => request::getRemoteIp()
                );
                session::set(session::KEY_VALIDATION,$vc);
            } else {
                if ($vc['ip'] != request::getRemoteIp()) {
                    die("Session integrity compromised.");
                }
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

    session::$id = session_id();
    if (config::get(session::KEY_STRICT_SESSIONS,true)==true) {
        session::validate();
    }
