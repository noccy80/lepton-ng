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

	if(defined('LEPTON_BASE_PATH')) {
		$path = LEPTON_BASE_PATH.'/..';
	} else {
		if (isset($argv)) {
			// $path = $argv[0];
			$path = getcwd();
			define('LEPTON_CONSOLE', true);
		} else {
			if (strrpos($path,'/') !== false) {
				$path = getenv('SCRIPT_FILENAME');
				$path = pathinfo($path, PATHINFO_DIRNAME);
			} else {
				$path = getcwd();
				if (!$path) throw new Exception('Failed to get script path!');
			}
		}
	}

	define('BASE_PATH', $path.'/');

	if (getenv("DEBUG") == "1") {
		error_reporting(E_ALL);
	}

	class Console {
		static function debug() {
			$args = func_get_args();
			$strn = @call_user_func_array('sprintf',array_slice($args,0));
			if (getenv("DEBUG") == "1") {
				fprintf(STDERR,"[%s] %s\n", Console::ts(), $strn);
			}
		}
		static function warn() {
			$args = func_get_args();
			$strn = @call_user_func_array('sprintf',array_slice($args,0));
			fprintf(STDERR,"[%s] WARNING: %s\n", Console::ts(), $strn);
		}
		static function write() {
			$args = func_get_args();
			$strn = @call_user_func_array('sprintf',array_slice($args,0));
			printf($strn);
		}
		static function writeLn() {
			$args = func_get_args();
			$strn = @call_user_func_array('sprintf',array_slice($args,0));
			printf($strn . "\n");
		}
		static function status() {
			$args = func_get_args();
			$strn = @call_user_func_array('sprintf',array_slice($args,0));
			printf($strn . str_repeat(chr(8),strlen($strn)));
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
			$instance = new $class();
			Console::debug("Instance constructed, running...");
			$rv = $instance->run();
			Console::debug("Main method exited with code %d.", $rv);
			Console::debug("Instance destructed, exiting...");
			return $rv;
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

		static function checkLibrary($func,$libinfo,$required=false) {
			if ( $func != null ) {
				if( function_exists($func) ) {
					return true;
				}
				Console::debug("Missing library: %s (testing for %s)", $libinfo, $func);
			}
			$extension = $libinfo . (strpos(PHP_OS, "WIN") === false ? ".so" : ".dll");
			Console::debug("Attempting a manual load of %s", $extension);
			if ( !@dl($extension) && ($required) ) {
				Console::warn("Dynamic loading of libraries disabled and library %s flagged as required. Please load it manually or enable the dl() function.", $libinfo);
				exit(1);
			}
			if ( $func != null ) {
				if ( function_exists($func) ) {
					return true;
				}
				Console::warn("The library %s is missing. Please refer to the documentation on how to fix this problem. A few hints would be to check your package manager as well as pecl and pear for the missing library.", $libinfo);
				if ($required) {
					Console::warn("Library marked as required. Exiting");
					exit(1);
				}
			} else {
				return true;
			}
		}

		static function load($module,$optional=false) {
			if (ModuleManager::has($module)) {
				Console::debug("Already loaded %s.",$module);
				return true;
			}
			$path = 'sys/'.str_replace('.','/',$module).'.php';
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

