<?php

// Console applications or services shouldn't time out
set_time_limit(0);

interface IConsoleApplication {
	function main($argc,$argv);
}

class Ansi {
	static $seq = array(
		'setCursor'         => '$1;$2H',
		'cursorUp'          => '$1A',
		'cursorDown'        => '$1A',
		'cursorForward'     => '$1A',
		'cursorBackward'    => '$1A',
		'saveCursor'        => 's',
		'restoreCursor'     => 'u',
		'eraseDisplay'      => '2J',
		'eraseLine'         => 'K',
		'setBold'           => '1m',
		'clearBold'			=> '0m',
		'setMode'           => '=$1h',
		'setColor'          => '$@m',
	);
	static function __callStatic($mtd,$arg) {
		$sstr = self::$seq[$mtd];
		for($n = 0; $n < count($arg); $n++) { $sstr = str_replace('$'.$n+1,$arg[$n],$sstr); }
		$sstr = str_replace('$@',join(';',$arg),$sstr);
		return (chr(27)."[".$sstr);
	}
	static function parse($str) {
		
	}
}

abstract class ConsoleApplication extends Application implements IConsoleApplication {
	protected $_args;
	protected $_params;
	function run() {
		global $argc, $argv;
		if (isset($this->arguments)) {
			list($args,$params) = $this->parseArguments($this->arguments);
			$this->_args = $args;
			$this->_params = $params;
		}
		if (isset($args['h'])) {
			if (method_exists($this,'usage')) {
				$this->usage();
			}
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
		return (isset($this->_args[$argument]));
	}
	function getArgument($argument) {
		return $this->_args[$argument];
	}
	function getParameters() {
		return $this->_params;
	}
	function getParameter($index) {
		if ($index >= count($this->_params))
			return null;
		return $this->_params[$index];
	}
	function getParameterSlice($first,$last=null) {
		return array_slice($this->_params,$first,($last)?$last:count($this->_params));
	}
	function getParameterCount() {
		return count($this->_params);
	}
	function sleep($ms=100) {
		usleep($ms*1000);
	}
}

interface IConsoleService {
	function servicemain();
	function signal($sig);
}

abstract class ConsoleService extends ConsoleApplication implements IConsoleService {

	public function __construct() {
		// Console::debug("Constructing service instance");
		// register_shutdown_function(array(&$this, 'fatal'));
		pcntl_signal(SIGINT, array(&$this, 'signal'));
		pcntl_signal(SIGQUIT, array(&$this,'signal'));
		pcntl_signal(SIGTERM, array(&$this,'signal'));
		pcntl_signal(SIGHUP, array(&$this,'signal'));
		pcntl_signal(SIGUSR1, array(&$this,'signal'));
		gc_enable();
		register_tick_function(array(&$this,'checkstate'));
	}

	public function __destruct() {
		// Console::debug("Destructing service instance");
		gc_collect_cycles();
	}

	public function checkstate() {
		// TODO: Time this better
		gc_collect_cycles();
	}

	function signal($signal) {
		echo "\n";
		Console::debug("Caught signal %d", $signal);
		if ($signal === SIGINT || $signal === SIGTERM) {
			exit();
		}
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

class ConsoleExceptionHandler extends ExceptionHandler {

	function exception(Exception $e) {

		Console::debugEx(0, get_class($e), "Unhandled exception: (%s) %s in %s:%d", get_class($e), $e->getMessage(), str_replace(BASE_PATH,'',$e->getFile()), $e->getLine());
		$f = file($e->getFile());
		foreach($f as $i=>$line) {
			$mark = (($i+1) == $e->getLine())?'=> ':'   ';
			$f[$i] = sprintf('  %05d. %s',$i+1,$mark).$f[$i];
			$f[$i] = str_replace("\n","",$f[$i]);
		}
		$first = $e->getLine() - 4; if ($first < 0) $first = 0;
		$last = $e->getLine() + 3; if ($last >= count($f)) $last = count($f)-1;
		$source = join("\n",array_slice($f,$first,$last-$first));
		Console::debugEx(0, get_class($e), Console::backtrace(0,$e->getTrace(),true));
		Console::debugEx(LOG_LOG,"Exception","Source dump of %s:\n%s", str_replace(BASE_PATH,'',$e->getFile()), $source);
		$rv = 1;
		Console::debugEx(LOG_BASIC,__CLASS__,"Exiting with return code %d after exception.", $rv);
	
	}
	
}

Lepton::setExceptionHandler('ConsoleExceptionHandler');

?>
