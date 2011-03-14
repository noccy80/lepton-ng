<?php __fileinfo("Console application base files");

// Console applications or services shouldn't time out
set_time_limit(0);

using('lepton.cli.debug');

interface IConsoleApplication {
    function main($argc,$argv);
}

using('lepton.cli.ansi');

logger::registerFactory(new ConsoleLoggerFactory());

/**
 * @class ConsoleApplication
 * @brief Abstract base class for console applications
 *
 *
 */
abstract class ConsoleApplication extends Application implements IConsoleApplication {
    protected $_args;
    protected $_params;
    /**
     * @brief Show usage information on the command.
     *
     */
    function usage() {

        Console::writeLn("%s - %s", $this->getName(), isset($this->description)?$this->description:"Application [\$description undefined]");
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
}

interface IConsoleService {
    function servicemain();
    function signal($sig);
}

/**
 * @class ConsoleService
 *
 *
 */
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




/**
 * @class ConsoleExceptionHandler
 *
 *
 */
class ConsoleExceptionHandler extends ExceptionHandler {

    function exception(Exception $e) {

        logger::emerg("Unhandled exception: (%s) %s in %s:%d", get_class($e), $e->getMessage(), str_replace(BASE_PATH,'',$e->getFile()), $e->getLine());
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
        logger::emerg("Exiting with return code %d after exception.", $rv);
        Console::debugEx(LOG_BASIC,__CLASS__,"Exiting with return code %d after exception.", $rv);
    }

}

Lepton::setExceptionHandler('ConsoleExceptionHandler');


