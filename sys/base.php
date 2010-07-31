<?php


	if (defined('LEPTON_BASE_PATH')) {
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
/*
	if (defined('LEPTON_CONSOLE')) {
		$p = explode('/',$path);
		$p = array_slice($p,0,count($p)-2);
		$path = join('/',$p);
	}
*/
	define('BASE_PATH', $path.'/');


	class Console {
		static function debug() {
			$args = func_get_args();
			$strn = @call_user_func_array('sprintf',array_slice($args,0));
			if (getenv("DEBUG")) {
				fprintf(STDERR,"[%s] %s\n", date("y-M-d H:i:s",time()), $strn);
			}
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
	}

	Console::debug('Starting with base %s', BASE_PATH);

	class Lepton {

		function run($class) {
			Console::debug("Inspecting environment:\n%s", ModuleManager::debug());
			Console::debug('Invoking application instance from %s.', $class);
			$instance = new $class();
			return $instance->run();
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

