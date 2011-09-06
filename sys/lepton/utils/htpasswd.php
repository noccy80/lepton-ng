<?php

/**
 * @class HtPassword
 *
 * Manages encrypted HtPassword files that are used for authenticating against
 * resources hosted on web servers.
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @category Utils
 * @
 */
class HtPassword {

    private $_filename = null;
    private $_entries = array();

    /**
     *
     * @param <type> $filename
     */
    public function __construct($filename = null) {
        if ($filename) $this->load($filename);
    }

    /**
     *
     * @param <type> $filename
     */
    public function load($filename) {
        $this->_filename = $filename;
    }
    
    /**
     * @par
     * @param <type> $filename
     */
    public function save($filename = null) {
        if ((!$filename) && ($this->_filename)) {
            $filename = $this->_filename;
        }
        if ($filename) {
            // save to $filename
            printf("Saving\n");
        } else {
            throw new FileNotFoundException("No file specified for saving", null);
        }
    }

    /**
     *
     * @param <type> $password
     * @return <type>
     */
    public function encrypt($password) {
        if (IS_LINUX) {
            $pwd = crypt($password, base64_encode($password));
            return $pwd;
        } else {
            throw new UnsupportedPlatformException("HtPassword::encrypt() possibly unsupported");
        }
    }

    /**
     *
     * @param <type> $user
     * @param <type> $password
     */
    public function addUser($user,$password) {
        $this->_entries[$user] = $this->encrypt($password);
    }

    /**
     *
     * @param <type> $user
     * @param <type> $password
     * @return bool True if the provided password is correct
     */
    public function verifyUser($user,$password) {
        if ($this->_entries[$user] == $this->encrypt($password)) {
            return true;
        }
        return false;
    }

}

class HtGroup {

}
