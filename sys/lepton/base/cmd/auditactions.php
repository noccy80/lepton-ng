<?php __fileinfo("Audit log management utilities", array(
	'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
	'version' => '1.0',
	'updater' => null
));

class AuditActions {
	static $help = array(
		'audit' => "Audit log management functions"
	);
	function _info($cmd) { return AuditActions::$help[$cmd->name]; }
	/**
	 * Shows or manages audit log entries
	 * @param string $cmd The command to execute
	 */
	function audit($cmd=null) {
		if ($cmd == null) {
			Console::writeLn(__astr("\b{audit}: Audit log management"));
			Console::writeLn(__astr("    audit \b{view} [\u{module}|\u{string}]       View audit log"));
			Console::writeLn(__astr("    audit \b{clear} [\u{module}|\u{string}]      Clear audit log"));
			Console::writeLn();
		}
	}

}

config::push('lepton.cmd.actionhandlers','AuditActions');
