<?php module("Basic actions for the Lepton utilities", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));

class BaseAction extends Action {
    private $extn;
    public static $commands = array(
            'package' => array(
                'arguments' => '',
                'info' => 'Manage packages'
            ),
            'load' => array(
                'arguments' => '',
                'info' => 'Load a lepton module'
            ),
            'initialize' => array(
                'arguments' => '',
                'info' => 'Initialize an environment'
            ),
            'config' => array(
                'arguments' => '',
                'info' => 'Reconfigure an environment'
            ),
            'set' => array(
                'arguments' => '',
                'info' => 'Set a configuration value (for the session)'
            ),
            'get' => array(
                'arguments' => '',
                'info' => 'Get a configuration value (or glob)'
            ),
            'push' => array(
                'arguments' => '',
                'info' => 'Push a value onto a configuration stack (for the session)'
            ),
    );

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
            Console::writeLn(__astr("\b{set}: set a configuration value for the session."));
            Console::writeLn(__astr("    set \u{key} \u{value}"));
            Console::writeLn(__astr("To make the change persistent, please use the configuration file."));
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

    function get($key="*") {
        $cfg = Config::get($key);
        if (is_array($cfg)) {
            foreach($cfg as $k=>$v) {
                if (is_array($v)) {
                    Console::writeLn(__astr("    \b{%s} \c{ltgray :} Array("), $k);
                    foreach($v as $vk=>$vv) {
                        if ($this->isPassword($vk)) {
                            $vv = __astr("\c{gray *****}");
                        } 
                        Console::writeLn(__astr("        \b{%s} \c{ltgray =>} %s"), $vk,__printable($vv));
                    }
                    Console::writeLn(__astr("    )"));
                } else {
                    if ($this->isPassword($k)) {
                        $v = __astr("\c{gray *****}");
                    } 
                    Console::writeLn(__astr("    \b{%s} \c{ltgray :} %s"), $k, __printable($v));
                }
            }
        } else {
            if (self::isPassword($key)) {
                $cfg = __astr("\c{gray *****}");
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

actions::register(
    new BaseAction(),
    'base',
    'Base management functions',
    BaseAction::$commands
);
