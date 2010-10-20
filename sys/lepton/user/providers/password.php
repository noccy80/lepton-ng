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
            if ($this->auth_backend->validateCredentials($this->username,$this->password)) {
                console::debugex(LOG_DEBUG2,__CLASS__,"Matched token valid for %s", $this->username);
                return true;
            } else {
                console::debugex(LOG_DEBUG2,__CLASS__,"Matched token not valid for %s", $this->username);
                return false;
            }
        }

        function login() {
            $this->userid = $this->auth_backend->getUserid();
            if ($this->userid) {
                $this->setUser($this->userid);
                // console::writeLn("Authenticated as user %d", $this->userid);
                return true;
            }
            throw new AuthenticationException("No user available to login()");
        }

    }


