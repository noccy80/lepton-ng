<?php

    ModuleManager::load('lepton.crypto.guid');

    /**
     * Defines a worker job
     */
    class LdwpJob {

        private $_guid;
        private $_state = null;

        public $data;

        function __construct($id=null) {
            $this->_guid =
            $this->data = array(); // new LdwpJobData();
        }

        function setState($state) {
            $this->_state = $state;
        }

        function getState() {
            return $this->_state;
        }

    }

?>
