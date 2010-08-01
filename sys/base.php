<?php

	/*
	 * Global defines and workarounds. These ensure a consistent platform for
	 * Lepton to use.
	 *
	 *
	 */
	if(!defined('PHP_VERSION_ID')) {
		$version = explode('.',PHP_VERSION);
		define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
	}
	if(PHP_VERSION_ID < 50207) {
		define('PHP_MAJOR_VERSION',    $version[0]);
	    define('PHP_MINOR_VERSION',    $version[1]);
		define('PHP_RELEASE_VERSION',  $version[2]);
	}
	if(getenv("APP_PATH")) define('APP_PATH', getenv("APP_PATH"));
	$path = null;
	if(defined('APP_PATH')) {
		$path = APP_PATH;
	} elseif (getenv("SCRIPT_FILENAME")) {
		$path = getenv('SCRIPT_FILENAME');
		$path = pathinfo($path, PATHINFO_DIRNAME);
	} else {
		$path = getcwd();
	}
	if (!$path) throw new Exception('Failed to get script path!');
	define('BASE_PATH', $path.'/');
	if (getenv("DEBUG") == "1") {
		error_reporting(E_ALL);
	}
	if (isset($argv)) {
		define('LEPTON_CONSOLE', true);
	}
	if (getenv("LOGFILE")) {
		define("LOGFILE", fopen(getenv("LOGFILE"),'w+'));
	} else {
		define("LOGFILE", null);
	}

	class Console {
		static function debug() {
			$args = func_get_args();
			$strn = @call_user_func_array('sprintf',array_slice($args,0));
			$ts = Console::ts();
			if (getenv("DEBUG") == "1") {
				fprintf(STDERR,"[%s] %s\n", $ts, $strn);
			}
			if (LOGFILE) fprintf(LOGFILE,"[%s] %s\n", $ts, $strn);
		}
		static function warn() {
			$args = func_get_args();
			$strn = @call_user_func_array('sprintf',array_slice($args,0));
			$ts = Console::ts();
			fprintf(STDERR,"[%s] WARNING: %s\n", $ts, $strn);
			if (LOGFILE) fprintf(LOGFILE,"[%s] WARNING: %s\n", $ts, $strn);
		}
		static function backtrace($trim=1,$stack=null) {
			if (!$stack) { $stack = debug_backtrace(false); }
			$trace = array();
			foreach($stack as $i=>$method) {
				$args = array();
				if ($i > ($trim - 1)) {
					foreach($method['args'] as $arg) { 
						$args[] = gettype($arg);
					}
					$mark = (($i == ($trim))?'in':'   invoked from');
					if (isset($method['type'])) {
						$trace[] = sprintf("  %s %s%s%s(%s) - %s:%d", $mark, $method['class'], $method['type'], $method['function'], join(',',$args), str_replace(BASE_PATH,'',$method['file']), $method['line']);
					} else {
						$trace[] = sprintf("  %s %s(%s) - %s:%d", $mark, $method['function'], join(',',$args), str_replace(BASE_PATH,'',$method['file']), $method['line']);
					}
				}
			}
			fprintf(STDERR, join("\n", $trace)."\n");
			if (LOGFILE) fprintf(LOGFILE, join("\n", $trace)."\n");
		}
		static function write() {
			$args = func_get_args();
			$strn = @call_user_func_array('sprintf',array_slice($args,0));
			printf($strn);
			if (LOGFILE) fprintf(LOGFILE, $strn);
		}
		static function writeLn() {
			$args = func_get_args();
			$strn = @call_user_func_array('sprintf',array_slice($args,0));
			printf($strn . "\n");
			if (LOGFILE) fprintf(LOGFILE, $strn . "\n");
		}
		static function status() {
			$args = func_get_args();
			$strn = @call_user_func_array('sprintf',array_slice($args,0));
			printf($strn . str_repeat(chr(8),strlen($strn)));
			if (LOGFILE) fprintf(LOGFILE, $strn);
		}

		static function ts() {
			return @date("y-M-d H:i:s",time());
		}
	}

	Console::debug('Starting with base %s', BASE_PATH);
	Console::debug("Running on PHP %d.%d.%d (%s)", PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION, PHP_OS);

	class Lepton {

		function run($class) {
			Console::debug("Inspecting environment:\n%s", ModuleManager::debug());
			Console::debug('Invoking application instance from %s.', $class);
			if (class_exists($class)) {
				$rv = 0;
				try {
					$instance = new $class();
					Console::debug("Instance constructed, running...");
					$rv = $instance->run();
					Console::debug("Main method exited with code %d.", $rv);
					Console::debug("Instance destructed, exiting...");
				} catch (Exception $e) {
					Console::warn("Unhandled exception: %s", $e->getMessage());
					Console::backtrace(0,$e->getTrace());
				}
				return $rv;
			} else {
				Console::warn('Application class not found!');
				return 1;
			}
		}

	}

	interface IApplication {
		function run();
	}

	abstract class Application implements IApplication {

	}


	/**
	 * Lepton-ng Bootstrap Code
	 *
	 */
	class ModuleManager {

		static $_modules;

		static function checkExtension($extension,$required=false) {
			if( extension_loaded($extension) ) {
				return true;
			}
			Console::debug("PHP extension not loaded: %s", $extension);
			$filename = (strpos(PHP_OS, "WIN") !== false ? "php_" : "") . $extension . (strpos(PHP_OS, "WIN") === false ? ".so" : ".dll");
			Console::debug("Attempting a manual load of %s from %s", $extension, $filename);
			if ( !@dl($filename) && ($required) ) {
				Console::warn("Dynamic loading of extensions disabled and extension %s flagged as required. Please load it manually or enable the dl() function.", $extension);
				exit(1);
			}
			return (extension_loaded($extension));
		}

		static function load($module,$optional=false) {
			if (strpos($module,'*') == (strlen($module) - 1)) {
				$path = BASE_PATH.'sys/'.str_replace('.','/',$module).'.php';
				$f = glob($path);
				$failed = false;
				foreach($f as $file) {
					if (!ModuleManager::load(str_replace('*',basename($file,'.php'),$module))) $failed=true;
				}
				return (!$failed);
			}
			if (ModuleManager::has($module)) {
				Console::debug("Already loaded %s.",$module);
				return true;
			}
			$path = BASE_PATH.'sys/'.str_replace('.','/',$module).'.php';
			if (file_exists($path)) {
				Console::debug("Loading %s (%s).",$module,$path);
				try {
					require($path);
				} catch(ModuleException $e) {
					return false;
				}
				ModuleManager::$_modules[strtolower($module)] = true;
				return true;
			} else {
				Console::debug("Failed to load %s.",$module);
				return false;
			}
		}

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

		static function debug() {
			$modinfo = array();
			if (!is_array(ModuleManager::$_modules)) {
				ModuleManager::$_modules = array();
			}
			foreach(ModuleManager::$_modules as $mod=>$meta) {
				$modinfo[] = sprintf(" - Module %s: Loaded", $mod);
			}
			return join("\n", $modinfo);
		}

	}

	if (isset($argc)) {
		ModuleManager::load('lepton.base.application');
	}

?>
