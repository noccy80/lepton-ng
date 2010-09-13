<?php __fileinfo("Air Utilities", array(
	'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
	'version' => '1.0',
	'updater' => null
));

ModuleManager::load('app.air.binder');

class AirActions {

	static $help = array(
		'airmake' => 'Update bindings for AirBinder',
		'airinitenv' => 'Initializes air environment',
		'aircrypto' => 'Air Cryptography management'
	);

	private $airopts = array();

	function __construct() {
		if (file_exists(APP_PATH.'/airmakerc')) {
			$fh = fopen(APP_PATH.'/airmakerc','r');
			while (!feof($fh)) {
				$line = fgets($fh);
				if ($line[strlen($line)-1] == "\n") { $line = substr($line,0,strlen($line)-1); }
				if (strpos($line,'=') !== false) {
					$ld = explode('=',$line);
					$this->airopts[$ld[0]] = dequote($ld[1]);
				}
			}
		} else {
			Console::writeLn("No %s found",APP_PATH.'/airmakerc');
		}
	}

	function _info($cmd) { return AirActions::$help[$cmd->name]; }

	function aircrypto($action=null,$dest=null) {
		switch($action) {
			case 'initstore':
				Console::write("Initializing certificate store ... ");
				$cmd = 'java -jar "'.$this->airopts['SDKDIR'].'/lib/adt.jar" -certificate -cn SelfSign -ou Dev -o "example" -c US 2048-RSA "'.APP_PATH.'/'.$this->airopts['AIRKEYSTORE'].'" "'.$this->airopts['AIRSTOREPASS'].'"';
				exec($cmd, $out, $ret);
				if ($ret == 0) {
					Console::writeLn("Done");
				}
				break;
			default:
				Console::writeLn(__astr('\b{aircrypto}: Air crypto management'));
				Console::writeLn(__astr('    airmake \b{initstore}           : Initialize the certificate store'));
		}
	}

	function airmake($action=null,$dest=null) {
		switch($action) {
			case 'update':
				if ($dest == null) {
					$dest = APP_PATH."/air/src/airbinder.js";
				}
				AirBinder::save($dest);
				Console::writeLn(__astr("\b{Configuration Saved} to %s"),$dest);
				break;
			case 'run':
				Console::writeLn("Running application from manifest %s!", $this->airopts['AIRMANIFEST']);
				$cmd = $this->airopts['SDKDIR'].'/bin/adl "'.APP_PATH.$this->airopts['AIRMANIFEST'].'"';
				exec($cmd, $out, $ret);
				if ($ret == 0) {
					Console::writeLn("Closed Ok");
				} else {
					Console::write(join("\n",$out)."\n");
				}
				break;
			case 'export':
				Console::writeLn("Exporting application from manifest %s!", $this->airopts['AIRMANIFEST']);
				$cmd =	$this->airopts['SDKDIR'].'/bin/adt '.
						'-package '.
							'-storetype pkcs12 '.
							'-keystore "'.APP_PATH.$this->airopts['AIRKEYSTORE'].'" '.
							'-storepass "'.$this->airopts['AIRSTOREPASS'].'" '.
							'-target air '.
							'"'.$this->airopts['AIRFILE'].'" '.
						'"'.APP_PATH.$this->airopts['AIRMANIFEST'].'" '.
						'-C "'.APP_PATH.$this->airopts['AIRDIR'].'" '.
						'"'.APP_PATH.$this->airopts['AIRFILES'].'"';
				Console::writeLn("$ %s", $cmd);
				exec($cmd, $out, $ret);
				if ($ret == 0) {
					Console::writeLn("Closed Ok");
				} else {
					Console::write(join("\n",$out)."\n");
				}
				break;
			case 'scaffold':
				Console::writeLn("Scaffolding");
				break;
			default:
				Console::writeLn(__astr('\b{airmake}: Run or export air package'));
				Console::writeLn(__astr('    airmake \b{run}                 : Run the package'));
				Console::writeLn(__astr('    airmake \b{scaffold}            : Scaffold all the files and directories'));
				Console::writeLn(__astr('    airmake \b{export} [\u{filename}]   : Run the package'));
				Console::writeLn(__astr('    airmake \b{update} [\u{filename}]   : Update bindings'));
		}
	}

}

config::push('lepton.cmd.actionhandlers','AirActions');

