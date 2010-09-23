<?php

    class HtPassword {
        private $_filename = null;
        private $_entries = array();
        public function __construct($filename = null) {
            if ($filename) $this->load($filename);
        }

        public function load($filename) {
            $this->_filename = $filename;
        }
        public function save($filename = null) {
            if ((!$filename) && ($this->_filename)) {
                $filename = $this->_filename;
            }
            if ($filename) {
                // save to $filename
                printf("Saving\n");
            } else {
                throw new FileNotFoundException("No file specified for saving");
            }
        }
        public function encrypt($password) {
            if (IS_LINUX) {
                $pwd = crypt($password, base64_encode($password));
                return $pwd;
            } else {
                throw new UnsupportedPlatformException("HtPassword::encrypt() possibly unsupported");
            }
        }
        public function addUser($user,$password) {
            $this->_entries[$user] = $this->encrypt($password);
        }
        public function testUser($user,$password) {
            if ($this->_entries[$user] == $this->encrypt($password)) {
                return true;
            }
        }
    }

    class HtGroup {

    }
