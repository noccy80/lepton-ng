<?php __fileinfo("Basic actions for the Lepton utilities", array(
	'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
	'version' => '1.0',
	'updater' => null
));

class BaseActions {
	function _info($cmd) {
		switch($cmd->getName()) {
			case 'help':		return "Show help";
			case 'package':		return "Manage packages";
			case 'load':		return "Load a lepton module";
			case 'info':		return "Show information";
			case 'initialize':	return "Initialize an environment";
			case 'config':		return "Reconfigure an environment";
			case 'set':			return "Set a configuration value";
			case 'get':			return "Get a configuration value (or glob)";
			case 'push':		return "Push a value onto a configuration stack";
			default:			return "n/a";
		}
	}
	function help($func=null) {
		Console::writeLn(__astr("\b{help}: Show available commands"));
		foreach(config::get('lepton.cmd.actionhandlers') as $handler) {
			$r = new ReflectionClass($handler);
			foreach($r->getMethods() as $method) {
				if (substr($method->getName(),0,1) != '_') {
					Console::writeLn(__astr("    \b{%-15s}: %s (\c{ltgray %s})"), $method->getName(), call_user_func_array(array($handler,"_info"),array($method)), basename($method->getFileName(),'.php'));
				}
			}
		}
		Console::writeln();
	}
	function clear($key=null) {
		if ($key) {
			$c = Config::clr($key);
			if (!$c) {
				Console::writeLn(__astr("    Nothing cleared"));
			} else {
				if (!is_array($c)) {
					Console::writeLn(__astr("    \b{%s} \c{ltgray :} <cleared>"), $c);
				} else {
					foreach($c as $cc) {
						Console::writeLn(__astr("    \b{%s} \c{ltgray :} <cleared>"), $cc);
					}
				}
			}
		} else {
			Console::writeLn(__astr("\b{clear}: clear a configuration value"));
			Console::writeLn(__astr("    cleart \u{key}"));
		}
	}
	function set($key=null,$val=null) {
		if (($key) && ($val)) {
			$val = __fromprintable($val);
			Config::set($key,$val);
			Console::writeLn(__astr("    \b{%s} \c{ltgray :} %s"), $key, __printable($val));
		} else {
			Console::writeLn(__astr("\b{set}: set a configuration value"));
			Console::writeLn(__astr("    set \u{key} \u{value}"));
		}
	}
	function push($key=null,$val=null) {
		if (($key) && ($val)) {
			$val = __fromprintable($val);
			Config::push($key,$val);
			Console::writeLn(__astr("    \b{%s[]} \c{ltgray :} %s"), $key, __printable($val));
		} else {
			Console::writeLn(__astr("\b{push}: push a configuration value"));
			Console::writeLn(__astr("    push \u{key} \u{value}"));
		}
	}
	function isPassword($str) {
		static $passwdstr = array(
			'/password/i'
		);
		foreach($passwdstr as $restr) {
			if (preg_match($restr,$str)) return true;
		}
		return false;
	}
	function get($key=null) {
		$cfg = Config::get($key);
		if (is_array($cfg)) {
			foreach($cfg as $k=>$v) {
				if (is_array($v)) {
					Console::writeLn(__astr("    \b{%s} \c{ltgray :} Array("), $k);
					foreach($v as $vk=>$vv) {
						if (self::isPassword($vk)) {
							$vv = __astr("\u{\c{gray \b{XxXxXx}}}");
						} 
						Console::writeLn(__astr("        \b{%s} \c{ltgray =>} %s"), $vk,__printable($vv));
					}
					Console::writeLn(__astr("    )"));
				} else {
					if (self::isPassword($k)) {
						$v = __astr("\u{\c{gray \b{XxXxXx}}}");
					} 
					Console::writeLn(__astr("    \b{%s} \c{ltgray :} %s"), $k, __printable($v));
				}
			}
		} else {
			if (self::isPassword($key)) {
				$cfg = __astr("\u{\c{gray \b{XxXxXx}}}");
			}
			Console::writeLn(__astr("    \b{%-40s} \c{ltgray :} %s"), $key, $cfg);
		}
	}
	function load($module=null) {
		if ($module) {
			if (ModuleManager::load($module)) {
				Console::writeLn(__astr("\b{load}: Module %s loaded"), $module);
			} else {
				Console::writeLn(__astr("\b{load}: Module %s failed to load!"), $module);
			}
		}
		Console::writeLn(__astr("\b{Loaded modules:}"));
		Console::writeLn("%s",ModuleManager::debug());
	}
	function package($op=null,$pkgname=null) {
		ModuleManager::load('lepton.utils.l2package');
		switch($op) {
			case 'install':
				$pm = new L2PackageManager();
				$pkg = new L2Package($pkgname);
				$pm->installPackage($pkg);
				break;
			case 'remove':
				$pm = new L2PackageManager();
				$pkg = new L2Package($pkgname);
				$pm->removePackage($pkg);
				break;
			case 'list':
				$pm = new L2PackageManager();
				$pm->listPackages();
				break;
			default:
				Console::writeLn(__astr("\b{Package}: Manage packages (l2p)"));
				Console::writeLn(__astr("    package \b{install} \u{package.l2p}        Installs a package"));
				Console::writeLn(__astr("    package \b{remove} \u{package}             Removes a package"));
				Console::writeLn(__astr("    package \b{list} [\u{package}]             List packages"));
				Console::writeLn(__astr("    package \b{info} [\u{package}]             Show information on packages"));
				Console::writeLn(__astr("    package \b{find} [\u{filename}]            Find package that owns file"));
				Console::writeLn(__astr("    package \b{update} [\u{package}|\b{all}]       List packages"));
				Console::writeLn();
		}
	}

	function info($act=null) {
		switch($act) {
			case 'pdo':
				Console::writeLn(__astr("\b{info pdo}: Installed PDO drivers:"));
				foreach(PDO::getAvailableDrivers() as $driver) {
					Console::writeLn("  %s", $driver);
				}
				break;
			case 'php':
				Console::writeLn(__astr("\b{info php}: PHP Compatibility Info"));
				$info = array(
					'php_uname()' => php_uname('a'),
					'phpversion()' => phpversion(),
					'php_sapi_name()' => php_sapi_name(),
					'PHP_OS' => PHP_OS,
					'DIRECTORY_SEPARATOR' => DIRECTORY_SEPARATOR,
					'PATH_SEPARATOR' => PATH_SEPARATOR,
					'PHP_SHLIB_SUFFIX' => PHP_SHLIB_SUFFIX,
					'sys_get_temp_dir()' => sys_get_temp_dir(),
					'Platform' => LEPTON_PLATFORM_ID
				);
				$comp = array(
					'COMPAT_GETOPT_LONGOPTS' => (PHP_VERSION >= "5.3")?'Supported':'Emulated (PHP >= 5.3)',
					'COMPAT_SOCKET_BACKLOG' => (PHP_VERSION >= "5.3.3")?'Supported':'Missing (PHP >= 5.3.3)',
					'COMPAT_HOST_VALIDATION' => (PHP_VERSION >= "5.2.13")?'Supported':'Emulated (PHP >= 5.2.13)',
					'COMPAT_NAMESPACES' => (PHP_VERSION >= "5.3.0")?'Supported':'Missing (PHP >= 5.3.0)',
					'COMPAT_INPUT_BROKEN' => ((PHP_VERSION >= "5") && (PHP_VERSION < "5.3.1"))?'php://input possibly broken (PHP >= 5, PHP < 5.3.1)':'Functional',
					'COMPAT_CALLSTATIC' => (PHP_VERSION >= "5.3.0")?'Supported':'Missing (PHP >= 5.3.0)',
					'COMPAT_CRYPT_BLOWFISH' => (PHP_VERSION >= "5.3.0")?'Supported':'Missing (PHP >= 5.3.0)'
				);
				$opts = array(
					'BASE_PATH' => BASE_PATH,
					'APP_PATH' => APP_PATH,
					'SYS_PATH' => SYS_PATH
				);
				Console::writeLn(__astr("\b{Lepton Overview:}"));
				foreach($opts as $key=>$val) {
					Console::writeLn("  %-25s : %s", $key, $val);
				}
				Console::writeLn(__astr("\b{PHP Overview:}"));
				foreach($info as $key=>$val) {
					Console::writeLn("  %-25s : %s", $key, $val);
				}
				Console::writeLn(__astr("\b{Compatibility layer overview:}"));
				foreach($comp as $key=>$val) {
					Console::writeLn("  %-25s : %s", $key, $val);
				}
				break;
			default:
				Console::writeLn(__astr("\b{info}: Show relevant information"));
				Console::writeLn(__astr("    info \b{php}        Shows system and compatibility information."));
				Console::writeLn(__astr("    info \b{pdo}        Shows information on available PDO drivers."));
				Console::writeLn();
		}

	}

	function initialize() {

		Console::write("Single or Multi domain site? [S/m] "); $mode = Console::readLn();
		Console::write("  Base domain name (for routing): "); $dombase = Console::readLn();

		Console::write("Creating folder structure ... ");
		Console::status("app             ");
		usleep(50000);
		Console::status("app/config      ");
		usleep(50000);
		Console::status("app/controllers ");
		usleep(50000);
		Console::status("app/models      ");
		usleep(50000);
		Console::status("app/views       ");
		usleep(50000);
		Console::status("res             ");
		usleep(50000);
		Console::writeLn("Done            ");

		Console::write("Copying files ... ");
		Console::writeLn("39 of 39");

		Console::write("Checking configuration ... ");
		Console::writeLn("Ok");
		Console::write("Do you want a htconf too? [y/N] ");
		$w = Console::readLn();
	}

	function config() {
		Console::write("Reading configuration metadata ... ");
		Console::status('dbx           '); sleep(1);
		Console::status('dbx.db        '); sleep(1);
		Console::status('dbx.db.mysql  '); sleep(1);
		Console::writeLn('done          ');
		Console::writeLn("Parsing configuration file ... done");
		Console::writeLn("Preparing options ... done");
	}

}

config::push('lepton.cmd.actionhandlers','BaseActions');
