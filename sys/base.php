<?php

/**
 * @brief Lepton Bootstrap Code.
 *
 * This is the bootstrap code for all things lepton. Make sure it is included
 * as require "sys/base.php" in your application.
 *
 * @example application.p
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @license GNU GPL Version 3
 */

define('SYSTEM_INITIAL_MEMORY_USE',(memory_get_usage(true)));

// Version definitions
foreach (array(
    'LEPTON_MAJOR_VERSION'      => 1,
    'LEPTON_MINOR_VERSION'      => 0,
    'LEPTON_RELEASE_VERSION'    => 0,
    'LEPTON_RELEASE_TAG'        => "alpha",
    'LEPTON_PLATFORM'           => "Lepton Application Framework",
    'PHP_RUNTIME_OS'            => php_uname('s')
) as $def => $val) define($def, $val);

// Various constants
define('RETURN_SUCCESS', 0);
define('RETURN_ERROR', 1);
const PI = 3.1415926535897931;
define('NS_SEPARATOR','::');
define('DATE_SQL','Y-m-d H:i:s');
declare(ticks = 1);

// Compatibility definitions
foreach (array(
    'COMPAT_GETOPT_LONGOPTS'    => (PHP_VERSION >= "5.3"),
    'COMPAT_SOCKET_BACKLOG'     => (PHP_VERSION >= "5.3.3"),
    'COMPAT_HOST_VALIDATION'    => (PHP_VERSION >= "5.2.13"),
    'COMPAT_NAMESPACES'         => (PHP_VERSION >= "5.3.0"),
    'COMPAT_INPUT_BROKEN'       => ((PHP_VERSION >= "5") && (PHP_VERSION < "5.3.1")),
    'COMPAT_CALLSTATIC'         => (PHP_VERSION >= "5.3.0"),
    'COMPAT_CRYPT_BLOWFISH'     => (PHP_VERSION >= "5.3.0"),
    'COMPAT_PHP_FNMATCH'        => (PHP_OS == "Linux") || ((PHP_OS == "Windows") && (PHP_VERSION >= "5.3"))
) as $compat => $val) define($compat, $val);

// Additional verison definitions
define("LEPTON_VERSION", LEPTON_MAJOR_VERSION . "." . LEPTON_MINOR_VERSION . "." . LEPTON_RELEASE_VERSION . " " . LEPTON_RELEASE_TAG);
define("LEPTON_PLATFORM_ID", LEPTON_PLATFORM . " v" . LEPTON_VERSION);

// Platform definitions
define('IS_WINNT', (strtolower(PHP_RUNTIME_OS) == 'windows'));
define('IS_LINUX', (strtolower(PHP_RUNTIME_OS) == 'linux'));
define('WINDOWS',  (strtolower(PHP_RUNTIME_OS) == 'windows'));

// PHP Version Definitions / Fixes
if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}
if (PHP_VERSION_ID < 50207) {
    define('PHP_MAJOR_VERSION', $version[0]);
    define('PHP_MINOR_VERSION', $version[1]);
    define('PHP_RELEASE_VERSION', $version[2]);
}

///// Compensate for missing/unavailable functions ///////////////////////////

if (!function_exists('fnmatch')) {
    function fnmatch($pattern, $string) {
        return @preg_match(
            '/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'),
                array('*' => '.*', '?' => '.?')) . '$/i', $string
        );
    }
}

if (!function_exists('sys_getloadavg')) {
    function sys_getloadavg() {
        $loadavg_file = '/proc/loadavg';
        if (file_exists($loadavg_file)) {
            return explode(chr(32),file_get_contents($loadavg_file));
        }
        return array(0,0,0);
    }
}

// @author sleek (php.net)
if (!function_exists('substr_compare')) {
    function substr_compare($main_str, $str, $offset, $length = NULL, $case_insensitivity = false) {
        $offset = (int) $offset;

        // Throw a warning because the offset is invalid
        if ($offset >= strlen($main_str)) {
            trigger_error('The start position cannot exceed initial string length.', E_USER_WARNING);
            return false;
        }

        // We are comparing the first n-characters of each string, so let's use the PHP function to do it
        if ($offset == 0 && is_int($length) && $case_insensitivity === true) {
            return strncasecmp($main_str, $str, $length);
        }

        // Get the substring that we are comparing
        if (is_int($length)) {
            $main_substr = substr($main_str, $offset, $length);
            $str_substr = substr($str, 0, $length);
        } else {
            $main_substr = substr($main_str, $offset);
            $str_substr = $str;
        }

        // Return a case-insensitive comparison of the two strings
        if ($case_insensitivity === true) {
            return strcasecmp($main_substr, $str_substr);
        }

        // Return a case-sensitive comparison of the two strings
        return strcmp($main_substr, $str_substr);
    }
}


///// Path Resolution ////////////////////////////////////////////////////////

// Resolve application and system paths
if (!defined('APP_PATH')) {
    if (getenv('APP_PATH')) {
        define('APP_PATH', realpath(getenv('APP_PATH')) . '/');
    } else {
        $path = '';
        if (getenv('SCRIPT_FILENAME')) {
            $_spath = getenv('SCRIPT_FILENAME');
            $_spath = realpath(pathinfo($path, PATHINFO_DIRNAME));
        } else {
            $_spath = getcwd();
        }
        // If in /bin, assume base is at ..
        if (substr($_spath, strlen($_spath) - 4, 4) == '/bin') {
            $path = $path . '/../';
        }
        if (substr($_spath, strlen($_spath) - 4, 4) == '/app') {
            $_spath = $_spath . '/../';
        }
        $_spath = $_spath . '/app';
        define('APP_PATH', realpath($_spath) . '/');
        unset($_spath);
    }
} else {
    Console::warn("APP_PATH already defined and set to %s", APP_PATH);
    $path = APP_PATH;
}

// Resolve base path
define('BASE_PATH', realpath(APP_PATH . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR);

// Resolve system path
if (getenv('SYS_PATH')) {
    define('SYS_PATH', realpath(getenv('SYS_PATH') . DIRECTORY_SEPARATOR) . '/');
} else {
    $syspath = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
    define('SYS_PATH', realpath($syspath) . '/');
}
if (!defined('APP_PATH')) {
    define('APP_PATH', join(DIRECTORY_SEPARATOR, array($path, 'app')) . '/');
}

// Resolve temporary path
if (!defined("TMP_PATH")) {
    if (file_exists(BASE_PATH . 'tmp') && (is_writable(BASE_PATH . 'tmp'))) {
        $tmp = BASE_PATH . 'tmp';
    } else {
        $tmp = sys_get_temp_dir();
    }
    define('TMP_PATH', $tmp . '/');
}

if ( !function_exists('sys_get_temp_dir')) {
    function sys_get_temp_dir() {
        if ($temp=getenv('TMP')) return $temp;
        if ($temp=getenv('TEMP')) return $temp;
        if ($temp=getenv('TMPDIR')) return $temp;
        return null;
    }
}

// Enable PHPs error reporting when the DEBUG envvar is set
if (getenv("DEBUG") >= 1) {
    define('DEBUGMODE', true);
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    $dlevel = intval(getenv("DEBUG"));
    if ($dlevel > 10) { $dlevel = 10; }
    base::logLevel($dlevel);
} else {
    define('DEBUGMODE', false);
    base::logLevel(0);
}

if (php_sapi_name() == 'cli') {
    define('LEPTON_CONSOLE', true);
    if (base::logLevel() == 0) base::logLevel(LOG_INFO);
} else {
    define('LEPTON_CONSOLE', false);
}
if (getenv("LOGFILE")) {
    define("LOGFILE", fopen(getenv("LOGFILE"), 'a+'));
    fprintf(LOGFILE, "\n --- MARK --- \n\n");
} else {
    define("LOGFILE", null);
}

define('LOG_DEBUG2', 7);
define('LOG_DEBUG1', 6);
define('LOG_EXTENDED', 5);
define('LOG_VERBOSE', 4);
define('LOG_BASIC', 3);
define('LOG_WARN', 2);
define('LOG_LOG', 1);

abstract class base {

    private static $_basepath = null;
    private static $_apppath = null;
    private static $_syspath = null;
    private static $_loglevel = 0;

    static function expand($pathstr,$prefix=null) {
        if (substr($pathstr,0,1) == '/') {
            $path = base::appPath().'/'.$prefix.'/'.substr($pathstr,1);
        } elseif (strtolower(substr($pathstr,0,4)) == 'app:') {
            $path = base::appPath().'/'.substr($pathstr,4);
        } elseif (strtolower(substr($pathstr,0,5)) == 'base:') {
            $path = base::basePath().'/'.substr($pathstr,5);
        } elseif (strtolower(substr($pathstr,0,4)) == 'sys:') {
            $path = base::sysPath().'/'.substr($pathstr,4);
        } elseif (strtolower(substr($pathstr,0,4)) == 'tmp:') {
            $path = base::tmpPath().'/'.substr($pathstr,4);
        } else {
            $path = base::appPath().'/'.$prefix.'/'.$pathstr;
        }		
        while (strpos($path,'//')) $path = str_replace('//','/',$path);
        return $path;
    }

    static function basePath($newpath=null) {
        $ret = (self::$_basepath) ? self::$_basepath : BASE_PATH;
        if ($newpath != null) {
            self::$_basepath = realpath($newpath).'/';
            console::debug("Setting base path: %s", self::$_basepath);
        }
        return $ret;
    }

    static function appPath($newpath=null) {
        $ret = (self::$_apppath) ? self::$_apppath : APP_PATH;
        if ($newpath != null) {
            self::$_apppath =  realpath($newpath).'/';
            console::debug("Setting app path: %s", self::$_apppath);
        }
        return $ret;
    }

    static function sysPath($newpath=null) {
        $ret = (self::$_syspath) ? self::$_syspath : SYS_PATH;
        if ($newpath != null) {
            self::$_syspath =  str_replace('//','/',realpath($newpath).'/');
            console::debug("Setting sys path: %s", self::$_syspath);
        }
        return $ret;
    }
    
    static function tmpPath() {
        return realpath(sys_get_temp_dir());        
    }

    static function logLevel($newlevel=null) {
        $ret = self::$_loglevel;
        if ($newlevel)
            self::$_loglevel = $newlevel;
        return $ret;
    }
    
}

function getpath($path) {
    return base::expand($path);
}

function expandpath($path) {
    return base::expand($path);
}


////// Interfaces /////////////////////////////////////////////////////////////

/*
interface IDataConsumer {
    function setData($data);
    function checkData($data);
}

interface IDataProvider {
    function getData();
}

abstract class DataConsumer implements IDataConsumer {
}

abstract class DataProvider implements IDataProvider {
}
*/


function using($mod) {
    ModuleManager::load($mod);
}
function depends($functionality) {
    if (extension_loaded($functionality)) {
        return;
    }
    throw new BaseException("Required functionality ".$functionality." missing");
}
function provides($functionality) { }

using('base.exception');
using('base.debug');
using('base.config');
using('base.assert');
using('base.utils');
using('base.structs');

abstract class Globals {
    
    private static $globals = array();
    
    private function __construct() { }
    
    static function get($key) {
        if (!self::$globals) return null;
        if (arr::hasKey(self::$globals,$key)) return self::$globals[$key];
        return null;
    }
    
    static function set($key,$value) {
        self::$globals[$key] = $value;
    }
    
    static function has($key) {
        return (arr::hasKey(self::$globals,$key));
    }
    
    static function clr($key) {
        unset(self::$globals[$key]);
    }
    
}

////// Console ////////////////////////////////////////////////////////////////

/**
 * @class Console
 * @brief Console management function.
 *
 * This class handles all console-related input and output.
 */
class Console {

    /**
     * @brief Output a debug message of a custom level
     *
     * @param integer $level The level of debug detail
     * @param string $module The module this message relates to
     * @param string $format The format of the message
     * @param Mixed ... The debug output in sprintf format
     */
    static function debugEx($level, $module) {
        $args = func_get_args();
        $strn = @call_user_func_array('sprintf', array_slice($args, 2));
        $ts = Console::ts();
        $lines = explode("\n", $strn);
        foreach ($lines as $line) {
            if (getenv("DEBUG") >= $level) {
                if (DEBUGMODE):
                    fprintf(STDERR, "%s %-20s %s\n", $ts, $module, $line);
                else:
                    fprintf(STDERR, "[%s] %s\n", $module, $line);
                endif;
            }
            if (LOGFILE)
                fprintf(LOGFILE, "%s | %-20s | %s\n", $ts, $module, $line);
        }
    }

    /**
     *
     */
    static function debug() {
        $args = func_get_args();
        @call_user_func_array(array('Console', 'debugEx'), array_merge(array(LOG_DEBUG1, 'Debug'), array_slice($args, 0)));
    }

    /**
     *
     */
    static function warn() {
        $args = func_get_args();
        @call_user_func_array(array('logger', 'warning'), $args);
        @call_user_func_array(array('Console', 'debugEx'), array_merge(array(LOG_WARN, 'Warning'), array_slice($args, 0)));
    }

    /**
     * @brief Print a message and exit with an error code
     *
     * @param string $format The format of the message
     * @param Mixed ...The elements of the output
     */
    static function fatal() {
        $args = func_get_args();
        @call_user_func_array(array('logger', 'emerg'), $args);
        @call_user_func_array(array('Console', 'debugEx'), array_merge(array(LOG_LOG, 'Fatal'), array_slice($args, 0)));
        die(RETURN_ERROR);
    }

    /**
     * @brief Print an error message to standard error
     *
     * @param string $format The format of the message
     * @param Mixed ...The elements of the output
     */
    static function error() {
        $args = func_get_args();
        call_user_func_array('fprintf', array_merge(array(STDERR, $args[0] . "\n"), array_slice($args, 1)));
    }

    /**
     * @brief Print a debug backtrace
     *
     * @param integer $trim Number of items to trim from the top of the stack
     * @param array $stack The stack, if null will get current stack
     * @param boolean $return If true the result will be returned instead of outputted
     */
    static function backtrace($trim=1, $stack=null, $return=false) {
        return Debug::backtrace($trim+1, $stack, $return);
        
        if (!$stack) {
            $stack = debug_backtrace(false);
        }
        $trace = array();
        foreach ($stack as $i => $method) {
            $args = array();
            if ($i > ($trim - 1)) {
                if (isset($method['args'])) {
                    foreach ($method['args'] as $arg) {
                        $args[] = gettype($arg);
                    }
                }
                $mark = (($i == ($trim)) ? 'in' : '  invoked from');
                if (!isset($method['file'])) {
                    if (isset($method['type'])) {
                        $trace[] = sprintf("  %s %s%s%s(%s) - %s:%d", $mark, $method['class'], $method['type'], $method['function'], join(',', $args), '???', 0);
                    } else {
                        $trace[] = sprintf("  %s %s(%s) - %s:%d", $mark, $method['function'], join(',', $args), '???', 0);
                    }
                } else {
                    if (isset($method['type'])) {
                        $trace[] = sprintf("  %s %s%s%s(%s) - %s:%d", $mark, $method['class'], $method['type'], $method['function'], join(',', $args), str_replace(SYS_PATH, '', $method['file']), $method['line']);
                    } else {
                        $trace[] = sprintf("  %s %s(%s) - %s:%d", $mark, $method['function'], join(',', $args), str_replace(SYS_PATH, '', $method['file']), $method['line']);
                    }
                }
            }
        }
        if ($return)
            return join("\n", $trace) . "\n";
        Console::debugEx(LOG_WARN, 'Backtrace', "%s", join("\n", $trace) . "\n");
        if (LOGFILE)
            fprintf(LOGFILE, join("\n", $trace) . "\n");
    }

    /**
     *
     */
    static function write() {
        $args = func_get_args();
        if (count($args) > 0) {
            if (count($args) > 1) {
                $strn = @call_user_func_array('sprintf', array_slice($args, 0));
            } else {
                $strn = $args[0];
            }
        } else {
            $strn = "";
        }
        printf("%s", $strn);
        if (LOGFILE)
            fprintf(LOGFILE, $strn);
    }

    static function erase($count) {
        console::write(str_repeat("\x08",$count));
    }

    /**
     *
     */
    static function readLn() {
        $ld = fgets(STDIN);
        $ld = __strip_newline($ld);
        return $ld;
    }

    static function readPass() {
        if (IS_LINUX) {
            system('stty -echo');
        }
        $ld = fgets(STDIN);
        $ld = __strip_newline($ld);
        if (IS_LINUX) {
            system('stty echo');
        }
        console::write("\n");
        return $ld;
    }

    static function clearLine() {
        printf("\x1b[1K\r");
    }

    static function writeLn() {
        $args = func_get_args();
        if (count($args) > 0) {
            if (count($args) > 1) {
                $strn = @call_user_func_array('sprintf', array_slice($args, 0));
            } else {
                $strn = $args[0];
            }
        } else {
            $strn = "";
        }
        printf("%s\n", $strn);
        if (LOGFILE)
            fprintf(LOGFILE, $strn . "\n");
    }

    /**
     *
     */
    static function status() {
        $args = func_get_args();
        $strn = @call_user_func_array('sprintf', array_slice($args, 0));
        printf("%s", $strn . str_repeat(chr(8), strlen($strn)));
        if (LOGFILE)
            fprintf(LOGFILE, $strn);
    }

    /**
     * @brief Get a timestamp
     *
     * @return string The timestamp
     */
    static function ts() {
        return @date("M-d H:i:s", time());
    }

    /**
     *
     */
    static function getChar() {
        
    }

    /**
     *
     */
    static function readLine($hidden=false) {
        if ($hidden) return console::readPass();
        return console::readLn();
    }

}

////// Timer //////////////////////////////////////////////////////////////////

/**
 * @class Timer
 * @brief A rough timer with microsecond resolution.
 *
 * Returns the time elapsed between a call to start() and stop() with
 * the getElapsed() method. 
 */
class Timer {

    private $_starttime = 0.0;
    private $_stoptime = 0.0;
    private $_running = false;

    /**
     * Creates the timer.
     *
     * @param bool $start If true, the timer will start when constructed
     */
    function __construct($start=false) {
        if ($start)
            $this->start();
    }

    /**
     * @brief Starts the timer.
     * The timer will remain running until a call is made to stop()
     *
     * @see Timer::stop()
     * @see Timer::getElapsed()
     */
    function start() {
        $this->_running = true;
        $this->_starttime = microtime(true);
    }

    /**
     * @brief Stops the timer again.
     *
     * @see Timer::start()
     * @see Timer::getElapsed()
     */
    function stop() {
        $this->_stoptime = microtime(true);
        $this->_running = false;
        return $this->getElapsed();
    }

    /**
     * @brief Returns the elapsed time.
     * The timer doesn't have to be stopped for this.
     *
     * @see Timer::start()
     * @return Float Seconds elapsed.
     */
    function getElapsed() {
        if ($this->_running) {
            return (microtime(true) - $this->_starttime);
        } else {
            return ($this->_stoptime - $this->_starttime);
        }
    }

}

////// Lepton /////////////////////////////////////////////////////////////////

/**
 * @class Lepton
 */
class Lepton {

    const EVT_SHUTDOWN = 'lepton.core.shutdown';

    private static $__exceptionhandler = null;
    private static $__errorhandler = null;
    static $mimetypes = array(
        'png' => 'image/png',
        'gif' => 'image/gif',
        'css' => 'text/css',
        'js' => 'text/javascript',
        'html' => 'text/html',
        'otf' => 'application/x-opentype'
    );

    static function applicationExists() {
        return (file_exists(APP_PATH));
    }

    /**
     *
     */
    static function run($class) {
        static $ic = 0;
        $args = func_get_args();
        $args = array_slice($args, 1);
        Console::debugEx(LOG_EXTENDED, __CLASS__, "Inspecting environment module state:\n%s", ModuleManager::debug());
        $ic++;
        if (class_exists($class)) {
            $rv = 0;
            $apptimer = new Timer();
            try {
                $instance = new $class();
                if (!($instance instanceOf IApplication)) {
                    console::warn("FATAL: Application is not instance of IApplication");
                    return RETURN_ERROR;
                }
                Console::debugEx(LOG_BASIC, __CLASS__, "Invoking application instance from %s.", $class);
                $apptimer->start();
                if (is_callable(array($instance, 'run'))) {
                    $rv = call_user_func_array(array($instance, 'run'), $args);
                } else {
                    console::writeLn("Requested application class %s is not runnable.", $class);
                }
                $apptimer->stop();
                unset($instance);
                Console::debugEx(LOG_BASIC, __CLASS__, "Main method exited with code %d after %.2f seconds.", $rv, $apptimer->getElapsed());
            } catch (Exception $e) {
                throw $e;
            }
            $ic--;
            if ($ic == 0)
                return( $rv );
        } else {
            $ic--;
            if ($ic == 0) {
                Console::warn('FATAL: Application class %s not found!', $class);
                exit(RETURN_ERROR);
            } else {
                Console::warn('Application class %s not found!', $class);
            }
        }
    }

    static function using($module) {
        Console::warn("Lepton::using() is deprecated (%s)", $module);
        ModuleManager::load($module);
    }

    static function autoload($module, $as) {
        Console::warn("Lepton::autoload() is deprecated (%s => %s)", $module, $as);
    }

    /**
     *
     */
    static function getMimeType($filename) {
        $file = escapeshellarg(BASE_PATH . $filename);
        return str_replace("\n", "", shell_exec("file -b --mime-type " . $file));
    }

    static function getServerHostname() {
        if (isset($_SERVER['hostname'])) {
            return $_SERVER['hostname'];
        } else {
            return basename($_SERVER['SCRIPT_NAME']);
        }
    }

    /**
     *
     */
    static function setExceptionHandler($handler, $override=false) {
        if (($override == true) || (self::$__exceptionhandler == null)) {
            self::$__exceptionhandler = $handler;
            Console::debugEx(LOG_BASIC, __CLASS__, "Assigned exception handler: %s", $handler);
        } else {
            Console::debugEx(LOG_BASIC, __CLASS__, "Ignoring exception handler: %s", $handler);
        }
    }

    /**
     *
     */
    static function handleException(Exception $e) {
        if (self::$__exceptionhandler) {
            $eh = new self::$__exceptionhandler();
            $eh->exception($e);
        } else {
            printf($e);
            die("Unhandled exception and no exception handler assigned.");
        }
    }

    static function setErrorHandler($handler, $override=false) {
        if (($override == true) || (self::$__errorhandler == null)) {
            self::$__errorhandler = $handler;
            console::debugEx(LOG_BASIC, __CLASS__, "Assigned error handler: %s", $handler);
        } else {
            console::debugEx(LOG_BASIC, __CLASS__, "Ignoring error handler: %s", $handler);
        }
    }

    static function handleError($errno,$errstr,$errfile,$errline,$errcontext=null) {
        $args = func_get_args();
        if (self::$__errorhandler) {
            return (call_user_func_array(self::$__errorhandler,$args) == true);
        } else {
            // Chain PHPs error handling
            switch($errno) {
            case E_STRICT:
                logger::debug('Warning: %s:%d %s', str_replace(base::basePath(),'',$errfile), $errline, $errstr);
                break;
            case E_DEPRECATED:
                if (config::has('lepton.showdeprecated',false))
                    logger::warning('Deprecated: %s:%d %s', str_replace(base::basePath(),'',$errfile), $errline, $errstr);
                break;
            default:
                logger::warning('%s:%d %s', str_replace(base::basePath(),'',$errfile), $errline, $errstr);
                break;
            }
            return true;
        }
    }

    /**
     * @brief Handle shutdown (for debugging and error reporting)
     */
    static function handleShutdown() {
        $error = error_get_last();
        if (($error['type'] == 1) && LEPTON_CONSOLE) {
            $f = file($error['file']);
            //printf('<pre>Error: %s',$error['message']);
            foreach ($f as $i => $line) {
                $mark = (($i + 1) == $error['line']) ? '=> ' : '   ';
                $f[$i] = sprintf('  %05d. %s', $i + 1, $mark) . $f[$i];
                $f[$i] = str_replace("\n", "", $f[$i]);
            }
            $first = $error['line'] - 4;
            if ($first < 0)
                $first = 0;
            $last = $error['line'] + 3;
            if ($last >= count($f))
                $last = count($f) - 1;
            $source = join("\n", array_slice($f, $first, $last - $first));
            echo "\n" . $source . "\n";
            //echo '</pre>';
            die();
        } elseif ($error['type'] == 1) {
            echo '<h1>Fatal error</h1><p>A fatal error occured processing your request</p>';
            if (config::get('lepton.mvc.exception.showdebug',false) == true)
                echo '<pre>'.$error['message']."\n".'  in '.$error['file']."\n".'  on line '.$error['line'].'</pre>';
            // MvcExceptionHandler::exception(new BaseException("Fatal error: " . $error['message'] . ' in ' . $error['file']));
        }
    }

}

set_error_handler(array('Lepton', 'handleError'));
set_exception_handler(array('Lepton', 'handleException'));
register_shutdown_function(array('Lepton', 'handleShutdown'));


abstract class ExceptionHandler {
    abstract function exception(Exception $e);
}

////// System /////////////////////////////////////////////////////////////////

class System {

    function getLoadAverage() {
        if (IS_LINUX) {
            $la = sys_getloadavg();
            return $la[0];
        } else {
            return 0.0;
        }
    }

    function getTempDir() {
        return sys_get_temp_dir();
    }

}

////// Application Base ///////////////////////////////////////////////////////

interface IApplication {
    function run();
}

abstract class Application implements IApplication {

}

////// Variable Casting / Utils ///////////////////////////////////////////////

using('base.string');

abstract class integer {

    static function cast($var) {
        return intval($var);
    }

    static function restrict($val, $min, $max) {
        return ($val >= $max) ? $max : ($val <= $min) ? $min : $val;
    }

    static function map($val, $minin, $maxin, $minout, $maxout) {
        return ((($val - $minin) / ($maxin - $minin)) * ($maxout - $minout) + $minout);
    }

}

abstract class float {

    static function cast($var) {
        return floatval($var);
    }

}

abstract class arr {

    static function merge($dest, $array) {
        return (array_merge($array,$dest));
    }

    static function apply($dest, $array) {
        foreach ($array as $k => $v) {
            $dest[$k] = $v;
        }
        return $dest;
    }
    
    static function defaults(array $array,$defaults) {
        foreach($defaults as $key=>$val) {
            if (!arr::hasKey($array,$key)) $array[$key] = $val;
        }
        return $array;
    }

    static function bucketize(array $array,$index) {
        $ret = array();
        foreach((array)$array as $item) {
            $ret[$item[$index]][] = $item;
        }
        return $ret;
    }

    static function flip(array $array,$index) {
        $ret = array();
        foreach((array)$array as $item) {
            $ret[$item[$index]] = $item;
        }
        return $ret;
    }

    static function hasKey(Array $arr, $key) {
        return array_key_exists($key, $arr);
    }

    static function hasValue(Array $arr, $key) {
        return in_array($key, $arr);
    }

    static function head(Array $arr, $num = 1) {
        return $arr[0];
    }

    static function tail(Array $arr, $num = 1) {
        return $arr[count($arr)-1];
    }

}

class vartype {
    private $vartype = null;
    private $optional = true;
    private $length = null;
    private $precision = null;
    private $defaultvalue = null;
    static function string($length=null) {
        $vt = new vartype('string');
        $vt->length($length);
        return $vt;
    }
    static function float($length=null,$precision=null) {
        $vt = new vartype('float');
        $vt->length($length);
        $vt->precision($precision);
        return $vt;
    }
    function __construct($vartype) {
        $this->vartype = $vartype;
    }
    function length($length=null) {
        $this->length = $length;
        return $this;
    }
    function precision($precision = null) {
        $this->precision = $precision;
        return $this;
    }
    function nullable() {
        $this->optional = true;
        return $this;
    }
    function required() {
        $this->optional = false;
        return $this;
    }
    function defaultvalue($default) {
        $this->defaultvalue = $default;	
        return $this;
    }
    function getVartype() { return $this->vartype; }
    function getRequired() { return !$this->optional; }
    function getLength() { return $this->length; }
    function getDefault() { return $this->defaultvalue; }
}

////// ModuleManager //////////////////////////////////////////////////////////


interface IModuleLoader {
    function queryClass($classname);
    function loadModule($modulename);
}

class BasicModuleLoader {
    
}

abstract class Module {
    static function volatile() {
        // Flag module as volatile
    }
}

/**
 * @class ModuleManager
 *
 */
class ModuleManager {

    static $_modules;
    static $_order;
    static $_lastmodule = null;

    /**
     *
     */
    static function checkExtension($extension, $required=false) {
        if (extension_loaded($extension)) {
            return true;
        }
        logger::debug("Requested PHP extension not loaded: %s (from %s)", $extension, __callee());
        $filename = (PHP_SHLIB_SUFFIX === 'dll' ? 'php_' : '') . $extension . '.' . PHP_SHLIB_SUFFIX;
        logger::debug("Attempting a manual load of %s from %s", $extension, $filename);
        if (ini_get('enable_dl') && !ini_get('safe_mode')) {
            if (function_exists('dl') && ($required) && !@dl($filename)) {
                logger::warnin("Dynamic loading of extensions disabled and extension %s flagged as required. Please load it manually or enable the dl() function.", $extension);
                exit(1);
            }
        }
        return (extension_loaded($extension));
    }

    static function getLastModuleName() {
        return self::$_lastmodule;
    }

    /**
     *
     */
    static function hasExtension($extension) {
        if (extension_loaded($extension)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     */
    static function _mangleModulePath($module) {
        // Console::debugEx(LOG_LOG,__CLASS__,"Mangling module %s", $module);
        if (preg_match('/^app\./', $module)) {
            $path = base::appPath() . '/' . str_replace('.', '/', str_replace('app.', '', $module)) . '.php';
        } else {
            $path = base::sysPath() . '/' . str_replace('.', '/', $module) . '.php';
        }
        // Console::debugEx(LOG_LOG,__CLASS__,"  -> %s", $path);
        return $path;
    }

    /**
     *
     */
    static function load($module, $optional=false) {

        // Check if the path is globbed
        if (strpos($module, '*') == (strlen($module) - 1)) {
            $path = self::_mangleModulePath($module);
            Console::debugEx(LOG_EXTENDED, __CLASS__, "Looking for modules matching %s from %s", $module, $path);
            $f = glob($path);
            sort($f);
            $failed = false;
            foreach ($f as $file) {
                if (!ModuleManager::load(str_replace('*', basename($file, '.php'), $module)))
                    $failed = true;
            }
            return (!$failed);
        }

        // Check if the module is already loaded
        if (ModuleManager::has($module)) {
            logger::debug("Already loaded %s.", $module);
            return true;
        }

        // Otherwise mangle the path
        $path = self::_mangleModulePath($module);
        /*
          if (file_exists(APP_PATH.$modpath)) {
          $path = APP_PATH.$modpath;
          } elseif (file_exists(SYS_PATH.$modpath)) {
          $path = SYS_PATH.$modpath;
          } else {
          $path = null;
          }
         */

        if ($path) {
            if (file_exists(basename($path,'.php').'.class.php')) {
                $path = basename($path,'.php').'.class.php';
            }
            if (file_exists($path)) {
                self::$_lastmodule = $module;
                Console::debugEx(LOG_BASIC, __CLASS__, "Loading %s (%s).", $module, str_replace(BASE_PATH, '', $path));
                try {
                    ModuleManager::$_modules[strtolower($module)] = array();
                    ModuleManager::$_order[] = strtolower($module);
                    // Console::debugEx(LOG_DEBUG2,__CLASS__,"  path = %s", $path);
                    require($path);
                    array_pop(ModuleManager::$_order);
                } catch (ModuleException $e) {
                    Console::debugEx(LOG_BASIC, __CLASS__, "Exception loading %s!", $module);
                    throw $e;
                    return false;
                }
                return true;
            } else {
                throw new ModuleException("Could not load module ".$module.": Path not found");
                return false;
            }
        } else {
            Console::debugEx(LOG_BASIC, __CLASS__, "Failed to load %s.", $module);
            return false;
        }
    }

    /**
     *
     */
    static function conflicts($module) {
        if (!is_array(ModuleManager::$_modules)) {
            ModuleManager::$_modules = array();
            return false;
        }
        $module = strtolower($module);
        foreach (ModuleManager::$_modules as $mod => $meta) {
            if ($mod == $module) {
                Console::warn("Requested module %s conflicts with the loaded module %s", $module, ModuleManager::$_order[count(ModuleManager::$_order) - 1]);
            }
        }
        return false;
    }

    /**
     *
     */
    static function has($module) {
        if (!is_array(ModuleManager::$_modules)) {
            ModuleManager::$_modules = array();
            return false;
        }
        $module = strtolower($module);
        foreach (ModuleManager::$_modules as $mod => $meta) {
            if ($mod == $module)
                return true;
        }
        return false;
    }

    /**
     *
     */
    static function debug() {
        $modinfo = array();
        if (!is_array(ModuleManager::$_modules)) {
            ModuleManager::$_modules = array();
        }
        $modlist = ModuleManager::$_modules;
        ksort($modlist);
        foreach ($modlist as $mod => $meta) {
            // TODO: Show metadata
            $mi = "n/a";
            $ver = null;
            if (isset($meta['modinfo']))
                $mi = $meta['modinfo'];
            if (isset($meta['version']))
                $ver = 'v' . $meta['version'];
            $modinfo[] = sprintf("    %s - %s", $mod, $mi . (isset($ver) ? ' ' . $ver : ''));
        }
        return join("\n", $modinfo) . "\n";
    }

}

////// KeyStore ///////////////////////////////////////////////////////////////

abstract class KeyStore {

    static $_data;

    function register($key, $data) {
        KeyStore::$_data[$key] = $data;
    }

    function query($key) {
        if (isset(KeyStore::$_data[$key])) {
            return KeyStore::$_data[$key];
        } else {
            return null;
        }
    }

}

class KeyStoreRequest {

    private $key;
    private $val;

    function __construct($key) {
        $this->key = $key;
        $this->val = KeyStore::query($key);
    }

    function __toString() {
        return $this->val;
    }

    function get() {
        return $this->val;
    }

}

function KeyStore($key) {
    return (string) (new KeyStoreRequest($key));
}

////// Logging Functionality //////////////////////////////////////////////////

interface ILoggerFactory {
    function __logMessage($priority, $message);
}

abstract class LoggerFactory implements ILoggerFactory {

}

class SyslogLoggerFactory extends LoggerFactory {

    private $verbose;
    private $logger;

    function __construct($perror=false, $facility=LOG_LOCAL0) {
        $this->verbose = $perror;
        $flag = LOG_PID;
        if ($perror)
            $flag |= LOG_PERROR;
        $this->logger = openlog(Lepton::getServerHostname(), $flag, $facility);
    }

    function __logMessage($prio, $msg) {
        syslog($prio, __fmt($msg));
    }

    function __destruct() {
        closelog();
    }

}

class DatabaseLoggerFactory extends LoggerFactory {

    function __logMessage($prio, $msg) {
        
    }

}

class EventLoggerFactory extends LoggerFactory {

    function __logMessage($prio, $msg) {
        event::invoke(debug::EVT_DEBUG, array($prio,$msg));
    }

}

class ConsoleLoggerFactory extends LoggerFactory {

    private static $level = array(
        'Base','Emerge','Alert','Critical','Warning','Notice','Info','Debug'
    );

    function __logMessage($prio,$msg) {
        $ts = @date("M-d H:i:s", time());
        $lines = explode("\n", $msg);
        foreach ($lines as $line) {
            if (defined('STDERR'))
                fprintf(STDERR, "%s %-20s %s\n", $ts, self::$level[$prio], $line);
            else
                printf("%s %-20s %s\n", $ts, self::$level[$prio], $line);
            //fprintf(STDERR, "%s | %-10s | %s\n", $ts, self::$level[$prio-1],$line);
        }
    }

}

class FileLoggerFactory extends LoggerFactory {

    private $filename = null;

    private static $level = array(
        'BASE','EMERG','ALERT','CRIT','WARN','NOTICE','INFO','DEBUG'
    );

    function __construct($filename) {
        $this->filename = $filename;
    }

    function __logMessage($prio,$msg) {
        $ts = @date("M-d H:i:s", time());
        $lines = explode("\n", $msg);
        $fh = fopen($this->filename,'a+');
        foreach ($lines as $line) {
            fprintf($fh, "%s %-20s %s\n", $ts, self::$level[$prio], $line);
            //fprintf(STDERR, "%s | %-10s | %s\n", $ts, self::$level[$prio-1],$line);
        }
        fclose($fh);
    }

}

abstract class Logger {

    static $_loggers = array();
    static $_logger = null;

    public static function emerg($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_EMERG, __fmt($arg));
    }

    public static function alert($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_ALERT, __fmt($arg));
    }

    public static function crit($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_CRIT, __fmt($arg));
    }

    public static function err($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_ERR, __fmt($arg));
    }

    public static function warning($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_WARNING, __fmt($arg));
    }

    public static function notice($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_NOTICE, __fmt($arg));
    }

    public static function info($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_INFO, __fmt($arg));
    }

    public static function debug($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_DEBUG, __fmt($arg));
    }

    public static function log($msgfmg) {
        $arg = func_get_args();
        self::__log(LOG_INFO, __fmt($arg));
    }

    public static function registerFactory(LoggerFactory $factory) {
        foreach(self::$_loggers as $logger) {
            if (typeOf($logger) == typeOf($factory)) {
                logger::warning('Attempting to register logger %s twice',typeOf($factory));
                return;
            }
        }
        self::$_loggers[] = $factory;
    }

    private static function __log($prio, $msg) {
        if ($prio <= base::logLevel()) {
            foreach (self::$_loggers as $logger) {
                $logger->__logMessage($prio, $msg);
            }
        }
    }

    public static function logEx($prio,$msg) {
        self::__log($prio,$msg);
    }

}

///// Events /////////////////////////////////////////////////////////////////

class EventHandler {

    private $_class = null;
    private $_method = null;
    private $_uid = null;

    /**
     * Constructor
     *
     * @param Object $class The class name or a class instance
     * @param Mixed $method The method to invoke
     */
    public function __construct($class, $method) {
        $this->_class = $class;
        $this->_method = $method;
        $this->_uid = uniqid('ev', true);
    }

    /**
     * Called when the event is invoked. Normal users don't have to bother
     * with this.
     *
     * @param Mixed $event The event that is being dispatched
     * @param Array $data The data being passed to the event
     */
    public function dispatch($event, Array $data) {
        if ($this->_class) {
            if (is_string($this->_class)) {
                $ci = new $this->_class;
            } else {
                $ci = $this->_class;
            }
            return (call_user_func_array(array($ci, $this->_method), array($event, $data)) == true);
        } else {
            return (call_user_func_array($this->_method, array($event, $data)) == true);
        }
    }

    /**
     * Returns an events unique ID.
     *
     * @return Mixed The unique ID assigned to the event
     */
    public function getUniqueId() {
        return $this->_uid;
    }

}

/**
 * Manages various events
 */
abstract class Event {

    private static $_handlers = array();

    /**
     * Register an event handler
     *
     * @param Mixed $event The event to register
     * @param EventHandler $handler The EventHandler in charge of the event.
     */
    static function register($event, EventHandler $handler) {
        if (!arr::hasKey(self::$_handlers, strtolower($event))) {
            self::$_handlers[$event] = array();
        }
        self::$_handlers[$event][$handler->getUniqueId()] = $handler;
    }

    /**
     * Invoke a specif event
     *
     * @param Mixed $event The event to invoke
     * @param Array $data The data to pass to the handler
     */
    static function invoke($event, Array $data) {
        if (arr::hasKey(self::$_handlers, strtolower($event))) {
            foreach (self::$_handlers[$event] as $evt) {
                if ($evt->dispatch($event, $data) == true)
                    return true;
            }
        }
        return false;
    }

}

interface IEventList { }

abstract class CoreEvents implements IEventList {
    const EVENT_BEFORE_APPLICATION = 'lepton.application.before';
    const EVENT_AFTER_APPLICATION = 'lepton.application.after';
}

/**
 * @deprecated To be replaced by delegate
 */
class Callback {
    private $cbarray = null;
    private $cbfixed = null;
    function __construct(&$object,$method) {
        $this->cbarray = array($object,$method);
        $args = func_get_args();
        if (count($args)>2) { $this->cbfixed = array_slice($args,2); }
    }
    function call() {
        $args = func_get_args();
        return call_user_func_array($this->cbarray,array_merge((array)$this->cbfixed,$args));
    }
}
function cb(callback $cb = null) {
    $args = func_get_args();
    if ($cb) call_user_func_array(array($cb,'call'),array_slice($args,1));
}
// Semantic prettification method
function callback(&$object,$method) { 
    // return array($o,$m);
    $args = func_get_args();
    return new Callback($object,array_slice($args,1));
}
////// Finalizing Bootstrap ///////////////////////////////////////////////////

if (PHP_VERSION < "5") {
    Console::warn("Lepton is running on an unsupported version of PHP. Behavior in versions prior to 5.0 may be unreliable");
}

// Initial debug output
Console::debugEx(LOG_BASIC, '(bootstrap)', "Base path: %s", BASE_PATH);
Console::debugEx(LOG_BASIC, '(bootstrap)', "System path: %s", SYS_PATH);
Console::debugEx(LOG_BASIC, '(bootstrap)', "App path: %s", APP_PATH);
Console::debugEx(LOG_BASIC, '(bootstrap)', "Include path: %s", get_include_path());
Console::debugEx(LOG_BASIC, '(bootstrap)', "Platform: PHP v%d.%d.%d (%s)", PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION, PHP_OS);
Console::debugEx(LOG_BASIC, '(bootstrap)', "Running as %s (uid=%d, gid=%d) with pid %d", get_current_user(), getmyuid(), getmygid(), getmypid());
Console::debugEx(LOG_BASIC, '(bootstrap)', "Memory allocated: %0.3f KB (Total used: %0.3f KB)", (memory_get_usage() / 1024 / 1024), (memory_get_usage(true) / 1024 / 1024));

// Load configuration settings
ModuleManager::load('defaults', false);
ModuleManager::load('app.config.*', false);

// Load application base if the $argc global variable is set
if (php_sapi_name() == 'cli') {
    ModuleManager::load('lepton.base.application');
}

function class_inherits($cn, $base) {
    if (!class_exists($cn))
        return false;
    $rc = new ReflectionClass($cn);
    $pc = $rc->getParentClass();
    if ($pc)
        return ($pc->name == $base);
    return false;
}

if (config::has('lepton.db.tableprefix')) {
    define('LEPTON_DB_PREFIX', config::get('lepton.db.tableprefix'));
} else {
    define('LEPTON_DB_PREFIX', '');
}

class LeptonInstanceScopeWatcher {
    function __destruct() {
        Console::debugEx(LOG_BASIC, '(destructor)', "Memory allocated at shutdown: %0.3f KB (Total used: %0.3f KB, By code: %0.3f KB)", (memory_get_usage() / 1024 / 1024), (memory_get_usage(true) / 1024 / 1024), ((memory_get_usage(true) - SYSTEM_INITIAL_MEMORY_USE) / 1024 / 1024));
    }
}
$__leptoninstancescope = new LeptonInstanceScopeWatcher();

using('lepton.utils.rtoptimization');
RuntimeOptimization::enable();
