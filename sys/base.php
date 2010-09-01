<?php

	declare(ticks = 1);
/*
	class __lepton_rt {
		static $_ltrace = null;
		function profile() {
			static $trace = null;
			if ($trace == null) {
				$trace = debug_backtrace(false);
				$trace = array_slice($trace,1);
				var_dump($trace);
			}
		}
	}
	register_tick_function(array('__lepton_rt','profile'));
*/

	define("LEPTON_MAJOR_VERSION", 2);
	define("LEPTON_MINOR_VERSION", 0);
	define("LEPTON_RELEASE_VERSION", 0);
	define("LEPTON_RELEASE_TAG", "alpha");
	define("LEPTON_VERSION", LEPTON_MAJOR_VERSION.".".LEPTON_MINOR_VERSION.".".LEPTON_RELEASE_VERSION." ".LEPTON_RELEASE_TAG);
	define("LEPTON_PLATFORM_ID", "Lepton Application Framework " . LEPTON_VERSION);

	foreach(array(
		'COMPAT_GETOPT_LONGOPTS' => (PHP_VERSION >= "5.3"),
		'COMPAT_SOCKET_BACKLOG' => (PHP_VERSION >= "5.3.3"),
		'COMPAT_HOST_VALIDATION' => (PHP_VERSION >= "5.2.13"),
		'COMPAT_NAMESPACES' => (PHP_VERSION >= "5.3.0"),
		'COMPAT_INPUT_BROKEN' => ((PHP_VERSION >= "5") && (PHP_VERSION < "5.3.1")),
		'COMPAT_CALLSTATIC' => (PHP_VERSION >= "5.3.0"),
		'COMPAT_CRYPT_BLOWFISH' => (PHP_VERSION >= "5.3.0")
	) as $compat=>$val) define($compat,$val);


	define("PHP_RUNTIME_OS", php_uname('s'));
	define("IS_WINNT", (strtolower(PHP_RUNTIME_OS) == 'windows'));
	define("IS_LINUX", (strtolower(PHP_RUNTIME_OS) == 'linux'));

	if(!defined('PHP_VERSION_ID')) {
		$version = explode('.',PHP_VERSION);
		define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
	}
	if(PHP_VERSION_ID < 50207) {
		define('PHP_MAJOR_VERSION',	$version[0]);
		define('PHP_MINOR_VERSION',	$version[1]);
		define('PHP_RELEASE_VERSION',  $version[2]);
	}

	if(getenv("APP_PATH")) define('APP_PATH', getenv("APP_PATH"));
	if(defined('APP_PATH')) {
		$path = APP_PATH;
	} elseif (getenv("SCRIPT_FILENAME")) {
		$path = getenv('SCRIPT_FILENAME');
		$path = pathinfo($path, PATHINFO_DIRNAME);
	} else {
		$path = getcwd();
	}
/*
	if (!$path) {
		$path = dirname(__FILE__).'/../';
	}
	if (!$path) throw new Exception('Failed to get script path!');
*/
	define('BASE_PATH', realpath($path.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);
	// set_include_path( BASE_PATH . PATH_SEPARATOR . get_include_path() );
	if(getenv('SYS_PATH')) {
		define('SYS_PATH', getenv('SYS_PATH').DIRECTORY_SEPARATOR);
	} else {
		$syspath = realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR;
		define('SYS_PATH', $syspath);
	}
	define('APP_PATH', join(DIRECTORY_SEPARATOR,array($path,'app')).'/');

	// Enable PHPs error reporting when the DEBUG envvar is set
	if (getenv("DEBUG") >= 1) {
		error_reporting(E_ALL);
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
			return (isset(Config::$values[$key])?Config::$values[$key]:$default);
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
			unset(Config::$values[$key]);
		}

	}

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
					fprintf(STDERR,"%s [%s] %s\n", $ts, $module,$line);
				}
				if (LOGFILE) fprintf(LOGFILE,"%s [%s] %s\n", $ts, $module, $line);
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
					if (isset($method['type'])) {
						$trace[] = sprintf("  %s %s%s%s(%s) - %s:%d", $mark, $method['class'], $method['type'], $method['function'], join(',',$args), str_replace(SYS_PATH,'',$method['file']), $method['line']);
					} else {
						$trace[] = sprintf("  %s %s(%s) - %s:%d", $mark, $method['function'], join(',',$args), str_replace(SYS_PATH,'',$method['file']), $method['line']);
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
			printf($strn);
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
			printf($strn . "\n");
			if (LOGFILE) fprintf(LOGFILE, $strn . "\n");
		}

		/**
		 *
		 */
		static function status() {
			$args = func_get_args();
			$strn = @call_user_func_array('sprintf',array_slice($args,0));
			printf($strn . str_repeat(chr(8),strlen($strn)));
			if (LOGFILE) fprintf(LOGFILE, $strn);
		}

		/**
		 *
		 */
		static function ts() {
			return @date("y-M-d H:i:s",time());
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

	/**
	 * @class Lepton
	 */
	class Lepton {

		static $__exceptionhandler = null;

		/**
		 *
		 */
		function run($class) {
			$args = func_get_args();
			$args = array_slice($args,1);
			Console::debugEx(LOG_EXTENDED,__CLASS__,"Inspecting environment module state:\n%s", ModuleManager::debug());
			if (class_exists($class)) {
				$rv = 0;
				$apptimer = new Timer();
				try {
					$instance = new $class();
					Console::debugEx(LOG_BASIC,__CLASS__,"Invoking application instance from %s.", $class);
					$apptimer->start();
					$rv = call_user_func_array(array($instance,'run'),$args);
					$apptimer->stop();
					Console::debugEx(LOG_BASIC,__CLASS__,"Main method exited with code %d after %.2f seconds.", $rv, $apptimer->getElapsed());
				} catch (Exception $e) {
					throw $e;
				}
				exit( $rv );
			} else {
				Console::warn('Application class %s not found!', $class);
				exit( 1 );
			}
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

	interface IApplication {
		function run();
	}

	abstract class Application implements IApplication {

	}


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
				$path = APP_PATH.str_replace('.','/',str_replace('app.','',$module)).'.php';
			} else {
				$path = SYS_PATH.str_replace('.','/',$module).'.php';
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
					ModuleManager::$_modules[strtolower($module)] = true;
					ModuleManager::$_order[] = strtolower($module);
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
				$modinfo[] = sprintf("    %s", $mod);
			}
			return join("\n", $modinfo)."\n";
		}

	}

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

?>
