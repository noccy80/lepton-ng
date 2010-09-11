<?php

	class PasswordAuthentication extends AuthenticationProvider {

		private $username;
		private $password;
		private $userid;

		public function __construct($username, $password) {
			$this->username = $username;
			$this->password = $password;
		}
		
		public function isTokenValid() {
			if ($this->auth_backend->testUserPassword($this->username,$this->password)) {
				return true;
			} else {
				return false;
			}
		}

		function login() {
			if ($this->userid) {
				$this->setUser($this->userid);
			}
			throw new AuthenticationException("No user available to login()");
		}
		
		function logout() {
			$this->clearUser();
		}

	}


