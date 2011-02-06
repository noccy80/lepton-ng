<?php

class LdwpException extends Exception { }

    class Governor {

        /**
         *
         * @param $uuid The UUID of the worker or null for first available
         */
        static function getWorker($uuid) {
            // Look up the uuid and if it exists create an instance of it
            $worker = new LdwpWorker($uuid);
            return $worker;
        }

        static function registerWorker($uuid,$description) {
            // Initialize a clean worker state
            $db = new DatabaseConnection();
            $ws = new WorkerState();
            // We need to create an empty slot in the workers table
            $db->insertRow("INSERT INTO ldwpworkers (uuid,description,state) VALUES (%s,%s,%s)", $uuid, $description, serialize($ws));
            
        }

    }

///////////////////////////////////////////////////////////////////////////////

    class LdwpActionConst {
        const ACTION_START = 1;
    }

    /**
     * Job queue implementation for workers
     */
    class LdwpJobQueue {

        private $_queue = null;

        function length() {
            return count((array)$this->_queue);
        }

        function push(LdwpJob $job, $priority=false) {
            // Add the job to the end
        }

        function pop() {
            // Return the queue from the top
        }

        function remove(LdwpJob $job) {

        }

        function postpone(LdwpJob $job) {
            // Push job to end of queue
        }

    }

    class LdwpGovernor {

        static $registry = array();
        static $queue;

        function getAvailableJobs($maxnum=5) {
            return array(
                array(
                    'id' => 12459,
                    'worker' => 'ldwp.workers.helloworld:HelloworldWorker',
                    'state' => null
                )
            );
        }

        function getJob($id) {
            return array(
                'id' => 12459,
                'worker' => 'ldwp.workers.helloworld:HelloworldWorker',
                'state' => null
            );
        }

        function register($ns,$class) {
            LdwpGovernor::$registry[$ns] = $class;
        }

        function action($action,$jobid) {
        $meta = LdwpGovernor::getJob($jobid);
        Console::debug("Preparing to send action to job %d (%s)", $jobid, $meta['worker']);
        $cn = explode(':',$meta['worker']);
        ModuleManager::load($cn[0]);
        if (class_inherits($cn[1], "LdwpWorker")) {
            if ($cn[1]) {
                    $inst = new $cn[1]($jobid);
                        return $inst->action($action);
                } else {
                        return null;
                }
        } else {
            Console::warn("ERROR: Worker instantiation rejected due to %d not being a worker", $cn[1]);
        }
    }
}

?>
