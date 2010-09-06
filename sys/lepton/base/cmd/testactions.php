<?php __fileinfo("Crash Test Utilities", array(
	'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
	'version' => '1.0',
	'updater' => null
));

class TestActions {

	static $help = array(
		'exception' => "Throw an exception",
		'run' => "Run an application instance",
		'uuid' => "Generate a new UUID v4"
	);
	function _info($cmd) { return TestActions::$help[$cmd->name]; }

	function exception($msg=null,$type=null) {
		if (!$msg) $msg = "Exception";
		if ($type) {
			throw new $type($msg);
		} else {
			throw new Exception($msg);
		}
	}

	function run($class,$args=null) {
		$arg = func_get_args();
		return call_user_func_array(array('Lepton','run'),$arg);
	}

	function uuid() {
		ModuleManager::load('lepton.crypto.uuid');
		Console::writeLn(__astr("    \b{UUID v4} : %s"), Uuid::v4());
	}

}

config::push('lepton.cmd.actionhandlers','TestActions');

