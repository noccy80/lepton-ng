<?php __fileinfo("Air Utilities", array(
	'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
	'version' => '1.0',
	'updater' => null
));

ModuleManager::load('app.air.binder');

class AirActions {

	static $help = array(
		'airmake' => "Update bindings for AirBinder"
	);
	function _info($cmd) { return TestActions::$help[$cmd->name]; }

	function airmake($cmd=null,$file=null) {
		Console::writeLn(__astr("\b{airmake}: %s (%s)"), $cmd, $file);
		if ($file == null) {
			$file = APP_PATH."/air/src/airbinder.js";
		}
		switch($cmd) {
		case 'update':
			AirBinder::save($file);
			Console::writeLn(__astr("\b{Configuration Saved} to %s"),$file);
			break;
		default:
			Console::writeLn("update is valid action.");
		}
	}

}

config::push('lepton.cmd.actionhandlers','AirActions');

