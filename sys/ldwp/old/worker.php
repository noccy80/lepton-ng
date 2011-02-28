<?php

using('lepton.crypto.uuid');

/**
 * Worker handles jobs.
 */
class LdwpWorker {

    const KEY_UUID = 'ldwp.worker.uuid';

    private $workerstate = null;
    protected $uuid = null;

    public function __construct($uuid) {
        $this->uuid = $uuid;
        $db = new DatabaseConnection();
        $workerinfo = $db->getSingleRow("SELECT * FROM ldwpworkers WHERE uuid=%s", $this->uuid);
        if ($workerinfo) {
            $this->workerstate = unserialize($workerinfo['state']);
        } else {
            throw new LdwpException("Worker not found. Is it registered?");
        }
    }

    public function __destruct() {
        // Save state to the database
        $db = new DatabaseConnection();
        $workerdata = serialize($this->workerstate);
        $db->updateRow("UPDATE ldwpjobs SET state=%s WHERE uuid=%s", $workerdata, $this->uuid);
    }

    public function addJob(LdwpAction $job) {
        // Add the job to the database
        $jobuuid = uuid::v4();
        $workeruuid = $this->uuid;
        $actionstate = new ActionState();
        // Save job
    }

    public function invokeJob($jobuuid) {

        $db = new DatabaseConnection();
        $job = $db->getSingleRow("SELECT * FROM ldwpjobs WHERE uuid=%s", $jobuuid);
        if ($job) {
            // Worker state need to be retrieved from the database prior to
            // this, perhaps in the constructor?
            $actionstate = unserialize($job['state']);
            $action = new $job['actionclass'];
            $action->process($this->workerstate,$actionstate);
            // Save the data again
            $actiondata = serialize($actionstate);
            $db->updateRow("UPDATE ldwpjobs SET state=%s WHERE uuid=%s", $actiondata, $jobuuid);
        } else {
            throw new LdwpException("Failed to invoke job: No such job");
        }

    }

    public function getQueue() {
        // Return the job queue for this worker
    }

}

class LocalWorker extends LdwpWorker {

    public function __construct() {
        parent::__construct(self::getUuid());
    }

    public static function getWorker() {
        return new LdwpWorker(self::getUuid());
    }

    public function getUuid() {
        return config::get(LdwpWorker::KEY_UUID);
    }

}

if (!config::get(LdwpWorker::KEY_UUID)) {
    console::fatal("Local system does not have a worker uuid!");
    exit(1);
}