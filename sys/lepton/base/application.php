<?php

// Console applications or services shouldn't time out
set_time_limit(0);

interface IConsoleApplication {
	function usage();
	function main($argc,$argv);
}

abstract class ConsoleApplication extends Application implements IConsoleApplication {
	protected $options;
	protected $arguments;
	function run() {
		global $argc, $argv;
		if (isset($this->arguments)) {
			list($opts,$args) = $this->parseArguments($this->arguments);
			$this->options = $opts;
			$this->arguments = $args;
		}
		if (isset($opts['h'])) {
			$this->usage();
			return 1;
		}
		return $this->main($argc,$argv);
	}
	function getName() {
		global $argv;
		return( basename($argv[0]) );
	}
	function parseArguments($options) {
		global $argc, $argv;
		$matched = false;
		$default = array();
		for ($n = 1; $n < $argc; $n++) {
			if (!$matched) {
				if ($argv[$n][0] == '-') {
					if (strpos($options,$argv[$n][1].':') !== false) $n++;
				} elseif ($argv[$n] == '--') {
					$n++;
					$matched = true;
				} else {
					$matched = true;
				}
			}
			if ($matched) $default[] = $argv[$n];
		}
		$params = (array)getopt($options);
		return array($params,$default);
	}
	function hasArgument($argument) {
		
		return (isset($this->options[$argument]));
	}
	function getArgument($argument) {
		return $this->options[$argument];
	}
	function sleep($ms) {
		usleep($ms*1000);
	}
}

interface IConsoleService {
	function servicemain();
	function signal($sig);
}

abstract class ConsoleService extends ConsoleApplication implements IConsoleService {

	public function __construct() {
		Console::debug("Constructing service instance");
		pcntl_signal(SIGQUIT, array(&$this,'signal'));
		pcntl_signal(SIGTERM, array(&$this,'signal'));
		pcntl_signal(SIGHUP, array(&$this,'signal'));
		pcntl_signal(SIGUSR1, array(&$this,'signal'));
		register_tick_function(array(&$this,'checkstate'));
		gc_enable();
	}

	public function __destruct() {
		Console::debug("Destructing service instance");
		gc_collect_cycles();
	}

	public function checkstate() {
		// TODO: Time this better
		gc_collect_cycles();
	}

	protected function fork() {
		$pid = pcntl_fork();
		if ($pid == -1) {
			Console::warn("Could not fork process!");
		} elseif ($pid == 0) {
			$this->servicemain();
		} else {
			Console::writeLn("Forked to new pid %d", $pid);
		}
	}

}

?>
