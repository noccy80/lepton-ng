<?php

interface IConsoleApplication {
	function usage();
	function main($argc,$argv);
}

abstract class ConsoleApplication extends Application implements IConsoleApplication {
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
}

?>
