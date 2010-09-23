<?php

    ModuleManager::load('lepton.ldwp.*');

    class HelloworldWorker extends LdwpWorker {

        function action($action) {
            if ($action == LdwpAction::ACTION_START) {
                Console::debugEx(LOG_LOG,__CLASS__,"Initializing...");
                sleep(2);
                Console::debugEx(LOG_LOG,__CLASS__,"Sorting files...");
                sleep(5);
                Console::debugEx(LOG_LOG,__CLASS__,"Taking a break");
                sleep(10);
                Console::debugEx(LOG_LOG,__CLASS__,"Done!");
            }
        }

    }

?>
