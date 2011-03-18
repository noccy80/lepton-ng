<?php

require('sys/base.php');
using('lepton.system.threading');

interface ILdwpTransport {
	function connect($uri);
	function getQueue();
	function getJob($jobid);
}

abstract class LdwpTransport implements ILdwpTransport {
	static function factory($uri) {
		//
	}
	function connect($uri) { }
	function getQueue() { }
	function getJob($jobid) { }
}

class LdwpLocalTransport extends LdwpTransport { }
class LdwpHttpTransport extends LdwpTransport { }
class LdwpSshTransport extends LdwpTransport { }

class LdwpNode extends Runnable {

	static function start($transport,$nodename=null,$background=true) {

		$node = new LdwpNode();
		if ($background) {
			$t = new Thread($node);
			$pid = $t->start();
			console::writeLn("Forked with pid %d", $pid);
		} else {
			$node->threadmain();
		}

	}

	public function threadmain() {
		echo "I am running the thread now!\n";
		usleep(500000);
		echo "Almost done running\n";
		usleep(500000);
		echo "And I am done!\n";
	}

}

LdwpNode::start(new LdwpLocalTransport(),'a1',true);

