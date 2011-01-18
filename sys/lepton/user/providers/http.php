<?php

/**
 * Authentication using HTTP auth
 */
final class HttpAuthentication extends AuthenticationProvider {

    const AUTH_BASIC = 1;
    const AUTH_DIGEST = 2;

    private $type;
    private $realm;
    private $username;
    private $password;
    private $userid;

    public function __construct($realm='lepton', $type=HttpAuthentication::AUTH_BASIC) {
        // look for http auth
        $this->type = $type;
        $this->realm = $realm;
        $this->username = $_SERVER['PHP_AUTH_USER'];
        $this->password = $_SERVER['PHP_AUTH_PW'];
    }

    /**
    * @brief Check if the token used for authentication is valid
    *
    * @return boolean True on success, false otherwise.
    */
    public function isTokenValid() {
        // only basic auth so far
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            if ($this->auth_backend->validateCredentials($this->username, $this->password)) {
                console::debugex(LOG_DEBUG2, __CLASS__, "Matched token valid for %s", $this->username);
                return true;
            } else {
                console::debugex(LOG_DEBUG2, __CLASS__, "Matched token not valid for %s", $this->username);
                return false;
            }
        }
        header('WWW-Authenticate: Basic realm="'.$this->realm.'"');
        header('HTTP/1.0 401 Unauthorized');
        // Removed by miniman (ticket #55)
        // Lepton::raiseError('HTTP 401: Unauthorized', 'You are not authorized to view this page. Please log in and try again.');
        throw new AccessException('You are not authorized to view this page. Please log in and try again.', AccessException::ERR_NOT_AUTHORIZED);
        exit;

    }


    /**
    * @brief Authenticate the specified user.
    *
    * @return boolean True on success
    */
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
