<?php


    interface ILdwpWorker {
        function action($action);
    }

    abstract class LdwpWorker implements ILdwpWorker {
        protected $data;
        protected $job;
        function __construct($id) {
            $this->job = new LdwpJob($id);
        }
    }

?>
