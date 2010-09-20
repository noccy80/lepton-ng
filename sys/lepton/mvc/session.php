<?php

	if (!headers_sent()) session_start();

	abstract class Session {

		static $id;

		static function set($key,$value) {

			$_SESSION[$key] = $value;
		
		}
	
		static function get($key,$default=null) {
		
			if (isset($_SESSION[$key])) {
				return $_SESSION[$key];
			} else {
				return $default;
			}
		}
	
	}
	
	Session::$id = session_id();
