<?php

using('lunit.*');

/**
 * @description IPC (InterProcess Communication)
 * @requirefunction msg_queue_get
 */
class LeptonIpcTests extends LunitCase {

	function __construct() {
		using('lepton.system.ipc');
	}

	/**
	 * @description MessageQueue: Create queue
	 */
	function createqueue() {
		$this->qfn = $this->getTempFile();
		$this->queue = new MessageQueue($this->qfn);
		$this->assertNotNull($this->queue);
	}

	/**
	 * @description MessageQueue: Posting message to queue
	 * @repeat 50
	 */
	function queuepostmessage() {
		$this->queue->push(1,new MessageEnvelope('lepton.test',array('foo'=>'bar')));
	}

	/**
	 * @description MessageQueue: Reading message from queue
	 * @repeat 50
	 */
	function queuegetmessage() {
		$message = $this->queue->pop(1);
		$this->assertEquals($message->foo,'bar');
	}

	/**
	 * @description MessageQueue: Destroy queue
	 */
	function destroyqueue() {
		$this->queue->destroy();
	}

}

Lunit::register('LeptonIpcTests');

