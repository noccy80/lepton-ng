<?php

	declare(ticks = 1);

	define('RETURN_SUCCESS', 0);
	define('RETURN_ERROR', 1);

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
	define('IS_WINNT', (strtolower(PHP_RUNTIME_OS) == 'windows'));
	define('IS_LINUX', (strtolower(PHP_RUNTIME_OS) == 'linux'));

	foreach(array(
		'COMPAT_GETOPT_LONGOPTS' => (PHP_VERSION >= "5.3"),
		'COMPAT_SOCKET_BACKLOG' => (PHP_VERSION >= "5.3.3"),
		'COMPAT_HOST_VALIDATION' => (PHP_VERSION >= "5.2.13"),
		'COMPAT_NAMESPACES' => (PHP_VERSION >= "5.3.0"),
		'COMPAT_INPUT_BROKEN' => ((PHP_VERSION >= "5") && (PHP_VERSION < "5.3.1")),
		'COMPAT_CALLSTATIC' => (PHP_VERSION >= "5.3.0"),
		'COMPAT_CRYPT_BLOWFISH' => (PHP_VERSION >= "5.3.0")
	) as $compat=>$val) define($compat,$val);

	if(!defined('PHP_VERSION_ID')) {
		$version = explode('.',PHP_VERSION);
		define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
	}
	if(PHP_VERSION_ID < 50207) {
		define('PHP_MAJOR_VERSION',	$version[0]);
		define('PHP_MINOR_VERSION',	$version[1]);
		define('PHP_RELEASE_VERSION',  $version[2]);
	}

	if(!defined("APP_PATH")) {
		if(getenv("APP_PATH")) {
			define('APP_PATH', realpath(getenv("APP_PATH")).'/');
		} else {
			if (getenv("SCRIPT_FILENAME")) {
				$path = getenv('SCRIPT_FILENAME');
				$path = realpath(pathinfo($path, PATHINFO_DIRNAME));
			} else {
				$path = getcwd();
			}
			if (substr($path,strlen($path)-4,4) == "/bin") {
				$path = $path.'/../';
			}
			$path = $path.'/app';
			define('APP_PATH', realpath($path).'/');
		}
	} else {
		Console::warn("APP_PATH already defined and set to %s", APP_PATH);
		$path = APP_PATH;
	}

	define('BASE_PATH', realpath(APP_PATH.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR);
	if(getenv('SYS_PATH')) {
		define('SYS_PATH', realpath(getenv('SYS_PATH').DIRECTORY_SEPARATOR).'/');
	} else {
		$syspath = realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR;
		define('SYS_PATH', realpath($syspath).'/');
	}
	if (!defined('APP_PATH')) {
		define('APP_PATH', join(DIRECTORY_SEPARATOR,array($path,'app')).'/');
	}
	// Enable PHPs error reporting when the DEBUG envvar is set
	if (getenv("DEBUG") >= 1) {
		define('DEBUGMODE',true);
		error_reporting(E_ALL);
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

////// Exceptions /////////////////////////////////////////////////////////////

	/*
		Exception classes. Should all be derived from BaseException
	*/
	class BaseException extends Exception { }
		class FilesystemException extends BaseException { }
			class FileNotFoundException extends FilesystemException { }
			class FileAccessException extends FilesystemException { }
		class UnsupportedPlatformException extends BaseException { }
		
		
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
		 *
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
		 *
		 */
		static function set($key,$value) {
			Config::$values[$key] = $value;
		}

		/**
		 *
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
		 *
		 */
		static function has($key,$value) {
			return (isset(Config::$values[$key]));
		}

		/**
		 *
		 */
		static function def($key,$default) {
			if (!isset(Config::$values[$key])) Config::$values[$key] = $default;
		}

		/**
		 *
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
			@call_user_func_array(array('Console','debugEx'),array_merge(array(LOG_WARN,'Warning'),array_slice($args,0)));
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
						$trace[] = sprintf("  %s %s%s%s(%s) - %s:%d", $mark, $method['class'], $method['type'], $method['function'], join(',',$args), '???', 0);
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
			$strn = @call_user_func_array('sprintf',array_slice($args,0));
			printf("%s",$strn);
			if (LOGFILE) fprintf(LOGFILE, $strn);
		}

		/**
		 *
		 */
		static function readLn() {
			return fgets(STDIN);
		}

		static function writeLn() {
			$args = func_get_args();
			$strn = @call_user_func_array('sprintf',array_slice($args,0));
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
		 *
		 */
		function start() {
			$this->_running = true;
			$this->_starttime = microtime(true);
		}

		/**
		 *
		 */
		function stop() {
			$this->_stoptime = microtime(true);
			$this->_running = false;
		}

		/**
		 *
		 */
		function getElapsed() {
			return ($this->_stoptime - $this->_starttime);
		}

	}

////// Lepton /////////////////////////////////////////////////////////////////

	/**
	 * @class Lepton
	 */
	class Lepton {

		static $__exceptionhandler = null;

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
				if ($ic == 0) exit( $rv );
			} else {
				Console::warn('Application class %s not found!', $class);
				$ic--;
				if ($ic == 0) exit( RETURN_ERROR );
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

	}

	set_exception_handler( array('Lepton','handleException') );

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

////// Meta Information ///////////////////////////////////////////////////////

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
			} 
		} else {
			Console::warn("Module reported modinfo '%s' without being requested???", $string);
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
			if ( !@dl($filename) && ($required) ) {
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
				$path = APP_PATH.'/'.str_replace('.','/',str_replace('app.','',$module)).'.php';
			} else {
				$path = SYS_PATH.'/'.str_replace('.','/',$module).'.php';
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
//			$modpath = str_replace('.','/',$module).'.php';
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
					Console::debugEx(LOG_DEBUG2,__CLASS__,"  path = %s", $path);
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


////// Finalizing Bootstrap ///////////////////////////////////////////////////

	if (PHP_VERSION < "5")
		Console::warn("Lepton is running on an unsupported version of PHP. Behavior in versions prior to 5.0 may be unreliable");

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

?>
