<?php

	declare(ticks = 1);

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



	class Console {
		static function debugEx($level,$module) {
			$args = func_get_args();
			$strn = @call_user_func_array('sprintf',array_slice($args,2));
			$ts = Console::ts();
			$lines = explode("\n",$strn);
			foreach($lines as $line) {
				if (getenv("DEBUG") >= $level) {
					fprintf(STDERR," %s | %20s | %s\n", $ts, $module,$line);
				}
				if (LOGFILE) fprintf(LOGFILE," %s | %20s | %s\n", $ts, $module, $line);
			}
		}

		static function debug() {
			$args = func_get_args();
			@call_user_func_array(array('Console','debugEx'),array_merge(array(LOG_DEBUG1,'Debug'),array_slice($args,0)));
		}

		static function warn() {
			$args = func_get_args();
			@call_user_func_array(array('Console','debugEx'),array_merge(array(LOG_WARN,'WARNING'),array_slice($args,0)));
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
			Console::debugEx(LOG_WARN,'Backtrace:', "%s", join("\n", $trace)."\n");
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

		static function getChar() {

		}

		static function readLine($hidden=false) {

		}

	}

	class Lepton {

		function run($class) {
			Console::debugEx(LOG_EXTENDED,__CLASS__,"Inspecting environment module state:\n%s", ModuleManager::debug());
			if (class_exists($class)) {
				$rv = 0;
				try {
					$instance = new $class();
					Console::debugEx(LOG_BASIC,__CLASS__,"Invoking application instance from %s.", $class);
					$rv = $instance->run();
					Console::debugEx(LOG_BASIC,__CLASS__,"Main method exited with code %d.", $rv);
				} catch (Exception $e) {
					Console::warn("Unhandled exception: (%s) %s in %s:%d", get_class($e), $e->getMessage(), str_replace(BASE_PATH,'',$e->getFile()), $e->getLine());
					$f = file($e->getFile());
					foreach($f as $i=>$line) {
						$mark = (($i+1) == $e->getLine())?'=> ':'   ';
						$f[$i] = sprintf('  %05d. %s',$i+1,$mark).$f[$i];
					}
					$first = $e->getLine() - 4; if ($first < 0) $first = 0;
					$last = $e->getLine() + 3; if ($last >= count($f)) $last = count($f)-1;
					$source = join("",array_slice($f,$first,$last-$first));
					Console::debug("Source dump:\n%s", $source);
					Console::backtrace(0,$e->getTrace());
				}
				return $rv;
			} else {
				Console::warn('Application class %s not found!', $class);
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
		static $_order;

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
				Console::debugEx(LOG_EXTENDED,__CLASS__,"Already loaded %s.",$module);
				return true;
			}
			$path = BASE_PATH.'sys/'.str_replace('.','/',$module).'.php';
			if (file_exists($path)) {
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
				// TODO: Show metadata
				$modinfo[] = sprintf(" - Module %s: Loaded", $mod);
			}
			return join("\n", $modinfo);
		}

	}

	Console::debugEx(LOG_BASIC,'(bootstrap)',"Base path: %s", BASE_PATH);
	Console::debugEx(LOG_BASIC,'(bootstrap)',"Platform: PHP v%d.%d.%d (%s)", PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION, PHP_OS);
	Console::debugEx(LOG_BASIC,'(bootstrap)',"Running as %s with pid %d", get_current_user(), getmypid());

	if (isset($argc)) {
		ModuleManager::load('lepton.base.application');
	}

?>
