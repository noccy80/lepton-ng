<?php

using('ldwp.job');

class JobQueue {

	private $db = null;

	public function __construct() {
		// $this->db = new DatabaseConnection();
	}

	public function enqueue(LdwpJob $job) {
		$jobser = serialize($job);
		// Save to db
	}

	public function isEmpty() {
		// Return true if the queue is empty
	}

	public function getJob($jobid) {
		// Fetch the job with the specified id
		return new DownloadJob('foo','bar');
	}

	public function getNextJobId() {
		// Return the jobid of the topmost job that is queued
	}

	function getQueue() {
		// Fetch the entire queue
	}

}
