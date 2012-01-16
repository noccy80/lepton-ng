<?php module("Console application base files");

// Console applications or services shouldn't time out
set_time_limit(0);

using('lepton.cli.debug');
using('lepton.cli.exception');
using('lepton.system.process');

interface IConsoleApplication {
    function main($argc,$argv);
}

using('lepton.cli.ansi');

logger::registerFactory(new ConsoleLoggerFactory());

class Argument {
	const ARG_NONE = 0;
	const ARG_SINGLE = 1;
	const ARG_MULTI = 2;
	private $shortopt = null;
	private $longopt = null;
	private $helpstr = null;
	private $paramtype = self::ARG_NONE;
	private $options = null;
	function __construct($shortopt,$longopt = null,$helpstr = null,$paramtype = Argument::ARG_NONE,$options=null) {
		$this->shortopt = $shortopt;
		$this->longopt = $longopt;
		$this->helpstr = $helpstr;
		$this->paramtype = $paramtype;
		$this->options = (array)$options;
	}
	function get() {
		if ($this->paramtype == self::ARG_NONE) {
			$as = $this->shortopt;
		} elseif ($this->paramtype == self::ARG_SINGLE) {
			$as = $this->shortopt.':';
		} elseif ($this->paramtype == self::ARG_MULTI) {
			$as = $this->shortopt.'::';
		}
		$al = $this->longopt;
		$help = $this->helpstr;
		return array($as,$al,$help);
	}
}

/**
 * @class ConsoleApplication
 * @brief Abstract base class for console applications
 *
 *
 */
abstract class ConsoleApplication extends Application implements IConsoleApplication {

    protected $_args;
    protected $_params;
    protected $_pidfile;
    protected $description = "Application";
    protected $arguments;

    protected function application($description,$options=null) {
        $this->description = $description;
    }

    protected function addArgument(Argument $arg) {
        $this->arguments[] = $arg->get();
    }

    const PROCESS_RUNNING = 0;
    const PROCESS_CLEAR = 1;
    const PROCESS_STALE = 2;

    /**
     * @brief Show usage information on the command.
     * 
     * If -h is specified as a valid argument, it will invoke the usage()
     * method. Data displayed from this method is hosted in the application.
     *
     */
    function usage() {

        Console::writeLn("%s - %s", $this->getName(), $this->description);
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
            if (strlen($val[0])>2) { $opts.='arg'; }
            if ($val[1] != NULL) {
                if ($opts != '') $opts .= ',';
                $opts.= '--'.$val[1];
            }
            if (strlen($val[0])>1) { $opts.=' arg'; }
            $desc = $val[2];
            Console::writeLn("    %-20s %s", __astr($opts), $desc);
        }
        if (isset($this->commands)) {
            Console::writeLn();
            Console::writeLn("Commands:");
            foreach($this->commands as $cmd) {
                Console::writeLn("    %-20s %s", __astr($cmd[0]),$cmd[1]);
            }
        }
        Console::writeLn();
        Console::writeLn("Environment Variables:");
        Console::writeLn("    APP_PATH             The application dir path");
        Console::writeLn("    SYS_PATH             The system path");
        Console::writeLn("    DEBUG                Show extended debug info (1-5)");
        Console::writeLn("    LOGFILE              Log file to output debug info to");
        Console::writeLn("");
    }

    /**
     * @brief Run the application. Invoked by Lepton.
     * Will parse the arguments and make sure everything is in order.
     *
     */
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
                    foreach($args as $argsrc=>$v) {
                        if ($argsrc == $arg) {
                            $args[$argsrc[0]] = $val;
                            $olarg = $argsrc[0];
                        }
                    }
                } else {
                    foreach($args as $argsrc=>$v) {
                        if ($argsrc == $arg) {
                            $arg[$argsrc] = $val;
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
    
    /**
     * @brief Return the name of the script.
     * 
     * @return string The name of the script executing
     */
    function getName() {
        global $argv;
        return( basename($argv[0]) );
    }
    
    /**
     * @brief Parse arguments.
     * @internal
     *
     * Used internally to parse the options from the command line.
     *
     * @param string $options Options
     * @param array $longopts Long options
     * @return array,array Arguments and Parameters
     */
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
    
    /**
     * @brief Check if an argument is present.
     *
     * @param String $argument The argument to test for
     * @return Bool True if the argument is present on the command line
     */
    function hasArgument($argument) {
        return (isset($this->_args[$argument]));
    }
    
    /**
     * @brief Return an argument value.
     *
     * Returns the argument value from the command line. Only valid for
     * arguments that are defined with a ":" in their field definitions.
     *
     * @param String $argument The argument to retrieve
     * @return String The argument value
     */
    function getArgument($argument) {
        if (!isset($this->_args[$argument])) {
            return null;
        }
        return $this->_args[$argument];
    }
    
    
    /**
     * @brief Return all arguments
     *
     * @return Array All arguments
     */
    function getArguments() {
        return $this->_args;
    }
    
    /**
     * @brief Return all parameters
     *
     * @return Array All parameters
     */
    function getParameters() {
        return $this->_params;
    }
    
    /**
     * @brief Return a parameter from its index
     *
     * @param Int $index The index to retrieve
     * @return String The parameter
     */
    function getParameter($index) {
        if ($index >= count($this->_params))
            return null;
        return $this->_params[$index];
    }
    
    /**
     * @brief Return a range of parameters
     *
     * @param Int $first The first index to retrieve
     * @param Int $last The last itndex to retrieve (or null)
     * @return Array The parameters
     */
    function getParameterSlice($first,$last=null) {
        return array_slice($this->_params,$first,($last)?$last:count($this->_params));
    }
    /**
     * @brief Return the number of parameters present
     *
     * @return Int The number of parameters present.
     */
    function getParameterCount() {
        return count($this->_params);
    }
    
    /**
     * @brief Sleep for a specific number of microseconds.
     *
     */
    function sleep($ms=100) {
        usleep($ms*1000);
    }

    function checkPidFile($pidfile=null) {
        if ($pidfile == null) {
            if ($this->_pidfile) {
                $pidfile = $this->_pidfile;
            } else {
                $pidfile = $this->getName().'.pid';
            }
        }
        logger::debug('Checking pidfile: %s', $pidfile);
        // Check if the process exist
        if (file_exists($pidfile)) {
            $pid = file_get_contents($pidfile);
            $p = new Process($pid);
            logger::debug(' - Inspecting pid %d', $pid);
            if ($p->exists()) {
                // Already running
               return self::PROCESS_RUNNING;
            }
            unlink($pidfile);
            $retval = self::PROCESS_STALE;
        } else {
            $retval = self::PROCESS_CLEAR;
        }
        $tp = new Process();
        file_put_contents($pidfile, $tp->getPid());
        $this->_pidfile = $pidfile;
        return $retval;
    }

    function __destruct() {
        if ($this->_pidfile) unlink($this->_pidfile);
    }

}

interface IConsoleService {
    function servicemain();
    static function signal($sig);
}

/**
 * @class ConsoleService
 * @brief Wrap the functionality of a service, including forking.
 *
 *
 */
abstract class ConsoleService extends ConsoleApplication implements IConsoleService {

    private $_last_pid = null;
    
    /**
     * @brief Constructor.
     */
    public function __construct() {
        Console::debug("Constructing service instance");
        // register_shutdown_function(array(&$this, 'fatal'));
        gc_enable();
        declare(ticks=1);
        register_tick_function(array($this,'checkstate'));
    }

    /**
     * @brief Attaches a handler to a signal.
     * 
     * 
     * @todo Add the ability to specify a custom handler.
     * @param int $signal The signal to attach
     */
    protected function attachSignal($signal) {
        pcntl_signal($signal, array($this,'signal'));
    }

    /**
     * @brief Destructor.
     */
    public function __destruct() {
        // Console::debug("Destructing service instance");
        gc_collect_cycles();
    }

    public function checkstate() {
        // TODO: Time this better
    }

    /**
     * @brief Static signal handler
     * 
     * @param type $signal 
     */
    static function signal($signal) {
        echo "\n";
        Console::debug("Caught signal %d", $signal);
        if ($signal === SIGINT || $signal === SIGTERM) {
            exit();
        }
    }

    /**
     * @brief Fork the process, sending it to the background.
     * 
     * Remember to check the return value in order to figure out if the fork
     * was successful, and exit your application gracefully if true. This
     * method does NOT return the new pid. Use getLastPid() to retrieve the
     * pid of the new child process.
     * 
     * @param boolean $quiet If false, the fork status will be displayed
     * @return boolean True in the forked code, false in code calling fork.
     */
    protected function fork($quiet=true) {
        $pid = pcntl_fork();
        if ($pid == -1) {
            throw new CriticalException("pcntl_fork() failed!");
        } elseif ($pid == 0) {
            $this->servicemain();
            return true;
        } else {
            $this->_last_pid = $pid;
            if (!$quiet) Console::writeLn("Forked to new pid %d", $pid);
            return false;
        }
    }
    
    /**
     * @brief Return the pid of the last forked child process.
     * 
     * @return int The pid of the child process.
     */
    protected function getLastPid() {
        return $this->_last_pid;
    }

}




/**
 * @interface IShutdownHandler
 *
 *
 */
interface IShutdownHandler {
    function shutdown();
}

/**
 * @class ShutdownHandler
 *
 *
 */
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

/**
 * @class ConsoleShutdownHandler
 *
 *
 */
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
Lepton::setExceptionHandler('ConsoleExceptionHandler');

