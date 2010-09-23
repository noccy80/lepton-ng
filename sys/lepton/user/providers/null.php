<?php

    /**
     * @class NullAuthentication
     * @brief Null authentication provider
     * 
     * Grants access to any account based on the user id. Be *VERY* careful
     * when using this one. It should never be used in a production system.
     * You have been warned.
     */
    class NullAuthentication extends AuthenticationProvider {

        private $uid = null;

        function __construct($id) {
            $this->uid = $id;
        }

        function isTokenValid() {
            return true;
        }

        function login() {
            $this->setUser($this->uid);
        }

        function logout() {
            $this->clearUser();
        }

    }

?>
