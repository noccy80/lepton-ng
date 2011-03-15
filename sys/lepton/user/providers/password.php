<?php

__fileinfo("Password Authentication Provider", array(
    'version' => '1.0'
));

using('lepton.user.authentication');

/**
 * @brief Password Authentication provider
 *
 */
class PasswordAuthentication extends AuthenticationProvider {

    private $username;
    private $password;
    private $userid;

    /**
     * @brief Constructor for Password Authentication
     *
     * @param string $username The username for which to validate the token
     * @param string $password The user's password.
     */
    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @brief Check if the token used for authentication is valid
     *
     * @return boolean True on success, false otherwise.
     */
    public function isTokenValid() {
        if ($this->auth_backend->validateCredentials($this->username, $this->password)) {
            console::debugex(LOG_DEBUG2, __CLASS__, "Matched token valid for %s", $this->username);
            return true;
        } else {
            console::debugex(LOG_DEBUG2, __CLASS__, "Matched token not valid for %s", $this->username);
            return false;
        }
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

