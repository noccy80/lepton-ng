<?php

/*
	This file is part of Lepton Framework.
	Copyright (C) 2001-2010  Noccy Labs

	Lepton Framework is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	Lepton Framework is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with the software; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

__fileinfo("CLI System Information", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));

class InfoAction extends Action {
    private $extn;
    public static $commands = array(
        'php' => array(
            'arguments' => '',
            'info' => 'Show information about the PHP platform in use'
        ),
        'pdo' => array(
            'arguments' => '',
            'info' => 'List the available PDO drivers'
        ),
        'extensions' => array(
            'arguments' => '',
            'info' => 'List loaded extensions'
        ),
        'streams' => array(
            'arguments' => '',
            'info' => 'Show the available stream protocols'
        )
    );

    public function pdo() {
        Console::writeLn(__astr("\b{info pdo}: Installed PDO drivers:"));
        foreach(PDO::getAvailableDrivers() as $driver) {
            Console::writeLn("  %s", $driver);
        }
    }

    public function php() {
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
            'COMPAT_CRYPT_BLOWFISH' => (PHP_VERSION >= "5.3.0")?'Supported':'Missing (PHP >= 5.3.0)',
            'COMPAT_PHP_FNMATCH' => ((PHP_OS == "Linux") || ((PHP_OS == "Windows") && (PHP_VERSION >= "5.3")))?'Native':'Emulated (WIN + PHP >= 5.3.0)'
        );
        $opts = array(
            'base::basePath()' => base::basePath(),
            'base::appPath()' => base::appPath(),
            'base::sysPath()' => base::sysPath(),
			'TMP_PATH' => TMP_PATH
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
    }

    private function checkExt($mod,$last=false,$lastp=false,$use='') {
        $ext = $this->extn;
        Console::write(" %s   %s %-10s: %s",
            ($lastp==false)?'|':' ',
            ($last==false)?'|-':'\'-',
            $mod,
            (isset($ext[$mod])?'\c{32 yes}':'\c{31 no}')
        );
        if ($use) {
           // Console::write(" - %s\n",$it.$use.$rc);
        } else {
            Console::write("\n");
        }
    }

    private function treenode($text,$last=false,$lastp=false) {
        $ext = $this->extn;
        Console::writeLn(" %s   %s %s",
            ($lastp==false)?'|':' ',
            ($last==false)?'|-':'\'-',
            $text
        );
    }

    function streams() {
        $sw = stream_get_wrappers();
        $sf = stream_get_filters();

        Console::writeLn("Stream Management");
        Console::writeLn(" |- Registered wrappers");
        for($n=0; $n<count($sw); $n++) {
            $this->treenode( $sw[$n] , !(($n+1)<count($sw)), false );
        }

        Console::writeLn(" '- Registered filters");
        for($n=0; $n<count($sf); $n++) {
            $this->treenode( $sf[$n] , !(($n+1)<count($sf)), true );
        }

        Console::writeLn();
    }

	function extensions() {
		$cb = 0;
		Console::writeLn(__astr("\b{Loaded extensions:}"));
		$ext = get_loaded_extensions();
		foreach($ext as $val) {
			Console::write('  %-18s', $val);
			$cb++;
			if ($cb > 3) { Console::writeLn(); $cb = 0; }
		}
		Console::writeLn();
	}

}

actions::register(
	new InfoAction(),
	'info',
	'Show various pieces of information',
	InfoAction::$commands
);
