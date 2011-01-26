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


    declare(ticks = 1);

    define('RETURN_SUCCESS', 0);
    define('RETURN_ERROR', 1);
    define('PI',3.1415926535897931);

    // Compatibility definitions
    foreach(array(
        'COMPAT_GETOPT_LONGOPTS' => (PHP_VERSION >= "5.3"),
        'COMPAT_SOCKET_BACKLOG' => (PHP_VERSION >= "5.3.3"),
        'COMPAT_HOST_VALIDATION' => (PHP_VERSION >= "5.2.13"),
        'COMPAT_NAMESPACES' => (PHP_VERSION >= "5.3.0"),
        'COMPAT_INPUT_BROKEN' => ((PHP_VERSION >= "5") && (PHP_VERSION < "5.3.1")),
        'COMPAT_CALLSTATIC' => (PHP_VERSION >= "5.3.0"),
        'COMPAT_CRYPT_BLOWFISH' => (PHP_VERSION >= "5.3.0"),
        'COMPAT_PHP_FNMATCH' => (PHP_OS == "Linux") || ((PHP_OS == "Windows") && (PHP_VERSION >= "5.3"))
    ) as $compat=>$val) define($compat,$val);

    if (!COMPAT_PHP_FNMATCH) {
        if (!function_exists('fnmatch')) {
            function fnmatch($pattern, $string) {
                return @preg_match(
                    '/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'),
                    array('*' => '.*', '?' => '.?')) . '$/i', $string
                );
            }
        }
    }

    // Version definitions
    foreach(array(
        'LEPTON_MAJOR_VERSION' => 1,
        'LEPTON_MINOR_VERSION' => 0,
        'LEPTON_RELEASE_VERSION' => 0,
        'LEPTON_RELEASE_TAG' => "alpha",
        'LEPTON_PLATFORM' => "Lepton Application Framework",
        'PHP_RUNTIME_OS' => php_uname('s')
    ) as $def=>$val) define($def,$val);
    define("LEPTON_VERSION", LEPTON_MAJOR_VERSION.".".LEPTON_MINOR_VERSION.".".LEPTON_RELEASE_VERSION." ".LEPTON_RELEASE_TAG);
    define("LEPTON_PLATFORM_ID", LEPTON_PLATFORM . " " . LEPTON_VERSION);

    // Platform definitions
    define('IS_WINNT', (strtolower(PHP_RUNTIME_OS) == 'windows'));
    define('IS_LINUX', (strtolower(PHP_RUNTIME_OS) == 'linux'));

    // PHP Version Definitions / Fixes
    if(!defined('PHP_VERSION_ID')) {
        $version = explode('.',PHP_VERSION);
        define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
    }
    if(PHP_VERSION_ID < 50207) {
        define('PHP_MAJOR_VERSION',    $version[0]);
        define('PHP_MINOR_VERSION',    $version[1]);
        define('PHP_RELEASE_VERSION',  $version[2]);
    }

    // Resolve application and system paths
    if(!defined('APP_PATH')) {
        if(getenv('APP_PATH')) {
            define('APP_PATH', realpath(getenv('APP_PATH')).'/');
        } else {
            if (getenv('SCRIPT_FILENAME')) {
                $path = getenv('SCRIPT_FILENAME');
                $path = realpath(pathinfo($path, PATHINFO_DIRNAME));
            } else {
                $path = getcwd();
            }
            // If in /bin, assume base is at ..
            if (substr($path,strlen($path)-4,4) == '/bin') {
                $path = $path.'/../';
            }
            if (substr($path,strlen($path)-4,4) == '/app') {
                $path = $path.'/../';
            }
            $path = $path.'/app';
            define('APP_PATH', realpath($path).'/');
        }
    } else {
        Console::warn("APP_PATH already defined and set to %s", APP_PATH);
        $path = APP_PATH;
    }

    // Resolve base path
    define('BASE_PATH', realpath(APP_PATH.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR);

    // Resolve system path
    if(getenv('SYS_PATH')) {
        define('SYS_PATH', realpath(getenv('SYS_PATH').DIRECTORY_SEPARATOR).'/');
    } else {
        $syspath = realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR;
        define('SYS_PATH', realpath($syspath).'/');
    }
    if (!defined('APP_PATH')) {
        define('APP_PATH', join(DIRECTORY_SEPARATOR,array($path,'app')).'/');
    }

    // Resolve temporary path
    if(!defined("TMP_PATH")) {
        if (file_exists(BASE_PATH.'tmp') && (is_writable(BASE_PATH.'tmp'))) {
            $tmp = BASE_PATH.'tmp';
        } else {
            $tmp = sys_get_temp_dir();
        }
        define('TMP_PATH', $tmp.'/');
    }

    // Enable PHPs error reporting when the DEBUG envvar is set
    if (getenv("DEBUG") >= 1) {
        define('DEBUGMODE',true);
        error_reporting(E_ALL);
        ini_set('display_errors','1');
    } else {
        define('DEBUGMODE',false);
    }

    if (php_sapi_name() == 'cli') {
        define('LEPTON_CONSOLE', true);
    }
    if (getenv("LOGFILE")) {
        define("LOGFILE", fopen(getenv("LOGFILE"),'a+'));
        fprintf(LOGFILE,"\n --- MARK --- \n\n");
    } else {
        define("LOGFILE", null);
    }

    define('LOG_DEBUG2', 5);
    define('LOG_DEBUG1', 4);
    define('LOG_EXTENDED', 3);
    define('LOG_VERBOSE', 2);
    define('LOG_BASIC', 1);
    define('LOG_WARN', 0);
    define('LOG_LOG', 0);

    abstract class base {
        private static $_basepath = null;
        private static $_apppath = null;
        private static $_syspath = null;
        static function basePath($newpath=null) {
            $ret = (self::$_basepath)?self::$_basepath:BASE_PATH;
            if ($newpath) {
                self::$_basepath = realpath($newpath);
                console::debug("Setting base path: %s", self::$_basepath);
            }
            return $ret;
        }
        static function appPath($newpath=null) {
            $ret = (self::$_apppath)?self::$_apppath:APP_PATH;
            if ($newpath) self::$_apppath = realpath($newpath);
            return $ret;
        }
        static function sysPath($newpath=null) {
            $ret = (self::$_syspath)?self::$_syspath:SYS_PATH;
            if ($newpath) self::$_syspath = realpath($newpath);
            return $ret;
        }
    }
    abstract class utils {
        static function iif($cond,$true,$false) {
            return ($cond)?$true:$false;
        }
        static function ifnull($cond,$value) {
            return ($cond==null)?$value:$cond;
        }
        static function inPath($path,$parent) {
            $preal = realpath($path);
            return (substr(strtolower($preal),0,strlen($parent)) == strtolower($parent));
        }
    }

////// Interfaces /////////////////////////////////////////////////////////////

    interface IDataConsumer {
        function setData($data);
        function checkData($data);
    }
    interface IDataProvider {
        function getData();
    }

    abstract class DataConsumer implements IDataConsumer { }
    abstract class DataProvider implements IDataProvider { }

////// Utility Functions and Aliases //////////////////////////////////////////

    function dequote($str) {
        $str = trim($str);
        $qt = $str[0];
        if (($qt == '"') || ($qt == "'" )) {
            if ($str[strlen($str)-1] == $qt) {
                return substr($str,1,strlen($str)-2);
            }
        }
    }

    function __fileinfo($strinfo,$vars=null) {
        if (count(ModuleManager::$_order) > 0) {
            $mod = ModuleManager::$_order[count(ModuleManager::$_order) - 1];
            ModuleManager::$_modules[$mod]['modinfo'] = $strinfo;
            if ($vars!=null) {
                foreach($vars as $key=>$var) {
                    ModuleManager::$_modules[$mod][$key] = $var;
                }
                // Load dependencies
                if (isset($vars['depends']) && is_array($vars['depends'])) {
                    $deps = (array)$vars['depends'];
                    foreach($vars['depends'] as $dep) {
                        ModuleManager::load($dep);
                    }
                }
            }
        } else {
            Console::warn("Module reported modinfo '%s' without being requested?", $string);
        }
    }

    function using($mod) {
        ModuleManager::load($mod);
    }

    function __fmt($args=null) {
        if (count($args) == 0) {
            return "";
        } else if (count($args) == 1) {
            return $args[0];
        } else {
            return call_user_func_array('sprintf',$args);
        }
    }

    function __deprecated($oldfunc,$newfunc = null) {

        $stack = debug_backtrace(false);
        $method = $stack[1];
        if (!isset($method['file'])) {
            $caller = sprintf("%s%s%s (%s:%d)", $method['class'], $method['type'], $method['function'], '???', 0);
        } else {
            if (isset($method['type'])) {
                $caller = sprintf("%s%s%s (%s:%d)", $method['class'], $method['type'], $method['function'], str_replace(SYS_PATH,'',$method['file']), $method['line']);
            } else {
                $caller = sprintf("%s (%s:%d)", $method['function'],  str_replace(SYS_PATH,'',$method['file']), $method['line']);
            }
        }

        // todo: add strict option to make deprecation warnings fatal

        if ($newfunc) {
            logger::warning('%s: Function %s is deprecated in favor of %s',$caller, $oldfunc, $newfunc);
            $msg = sprintf('%s: Function %s is deprecated in favor of %s',$caller, $oldfunc, $newfunc);
        } else {
            logger::warning('%s. Function %s is deprecated', $caller, $oldfunc);
            $msg = sprintf('%s. Function %s is deprecated', $caller, $oldfunc);
        }

        if (config::get('lepton.base.strict',false) == true) {
            throw new BaseException($msg);
        }

    }
    
    function __fileext($filename) {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }

    function __strip_newline($str) {
        $str = str_replace("\r","",$str);
        $str = str_replace("\n","",$str);
        return $str;
    }

////// Exceptions /////////////////////////////////////////////////////////////

    /*
        Exception classes. Should all be derived from BaseException
    */
    class BaseException extends Exception { }
        class FilesystemException extends BaseException { }
            class FileNotFoundException extends FilesystemException { }
            class FileAccessException extends FilesystemException { }
        class UnsupportedPlatformException extends BaseException { }
        class SystemException extends BaseException { }
        class ClassNotFoundException extends BaseException { }
        class BadPropertyException extends BaseException { }
        class BadArgumentException extends BaseException { }
        class CriticalException extends BaseException { }
            class SecurityException extends CriticalException { }



/*
    function __autoload($class) {
        if (class_exists($class)) return;
        if (PHP_VERSION_ID < 50300) {
            console::backtrace();
            console::fatal('Could not load class %s!', $class);
            die(1);
        } else {
            throw new ClassNotFoundException('Could not load class '.$class);
        }
    }
*/

////// Configuration //////////////////////////////////////////////////////////

    /**
     * @class Config
     * @brief Configuration management
     *
     * This is a light re-implementation to give global access to config-
     * uration values.
     * 
     * @author Christoper Vagnetoft <noccy@chillat.net>
     */
    abstract class Config {
        static $values = array();

        /**
         * @brief Get a configuration value (or an array of them).
         * Call with the key you want to query, or with a wildcard.
         *
         * @param String $key The key to query
         * @param Mixed $default The value to return if key is empty
         * @return Mixed The value or the default value.
         */
        static function get($key,$default=null) {
            if (strpos($key,'*') !== false) {
                $ol = array();
                foreach(Config::$values as $ckey=>$val) {
                    if (preg_match('/'.str_replace('*','.*',$key).'/',$ckey)) {
                        $ol[$ckey] = $val;
                    }
                }
                return $ol;
            } else {
                if (isset(Config::$values[$key])) {
                    return Config::$values[$key];
                } else {
                    return $default;
                }
            }
        }

        /**
         * @brief Set a configuration value.
         *
         * @param String $key The key to set
         * @param Mixed $value The value to set
         */
        static function set($key,$value) {
            Config::$values[$key] = $value;
        }

        /**
         * @brief Push the value onto a configuration key
         *
         * @param String $key The key to update
         * @param Mixed $value The value to push
         */
        static function push($key,$value) {
            if (isset(Config::$values[$key])) {
                $old = (array)Config::$values[$key];
            } else {
                $old = array();
            }
            $old[] = $value;
            Config::$values[$key] = $old;
        }

        /**
         * @brief Check if a key is set.
         *
         * @param String $key The key to check
         * @return Bool True if the key is set
         */
        static function has($key) {
            return (isset(Config::$values[$key]));
        }

        /**
         * @brief Set a default value.
         * Will only change the value if it's not set.
         *
         * @param String $key The key to set
         * @param Mixed $default The default to set if empty
         */
        static function def($key,$default) {
            if (!isset(Config::$values[$key])) Config::$values[$key] = $default;
        }

        /**
         * @brief Clear a configuration key
         *
         * @param String $key The key to clear
         */
        static function clr($key) {
            if (strpos($key,'*') !== false) {
                $kv = array();
                foreach(Config::$values as $ckey=>$val) {
                    if (preg_match('/'.str_replace('*','.*',$key).'/',$ckey)) {
                        unset(config::$values[$ckey]);
                        $kv[] = $ckey;
                    }
                }
                return $kv;
            } else {
                unset(config::$values[$key]);
                return $key;
            }
        }

    }

    function __fromprintable($str) {
        if (in_array($str[0],array('"',"'"))) {
            $qc = $str[0];
            if ($str[strlen($str)-1] == $qc) {
                return substr($str,1,strlen($str)-2);
            }
        }
        switch($str) {
            case 'NULL':
                return NULL;
            case 'false':
                return false;
            case 'true':
                return true;
            default:
                return $str;
        }
    }

    function __printable($var) {
        if (is_null($var)) {
            return "NULL";
        } elseif (is_bool($var)) {
            return ($var)?'true':'false';
        } elseif (is_string($var)) {
            return '"'.$var.'"';
        } else {
            return $var;
        }

    }

////// Structures /////////////////////////////////////////////////////////////

    class BasicList implements IteratorAggregate {

        private $list;
        private $typeconst;

        public function __construct($typeconst=null) {
            $this->type = $typeconst;
        }

        public function getIterator() {
            return new ArrayIterator((array)$this->list);
        }

        public function add($item) {
            if ($this->type) {
                if (!is_a($item,$this->type)) {
                    throw new BaseException("Error; Pushing invalid type with add(). ".$item." is not a ".$this->type);
                }
            }
            $this->list[] = $item;
        }

        public function item($index) {
            return $this->list[$index];
        }

        public function find($item) {
            return (in_array($item,$this->list));
        }

        public function count() {
            return count($this->list);
        }

    }

    class BasicContainer {

        protected $propertyvalues = array();

        function __construct() {
            if (!isset($this->properties)) {
                throw new RuntimeException("BasicContainer descendant doesn't have a protected variable properties");
            }
        }

        function __get($property) {
            if (isset($this->properties[$property]) || ($this->properties[$property] === null)) {
                return $this->properties[$property];
            } else {
                throw new BadPropertyException("No such property: $property");
            }
        }

        function __set($property,$value) {
            if (isset($this->properties[$property]) || ($this->properties[$property] === null)) {
                if (is_array($this->properties[$property]) &&
                    (!is_array($value))) {
                    throw new RuntimeException("Attempting to assign non-array to array property");
                }
                $this->properties[$property] = $value;
            } else {
                throw new BadPropertyException("No such property: $property");
            }
        }

        function __isset($property) {
            return (isset($this->properties[$property]));
        }

    }

////// Console ////////////////////////////////////////////////////////////////

    /**
     * @class Console
     * @brief Console management function
     */
    class Console {
        /**
         *
         */
        static function debugEx($level,$module) {
            $args = func_get_args();
            $strn = @call_user_func_array('sprintf',array_slice($args,2));
            $ts = Console::ts();
            $lines = explode("\n",$strn);
            foreach($lines as $line) {
                if (getenv("DEBUG") >= $level) {
                    if (DEBUGMODE):
                        fprintf(STDERR,"%s %-20s %s\n", $ts, $module,$line);
                    else:
                        fprintf(STDERR,"[%s] %s\n", $module,$line);
                    endif;
                }
                if (LOGFILE) fprintf(LOGFILE,"%s | %-20s | %s\n", $ts, $module, $line);
            }
        }

        /**
         *
         */
        static function debug() {
            $args = func_get_args();
            @call_user_func_array(array('Console','debugEx'),array_merge(array(LOG_DEBUG1,'Debug'),array_slice($args,0)));
        }

        /**
         *
         */
        static function warn() {
            $args = func_get_args();
            @call_user_func_array(array('logger','warn'), $args);
            @call_user_func_array(array('Console','debugEx'),array_merge(array(LOG_WARN,'Warning'),array_slice($args,0)));
        }

        /**
         *
         */
        static function fatal() {
            $args = func_get_args();
            @call_user_func_array(array('logger','emerg'), $args);
            @call_user_func_array(array('Console','debugEx'),array_merge(array(LOG_LOG,'Fatal'),array_slice($args,0)));
            die(RETURN_ERROR);
        }

        /**
         *
         */
        static function error() {
            $args = func_get_args();
            call_user_func_array('fprintf',array_merge(array(STDERR,$args[0]."\n"),array_slice($args,1)));
        }

        /**
         *
         */
        static function backtrace($trim=1,$stack=null,$return=false) {
            if (!$stack) { $stack = debug_backtrace(false); }
            $trace = array();
            foreach($stack as $i=>$method) {
                $args = array();
                if ($i > ($trim - 1)) {
                    if (isset($method['args'])) {
                        foreach($method['args'] as $arg) {
                            $args[] = gettype($arg);
                        }
                    }
                    $mark = (($i == ($trim))?'in':'  invoked from');
                    if (!isset($method['file'])) {
                        if (isset($method['type'])) {
                            $trace[] = sprintf("  %s %s%s%s(%s) - %s:%d", $mark, $method['class'], $method['type'], $method['function'], join(',',$args), '???', 0);
                        } else {
                            $trace[] = sprintf("  %s %s(%s) - %s:%d", $mark, $method['function'], join(',',$args), '???', 0);
                        }
                    } else {
                        if (isset($method['type'])) {
                            $trace[] = sprintf("  %s %s%s%s(%s) - %s:%d", $mark, $method['class'], $method['type'], $method['function'], join(',',$args), str_replace(SYS_PATH,'',$method['file']), $method['line']);
                        } else {
                            $trace[] = sprintf("  %s %s(%s) - %s:%d", $mark, $method['function'], join(',',$args), str_replace(SYS_PATH,'',$method['file']), $method['line']);
                        }
                    }
                }
            }
            if ($return) return join("\n", $trace)."\n";
            Console::debugEx(LOG_WARN,'Backtrace', "%s", join("\n", $trace)."\n");
            if (LOGFILE) fprintf(LOGFILE, join("\n", $trace)."\n");
        }

        /**
         *
         */
        static function write() {
            $args = func_get_args();
            if (count($args)>0) {
                if (count($args)>1) {
                    $strn = @call_user_func_array('sprintf',array_slice($args,0));
                } else {
                    $strn = $args[0];
                }
            } else {
                $strn = "";
            }
            printf("%s",$strn);
            if (LOGFILE) fprintf(LOGFILE, $strn);
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
            if (IS_LINUX) { system('stty -echo'); }
            $ld = fgets(STDIN);
            $ld = __strip_newline($ld);
            if (IS_LINUX) { system('stty echo'); }
            console::write("\n");
            return $ld;
        }

        static function writeLn() {
            $args = func_get_args();
            if (count($args)>0) {
                if (count($args)>1) {
                    $strn = @call_user_func_array('sprintf',array_slice($args,0));
                } else {
                    $strn = $args[0];
                }
            } else {
                $strn = "";
            }
            printf("%s\n",$strn);
            if (LOGFILE) fprintf(LOGFILE, $strn . "\n");
        }

        /**
         *
         */
        static function status() {
            $args = func_get_args();
            $strn = @call_user_func_array('sprintf',array_slice($args,0));
            printf("%s",$strn . str_repeat(chr(8),strlen($strn)));
            if (LOGFILE) fprintf(LOGFILE, $strn);
        }

        /**
         *
         */
        static function ts() {
            return @date("M-d H:i:s",time());
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
            if ($start) $this->start();
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
                return (microtime(t1rue) - $this->_starttime);
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

        static $__exceptionhandler = null;
        static $mimetypes = array(
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'css'  => 'text/css',
            'js'   => 'text/javascript',
            'html' => 'text/html',
            'otf'  => 'application/x-opentype'
        );

        static function applicationExists() {
            return (file_exists(APP_PATH));
        }

        /**
         *
         */
        function run($class) {
            static $ic = 0;
            $args = func_get_args();
            $args = array_slice($args,1);
            Console::debugEx(LOG_EXTENDED,__CLASS__,"Inspecting environment module state:\n%s", ModuleManager::debug());
            $ic++;
            if (class_exists($class)) {
                $rv = 0;
                $apptimer = new Timer();
                try {
                    $instance = new $class();
                    Console::debugEx(LOG_BASIC,__CLASS__,"Invoking application instance from %s.", $class);
                    $apptimer->start();
                    $rv = call_user_func_array(array($instance,'run'),$args);
                    $apptimer->stop();
                    unset($instance);
                    Console::debugEx(LOG_BASIC,__CLASS__,"Main method exited with code %d after %.2f seconds.", $rv, $apptimer->getElapsed());
                } catch (Exception $e) {
                    throw $e;
                }
                $ic--;
                if ($ic == 0) return( $rv );
            } else {
                $ic--;
                if ($ic == 0) {
                    Console::warn('FATAL: Application class %s not found!', $class);
                    exit( RETURN_ERROR );
                } else {
                    Console::warn('Application class %s not found!', $class);
                }

            }
        }

        function using($module) {
            Console::warn("Lepton::using() is deprecated (%s)", $module);
            ModuleManager::load($module);
        }
        function autoload($module, $as) {
            Console::warn("Lepton::autoload() is deprecated (%s => %s)", $module, $as);
        }

        /**
         *
         */
        function getMimeType($filename) {
            $file = escapeshellarg( BASE_PATH.$filename );
            return str_replace("\n","",shell_exec("file -b --mime-type " . $file));
        }
        
        function getServerHostname() {
            if (isset($_SERVER['hostname'])) {
                return $_SERVER['hostname'];
            } else {
                return basename($_SERVER['SCRIPT_NAME']);
            }
        }
        
        /**
         *
         */
        function setExceptionHandler($handler,$override=false) {
            if (($override == true) || (Lepton::$__exceptionhandler == null)) {
                Lepton::$__exceptionhandler = $handler;
                Console::debugEx(LOG_BASIC,__CLASS__,"Assigned exception handler: %s", $handler);
            } else {
                Console::debugEx(LOG_BASIC,__CLASS__,"Ignoring exception handler: %s", $handler);
            }            
        }

        /**
         *
         */
        static function handleException(Exception $e) {
            if (Lepton::$__exceptionhandler) {
                $eh = new Lepton::$__exceptionhandler();
                $eh->exception($e);
            } else {
                printf($e);
                die("Unhandled exception and no exception handler loaded.");
            }
        }
        
        /**
         * @brief Handle shutdown (for debugging and error reporting)
         */
        static function handleShutdown() {
            $error = error_get_last(); 
            if (($error['type'] == 1) && (defined('LEPTON_CONSOLE'))) { 
				$f = file($error['file']);
				foreach($f as $i=>$line) {
					$mark = (($i+1) == $error['line'])?'=> ':'   ';
					$f[$i] = sprintf('  %05d. %s',$i+1,$mark).$f[$i];
					$f[$i] = str_replace("\n","",$f[$i]);
				}
				$first = $error['line'] - 4; if ($first < 0) $first = 0;
				$last = $error['line'] + 3; if ($last >= count($f)) $last = count($f)-1;
				$source = join("\n",array_slice($f,$first,$last-$first));
				echo "\n".$source."\n";
				die();
            } 
        }

    }

    set_exception_handler( array('Lepton','handleException') );
    register_shutdown_function( array('Lepton','handleShutdown') ); 


    interface IExceptionHandler {
        function exception(Exception $e);
    }
    
    abstract class ExceptionHandler implements IExceptionHandler { 
    
    }

////// Application Base ///////////////////////////////////////////////////////

    interface IApplication {
        function run();
    }

    abstract class Application implements IApplication {

    }

////// Variable Casting / Utils ///////////////////////////////////////////////

	abstract class string {
		
		const CHS_ALPHA='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-';
		const CHS_NUMERIC='0123456789';

                const KEY_CHARSET='lepton.string.charset';
		
		static function strip($string,$charset) {
			$out = '';
			for ($ci = 0; $ci<strlen($string); $ci++) {
				if (strpos($charset,$string[$ci]) !== false) {
					$out.=$string[$ci];
				}
			}
			return $out;
		}
		
		static function replace($str,$find,$replace) {
		    return str_replace($find,$replace,$str);
		}
		
		static function rereplace($str,$find,$replace) {
		    return preg_replace($find,$replace,$str);
		}
		
        static function truncate($string,$maxlen) {
            if (strlen($string)>$maxlen) {
                return substr($string,0,$maxlen).'...';
            }
            return $string;
        }

		static function slug($string) {
                        $s = strToLower($string);
                        $charset = strToUpper(config::get(self::KEY_CHARSET, 'utf-8'));
                        $s = @iconv($charset, 'ASCII//TRANSLIT', $s);
                        $s = preg_replace('/[^a-z0-9]/','-',$s);
                        $s = preg_replace('/-{2,}/','-',$s);
                        return $s;
		}
		
		static function cast($var) {
			return strval($var);
		}
		
	}

	abstract class integer {
	
		static function cast($var) {
			return intval($var);
		}

        static function restrict($val,$min,$max) {
            return ($val>=$max)?$max:($val<=$min)?$min:$val;
        }
	
	}
	
	abstract class float {
	
		static function cast($var) {
			return floatval($var);
		}
		
	}
	
	abstract class arr {
	
		static function apply($dest,$array) {
			foreach($array as $k=>$v) {
				$dest[$k] = $v;
			}
			return $dest;
		}
		
		static function bucketize(array $array,$index) {
		    $ret = array();
		    foreach($array as $item) {
		        $ret[$item[$index]][] = $item;
		    }
		    return $ret;
		}

		static function hasKey(Array $arr,$key) {
			return array_key_exists($key,$arr);
		}

		static function hasValue(Array $arr,$key) {
			return in_array($key,$arr);
		}

	}


////// ModuleManager //////////////////////////////////////////////////////////

    /**
     * @class ModuleManager
     *
     */
    class ModuleManager {

        static $_modules;
        static $_order;

        /**
         *
         */
        static function checkExtension($extension,$required=false) {
            if( extension_loaded($extension) ) {
                return true;
            }
            Console::debug("PHP extension not loaded: %s", $extension);
            $filename = (PHP_SHLIB_SUFFIX === 'dll' ? 'php_' : '') . $extension . PHP_SHLIB_SUFFIX;
            Console::debug("Attempting a manual load of %s from %s", $extension, $filename);
            if (function_exists('dl') && !@dl($filename) && ($required) ) {
                Console::warn("Dynamic loading of extensions disabled and extension %s flagged as required. Please load it manually or enable the dl() function.", $extension);
                exit(1);
            }
            return (extension_loaded($extension));
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
            if (preg_match('/^app\./',$module)) {
                $path = base::appPath().'/'.str_replace('.','/',str_replace('app.','',$module)).'.php';
            } else {
                $path = base::sysPath().'/'.str_replace('.','/',$module).'.php';
            }
            // Console::debugEx(LOG_LOG,__CLASS__,"  -> %s", $path);
            return $path;
        }

        /**
         *
         */
        static function load($module,$optional=false) {
            if (strpos($module,'*') == (strlen($module) - 1)) {
                $path = self::_mangleModulePath($module);
                Console::debugEx(LOG_EXTENDED,__CLASS__,"Looking for modules matching %s from %s", $module, $path);
                $f = glob($path);
                sort($f);
                $failed = false;
                foreach($f as $file) {
                    if (!ModuleManager::load(str_replace('*',basename($file,'.php'),$module))) $failed=true;
                }
                return (!$failed);
            }
            if (ModuleManager::has($module)) {
                Console::debugEx(LOG_EXTENDED,__CLASS__,"Already loaded %s.",$module);
                return true;
            }
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
            if (($path) && (file_exists($path))) {
                Console::debugEx(LOG_BASIC,__CLASS__,"Loading %s (%s).",$module,str_replace(BASE_PATH,'',$path));
                try {
                    ModuleManager::$_modules[strtolower($module)] = array();
                    ModuleManager::$_order[] = strtolower($module);
                    // Console::debugEx(LOG_DEBUG2,__CLASS__,"  path = %s", $path);
                    require($path);
                    array_pop(ModuleManager::$_order);
                } catch(ModuleException $e) {
                    return false;
                }
                return true;
            } else {
                Console::debugEx(LOG_BASIC,__CLASS__,"Failed to load %s.",$module);
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
            foreach(ModuleManager::$_modules as $mod=>$meta) {
                if ($mod == $module) {
                    Console::warn("Requested module %s conflicts with the loaded module %s", $module, ModuleManager::$_order[count(ModuleManager::$_order)-1]);
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
            foreach(ModuleManager::$_modules as $mod=>$meta) {
                if ($mod == $module) return true;
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
            foreach(ModuleManager::$_modules as $mod=>$meta) {
                // TODO: Show metadata
                $mi = "n/a";
                $ver = null;
                if (isset($meta['modinfo'])) $mi = $meta['modinfo'];
                if (isset($meta['version'])) $ver = 'v'.$meta['version'];
                $modinfo[] = sprintf("    %s - %s", $mod, $mi.(isset($ver)?' '.$ver:''));
            }
            return join("\n", $modinfo)."\n";
        }

    }

////// KeyStore ///////////////////////////////////////////////////////////////

    abstract class KeyStore {
        static $_data;
        function register($key,$data) {
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
        return (string)(new KeyStoreRequest($key));
    }

////// Logging Functionality //////////////////////////////////////////////////

    interface ILoggerFactory {
        function __logMessage($priority,$message);
    }

    abstract class LoggerFactory implements ILoggerFactory {

    }

    class SyslogLoggerFactory extends LoggerFactory {
        private $verbose;
        private $logger;
        function __construct($perror=false, $facility=LOG_LOCAL0) {
            $this->verbose = $perror;
            $flag = LOG_PID;
            if ($perror) $flag |= LOG_PERROR;
            $this->logger = openlog(Lepton::getServerHostname(), $flag, $facility);
        }
        function __logMessage($prio,$msg) {
            syslog($prio,$msg);
        }
        function __destruct() {
            closelog();
        }
    }

    class DatabaseLoggerFactory extends LoggerFactory {
        function __logMessage($prio,$msg) {

        }
    }

    abstract class Logger {

        static $_loggers = array();
        static $_logger = null;

        public static function emerg($msgfmt)    { $arg = func_get_args(); self::__log(LOG_EMERG,   __fmt($arg)); }
        public static function alert($msgfmt)    { $arg = func_get_args(); self::__log(LOG_ALERT,   __fmt($arg)); }
        public static function crit($msgfmt)     { $arg = func_get_args(); self::__log(LOG_CRIT,    __fmt($arg)); }
        public static function err($msgfmt)      { $arg = func_get_args(); self::__log(LOG_ERR,     __fmt($arg)); }
        public static function warning($msgfmt)  { $arg = func_get_args(); self::__log(LOG_WARNING, __fmt($arg)); }
        public static function notice($msgfmt)   { $arg = func_get_args(); self::__log(LOG_NOTICE,  __fmt($arg)); }
        public static function info($msgfmt)     { $arg = func_get_args(); self::__log(LOG_INFO,    __fmt($arg)); }
        public static function debug($msgfmt)    { $arg = func_get_args(); self::__log(LOG_DEBUG,   __fmt($arg)); }
        public static function log($msgfmg)      { $arg = func_get_args(); self::__log(LOG_INFO,    __fmt($arg)); }
        public static function registerFactory(LoggerFactory $factory) {
            self::$_loggers[] = $factory;
        }
        private static function __log($prio,$msg) {
            foreach(self::$_loggers as $logger) {
                $logger->__logMessage($prio,$msg);
            }
        }
    }

////// Debugging Foundation ///////////////////////////////////////////////////

    /**
     * Debugging Foundation. Gives access to handy debug functions.
     *
     */
    class Debug {
        /**
         * Enable error reporting
         *
         * @param bool $notices Set to false to hide notices
         */
        static function enable($notices = true) {
            error_reporting(E_ERROR | E_WARNING | E_PARSE | (($notices)?E_NOTICE:0));
        }
        /**
         *
         *
         */
        static function disable() {
            error_reporting(0);
        }

        static function inspect(array $array) {
            echo '<style type="text/css">';
            echo 'table { font:12px sans-serif; border-collapse:collapse; border:solid 1px #BBB; width:100%; }';
            echo 'th { text-align:left; padding:3px; border:solid 1px #BBB; background-color:#EEE; width:10%; }';
            echo 'td { padding:3px; border:solid 1px #BBB}';
            echo '</style>';
            echo debug::inspectArray($array);
            die();
        }

        static function inspectArray($data) {
            $ret = '<table>';
            foreach($data as $key=>$value) {
                $ret.='<tr><th>'.htmlentities($key).'</th><td>';
                if (is_array($value)) {
                    $ret.= debug::inspectArray($value);
                } else {
                    $ret.= htmlentities($value);
                }
                $ret.='</td></tr>';
            }
            $ret.= '</table>';
            return $ret;
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
		public function __construct($class,$method) {
			$this->_class = $class;
			$this->_method = $method;
			$this->_uid = uniqid('ev',true);
		}

		/**
		 * Called when the event is invoked. Normal users don't have to bother
		 * with this.
		 *
		 * @param Mixed $event The event that is being dispatched
		 * @param Array $data The data being passed to the event
		 */
		public function dispatch($event,Array $data) {
			if (is_string($this->_class)) {
				$ci = new $this->_class;
			} else {
				$ci = $this->_class;
			}
			return (call_user_func_array(array($ci,$this->_method), array($event,$data)) == true);
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
		static $_handlers = array();

		/**
		 * Register an event handler
		 *
		 * @param Mixed $event The event to register
		 * @param EventHandler $handler The EventHandler in charge of the event.
		 */
		function register($event, EventHandler $handler) {
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
		function invoke($event,Array $data) {
			if (arr::hasKey(self::$_handlers, strtolower($event))) {
				foreach(self::$_handlers[$event] as $evt) {
					if ($evt->dispatch($event,$data) == true) return true;
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

////// Finalizing Bootstrap ///////////////////////////////////////////////////

    if (PHP_VERSION < "5") {
        Console::warn("Lepton is running on an unsupported version of PHP. Behavior in versions prior to 5.0 may be unreliable");
    }

    // Initial debug output
    Console::debugEx(LOG_BASIC,'(bootstrap)',"Base path: %s", BASE_PATH);
    Console::debugEx(LOG_BASIC,'(bootstrap)',"System path: %s", SYS_PATH);
    Console::debugEx(LOG_BASIC,'(bootstrap)',"App path: %s", APP_PATH);
    Console::debugEx(LOG_BASIC,'(bootstrap)',"Include path: %s", get_include_path());
    Console::debugEx(LOG_BASIC,'(bootstrap)',"Platform: PHP v%d.%d.%d (%s)", PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION, PHP_OS);
    Console::debugEx(LOG_BASIC,'(bootstrap)',"Running as %s (uid=%d, gid=%d) with pid %d", get_current_user(), getmyuid(), getmygid(), getmypid());
    Console::debugEx(LOG_BASIC,'(bootstrap)',"Memory allocated: %0.3f KB (Total used: %0.3f KB)", (memory_get_usage() / 1024 / 1024), (memory_get_usage(true) / 1024 / 1024));

    // Load configuration settings
    ModuleManager::load('defaults',false);
    ModuleManager::load('app.config.*',false);

    // Load application base if the $argc global variable is set
    if (php_sapi_name() == 'cli') {
        ModuleManager::load('lepton.base.application');
    }

    function class_inherits($cn,$base) {
        if (!class_exists($cn)) return false;
        $rc = new ReflectionClass($cn);
        $pc = $rc->getParentClass();
        if ($pc) return ($pc->name == $base);
        return false;
    }

    if (config::has('lepton.db.tableprefix')) {
        define('LEPTON_DB_PREFIX',config::get('lepton.db.tableprefix'));
    } else {
        define('LEPTON_DB_PREFIX','');
    }

    using('lepton.utils.rtoptimization');
    RuntimeOptimization::enable();
