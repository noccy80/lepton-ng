<?php

using('lepton.crypto.uuid');

interface ILdwpJob {
    function start();
}

/**
 * Job Implementation
 *
 *
 */
abstract class LdwpJob implements ILdwpJob {

    const STATE_NULL = null; ///< @var Newly created job, not queued yet
    const STATE_QUEUED = 0; ///< @var Job queued in DB
    const STATE_ERROR = 1; ///< @var Job has encountered an error condition
    const STATE_RUNNING = 2; ///< @var Job is running
    const STATE_SUSPENDED = 3; ///< @var Job has been suspended
    const STATE_COMPLETED = 4; ///< @var Job has finished

    protected $_state = null;
    protected $_message = null;
    protected $_progress = null;
    protected $_id = null;

    /**
     * Constructor, must be chained from the worker class using parent::__construct() or
     * things will fail.
     *
     */
    public function __construct() {
        $this->setState(self::STATE_QUEUED);
        $this->setProgress("Job queued");
        $this->_id = uuid::v4();
    }

    /**
     * Retrieve the Job ID
     *
     * @return String The Job ID (UUID)
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * Set the state of the job to one of the LdwpJob::STATE_* constants.
     *
     * @param Int $state The new state of the job
     */
    protected function setState($state) {
        $this->_state = $state;
    }

    /**
     * Retrieve the state of the job. The state can only be changed from within the class.
     *
     * @return Int The state of the job
     */
    public function getState() {
        return $this->_state;
    }

    /**
     * Update the progress information of the job. This allows for a plaintext string to
     * be displayed when the status is requested as well as the current and maximum
     * progress, f.ex. step 1 or 4 or 1 of 100%.
     *
     * @param String $message The message to set for the current progress
     * @param Int $current The current progress
     * @param Int $max The maximum progress
     */
    protected function setProgress($message,$current=null,$max=null) {
        $this->_message = $message;
        $this->_progress = array($current, $max);

    }

}
