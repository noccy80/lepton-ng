<?php

using('ldwp.job');

/**
 * Job Queue for LDWP
 *
 *
 */
class JobQueue {

	private $db = null; ///< @var The database connection handle

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$this->db = new DatabaseConnection();
	}

	/**
	 * Push a job onto the queue.
	 *
	 * @param LdwpJob $job The job to queue
	 */
	public function enqueue(LdwpJob $job) {
		$jobser = serialize($job);
		// Save to db
	}

	/**
	 * Returns true if the queue is empty
	 *
	 * @return Bool True if the queue is empty
	 */
	public function isEmpty() {
		// Return true if the queue is empty
	}

	/**
	 * Retrieve a specific job from the queue
	 *
	 * @param String $jobid The job to retrieve
	 * @return LdwpJob The job with the specified id
	 */
	public function getJob($jobid) {
		// Fetch the job with the specified id
		$job = new DownloadJob('foo','bar');
		// Assign the queue to the job and return it
		$job->setQueue($this);
		return $job;
	}

	/**
	 * Get the ID of the next scheduled job
	 *
	 * @return String The ID of the next scheduled job
	 */
	public function getNextJobId() {
		// Return the jobid of the topmost job that is queued with
		// the appropriate status.
	}

	/**
	 * Retrieve status on the entire queue
	 *
	 * @return Array The queue data
	 */
	function getQueue() {
		// Fetch the entire queue
	}

}
