<?php

using('lepton.base.application');

try {
	using('lepton.ui.ncurses');
} catch(Exception $e) {
	//fprintf(STDERR,"Curses support not present.\n");
}

class ConfigEditor {
	private $conf = null;
	function __construct() {
		$this->conf = globals::get('config');
	}
	function loop() {
		$this->doReadline();
	}

    function doReadline() {
        $keys = $this->conf->getAll();
        foreach($keys as $key=>$val) {
			$defs = $this->conf->getDefs($key);
			if (arr::hasKey($defs,'vartype')) {
				$typeset = $defs['vartype'];
			} else {
				$typeset = typeof($val);
			}
			if (typeof($val) == 'array') {
				// Can't deal with these yet
			} else {
				printf(__astr("\b{%s}: %s\n"),$key,$defs['description']);
				if (arr::hasKey($defs,'extdescription')) {
					printf(__astr('\g{%s}'),$defs['extdescription']);
				}
				printf(__astr("  ["));
				switch($typeset) {
				case 'boolean':
					if ($val) { printf(__astr("\c{ltcyan true}")); }
					else { printf(__astr("(\c{cyan false})")); }
					break;
				case 'NULL':
					printf(__astr("\c{red NULL}"));
					break;
				case 'integer':
					printf(__astr("\c{cyan %d}"), $val);
					break;
				case 'float':
					printf(__astr("\c{cyan %.2f}"), $val);
					break;
				default:
					printf(__astr("\c{yellow '%s'}"), $val);
				}
				printf(__astr("]: "));
				$rl = console::readline();
				if ($rl) {
					$this->conf->{$key} = $rl;
					printf("Updated value of %s\n", $key);
				}
				printf("\n");
			}
		}		
        return true;
    }
}

class CliApplication extends ConsoleApplication {

	public $arguments = array(
		array('h','help','Show this help'),
		array('v','verbose','Be verbose'),
		array('f','no-safe-arrays','Disable array protection when setting/getting'),
		array('u:','username','Set the username to authenticate with'),
		array('p','password','Prompt for password')
	);
	public $commands = array(
		array('set \g{key} \g{val}'	,' - Set a value in the config'),
		array('get [\g{key}]'		,' - Get a value from the config'),
		array('unset \g{key}'		,' - Unset a value in the config'),
		array('backup \g{file}'		,' - Back up the configuration to a file'),
		array('restore \g{file}'	,' - Restore the configuration from a file'),
		array('call \g{cmd} [\g{k}:\g{v}]..' ,' - Call an XmlRpc endpoint'),
		array('\g{cmd} [\g{params...}]', ' - Directly call an application command')
	);

	protected $conf = null;

	protected function printValue($key,$val,$inst = 0) {
	    $defs = $this->conf->getDefs($key);
	    if (arr::hasKey($defs,'vartype')) {
		    $typeset = $defs['vartype'];
		} else {
		    $typeset = typeof($val);
		}
	    if ($inst == 0) {
			printf(__astr("'\b{%s}' = \g{%s(}"), $key, $typeset);
		}
		if (typeof($val) == 'array') {
			foreach($val as $k=>$v) {
				$this->printValue($k,$v,$inst+1);
			}
		} else {
			switch($typeset) {
			case 'boolean':
				if ($val) { printf(__astr("\c{ltcyan true}")); }
				else { printf(__astr("(\c{cyan false})")); }
				break;
			case 'NULL':
				printf(__astr("\c{red NULL}"));
				break;
			case 'integer':
				printf(__astr("\c{cyan %d}"), $val);
				break;
			case 'float':
				printf(__astr("\c{cyan %.2f}"), $val);
				break;
			default:
				printf(__astr("\c{yellow '%s'}"), $val);

			}
			if ($inst == 0) {
				printf(__astr("\g{)} "));
			}
			printf(__astr("  \c{ltgreen //} \c{green %s}"), $defs['description']);
			printf("\n");
		}
	}


	function __construct() {
		$this->conf = globals::get('config');
	}

	function main($argc,$argv) {

		if ($this->getParameterCount() == 0) {
			$this->usage();
			return 1;
		}
		switch($this->getParameter(0)) {
		case 'call':
			if ($this->hasArgument('u')) {
				$user = $this->getArgument('u');
				$pass = null;
				if ($this->hasArgument('p')) {
					printf('Password: ');
					$pass = console::readPass();
				}
			} else {
				$user = null;
				$pass = null;
			}
			if ($this->getParameterCount() == 1) {
				printf("Command needed for call.\n");
				return 1;
			}
			$paramlist = array_slice($this->getParameters(),2);
			$data = array();
			foreach($paramlist as $paramlistitem) {
				list($k,$v) = explode(':',$paramlistitem.':');
				$data[$k] = $v;
			}
			$site = $this->conf->vs_siteurl;
			$xc = new XmlrpcClient($site.'/api/xmlrpc', $user, $pass);
			$ret = $xc->call($this->getParameter(1), $data);
			if ($ret) {
				if (arr::hasKey($ret,'faultCode')) {
					printf("Error %d: %s\n", $ret['faultCode'], $ret['faultString']);
				} else {
					debug::inspect($ret,false,false);
				}
			} else {
				printf("Server error.\n");
			}
			break;
		case 'config':
			$editor = new ConfigEditor();
			$editor->loop();
			break;
		case 'set':
			$key = $this->getParameter(1);
			$defs = $this->conf->getDefs($key);
			$val = $this->getParameter(2);
			$this->printValue($key,$val);
			switch ($defs['vartype']) {
			case 'boolean':
				if ($val == "1") {
					$this->conf->{$key} = true;
				} else {
					$this->conf->{$key} = false;
				}
				break;
			case 'integer':
				$this->conf->{$key} = intval($val);
				break;
			case 'float':
				$this->conf->{$key} = floatval($val);
				break;
			default:
				$this->conf->{$key} = $val;
			}
			break;
		case 'get':
			if ($this->getParameterCount()>1) {
				$key = $this->getParameter(1);
				$val = $this->conf->{$key};
				$this->printValue($key,$val);
			} else {
				$keys = $this->conf->getAll();
				ksort($keys);
				foreach($keys as $key=>$val) {
					if ($key) $this->printValue($key,$val);
				}
			}
			break;
		case 'backup':
			$filename = $this->getParameter(1);
			printf("Backing up to %s...\n", $filename);
			$keys = $this->conf->getAll();
			file_put_contents($filename, serialize($keys));
			break;
		case 'restore':
			$filename = $this->getParameter(1);
			printf("Restoring from %s...\n", $filename);
			$keys = unserialize(file_get_contents($filename));
			foreach($keys as $key=>$value) {
				printf("  %s: ", $key);
				$this->conf->{$key} = $value;
				printf("Ok\n");
			}
			break;
		case 'unset':
			$keys = $this->getParameters();
			$keys = array_slice($keys,1);
			foreach($keys as $key) {
				printf("Unset key: %s\n", $key);
				$this->conf->{$key} = null;
			}
			break;
		default:
			$params = $this->getParameters();
			$cmd = $params[0];
			$params = array_slice($params,1);
			$cmdm = 'cmd_'.$cmd;
			if (is_callable(array($this,$cmdm))) {
				call_user_func_array(array($this,$cmdm),$params);
			} else {
				printf("Unknown command: %s, try -h\n", $cmd);
			}
			break;
		}

	}

}

