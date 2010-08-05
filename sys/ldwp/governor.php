<?php

    class LdwpAction {
        const ACTION_START = 1;
    }

	class LdwpGovernor {

        static $registry = array();

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
            if ($cn[1]) {
                $inst = new $cn[1]($jobid);
                return $inst->action($action);
            } else {
                return null;
            }
        }

	}

?>
