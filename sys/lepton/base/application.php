<?php __fileinfo("Console application base files");

// Console applications or services shouldn't time out
set_time_limit(0);

interface IConsoleApplication {
	function main($argc,$argv);
}

class Ansi {
	static $fgcolor = array(
		'black' => '0;30',
		'gray' => '1;30',
		'blue' => '0;34',   
		'ltblue' => '1;34',
		'green' => '0;32',
		'ltgreen' => '1;32',
		'cyan' => '0;36',
		'ltcyan' => '1;36',
		'red' => '0;31',
		'ltred' => '1;31',
		'purple' => '0;35',
		'ltpurple' => '1;35',
		'brown' => '0;33',
		'yellow' => '1;33',
		'ltgray' => '0;37',
		'white' => '1;37',
		'default' => '39'
	);
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
		'setItalic'         => '3m',
		'clearItalic'		=> '0m',
		'setUnderline'      => '4m',
		'clearUnderline'	=> '0m',
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
		$s = $str;
		$s = preg_replace('/\\\\b\{(.*?)\}/', Ansi::setBold().'$1'.Ansi::clearBold(), $s);
		$s = preg_replace('/\\\\i\{(.*?)\}/', Ansi::setItalic().'$1'.Ansi::clearItalic(), $s);
		$s = preg_replace('/\\\\u\{(.*?)\}/', Ansi::setUnderline().'$1'.Ansi::clearUnderline(), $s);
		$s = preg_replace_callback('/\\\\c\{(.*?)\}/', array('Ansi','_cb_color'), $s);
		return $s;
	}
	static function _cb_color($str) {
		$str = $str[1];
		$sa = explode(' ',$str);
		$color = $sa[0];
		$text = join(" ",array_slice($sa,1));
		return chr(27).'['.Ansi::$fgcolor[$color].'m'.$text.chr(27).'['.Ansi::$fgcolor['default'].'m';
	}
}
function __astr($str) { return Ansi::parse($str); }


abstract class ConsoleApplication extends Application implements IConsoleApplication {
	protected $_args;
	protected $_params;
	function usage() {
	
		Console::writeLn("%s - %s", $this->getName(), isset($this->description)?$this->description:"Untitled Lepton Application");
		Console::writeLn("");
		Console::writeLn("Usage:");
		Console::writeLn("    %s [-arguments] [parameters]", $this->getName());
		Console::writeLn("");
		Console::writeLn("Arguments:");
		foreach($this->arguments as $arg=>$val) {
			$opts = '';
			if ($val[0] != null) {
				$opts.= '-'.$val[0][0];
			}
			if ($val[1] != NULL) {
				if ($opts != '') $opts .= ',';
				$opts.= '--'.$val[1];
			}
			if (strlen($val[0])>1) { $opts.=' arg'; }
			$desc = $val[2];
			Console::writeLn("    %-20s %s", __astr($opts), $desc);
		}
		Console::writeLn();
		Console::writeLn("Environment Variables:");
		Console::writeLn("    APP_PATH             The application dir path");
		Console::writeLn("    SYS_PATH             The system path");
		Console::writeLn("    DEBUG                Show extended debug info (1-5)");
		Console::writeLn("    LOGFILE              Log file to output debug info to");
	
	}
	function run() {
		global $argc, $argv;
		if (isset($this->arguments)) {
			if (is_string($this->arguments)) {
				$strargs = $this->arguments;
				$longargs = array();
			} elseif (is_array($this->arguments)) {
				$strargs = '';
				$longargs = array();
				foreach($this->arguments as $arg) {
					$strargs.= $arg[0];
					// Scope is the : or ::
					$scope = substr($arg[0],1);
					$longargs[] = $arg[1].$scope;
				}
			} else {
				console::warn('Application->$arguments is set but format is not understood');
			}
			list($args,$params) = $this->parseArguments($strargs,$longargs);
			foreach($args as $arg=>$val) {
				if(in_array($arg,$longargs)){
					foreach($this->arguments as $argsrc) {
						if ($argsrc[1] == $arg) {
							$args[$argsrc[0]] = $val;
							$olarg = $argsrc[0];
						}
					}
				} else {
					foreach($this->arguments as $argsrc) {
						if ($argsrc[0] == $arg) {
							$args[$argsrc[1]] = $val;
							$olarg = $argsrc[0];
							// Do any matching we need here
						}
					}
				}
			}
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
	function parseArguments($options,$longopts=null) {
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
		if (COMPAT_GETOPT_LONGOPTS) {
			$params = (array)getopt($options,$longopts);
		} else {
			$params = (array)getopt($options);
		}
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




interface IShutdownHandler {
	function shutdown();
}
abstract class ShutdownHandler implements IShutdownHandler {
	static $handlers = array();
	private $lasterror;
	function __construct() {
		$this->lasterror = error_get_last();
	}
	function wasError() {
		return(!is_null($this->lasterror));
	}
	function getLastError() {
		return $this->lasterror;
	}
	static function register($handler) {
		$sh = new $handler();
		ShutdownHandler::$handlers[$handler] = $sh;
		register_shutdown_function(array(ShutdownHandler::$handlers[$handler],"shutdown"));
	}
}

class ConsoleShutdownHandler extends ShutdownHandler {

	function shutdown() {
		if ($this->wasError()) {
			// TODO: Write pretty error info
			// printf("There was an error!\n");
			// print_r($this->getLastError());
		}
	}

}

ShutdownHandler::register('ConsoleShutdownHandler');




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
