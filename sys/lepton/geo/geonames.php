<?php

abstract class GeoNames {

}

abstract class GeoCountry {

    function getInfoFromCountryCode($cc) {

    }

    function getInfoFromCountryName($cn) {

    }

}

using('ldwp.action');

/**
 * Lepton Worker for installing of geo country database
 */
class GeoCountryAction extends LdwpAction {

    const PSTATE_INITIALIZE = NULL;
    const PSTATE_DOWNLOAD = 1;
    const PSTATE_IMPORT = 2;
    const PSTATE_FINALIZE = 3;

    /**
     * Process will be called continuously until the worker signalize its completion
     * by setting $worker->complete = true;
     */
    function process(WorkerState $worker, ActionState $action) {
        // Process based on the actions process state. It starts empty as NULL,
        // which prepares the actual process.
        switch($action->pstate) {
        case self::PSTATE_INITIALIZE:
            // Start processing here, check tables etc,
            // Prepare the next state for operation
            $action->pstate = self::PSTATE_DOWNLOAD;
            break;
        case self::PSTATE_DOWNLOAD:
            // Prepare the next state for operation
            $action->pstate = self::PSTATE_IMPORT;
            break;
        case self::PSTATE_IMPORT:
            // Prepare the next state for operation
            $action->pstate = self::PSTATE_FINALIZE;
            break;
        case self::PSTATE_FINALIZE:
            // Flag the job as completed
            $worker->complete = true;
            break;
        }
        $this->yield();
    }
}
