<?php

using('lepton.cli.ansi');

abstract class Action { 

}

abstract class Actions {
	private static $_actions = array();
	public static function register(Action $action, $command, $description, $subcommands) {
		self::$_actions[$command] = array(
			'commands' => $subcommands,
			'info' => $description,
			'handler' => $action
		);
	}
	public static function invoke($command,$arguments) {
		if (isset(self::$_actions[$command])) {
			// Look up the sub command if any, otherwise show help
			if (count($arguments) == 0) {
				console::writeLn("Valid commands for %s:", $command);
				foreach(self::$_actions[$command]['commands'] as $cmd=>$data) {
					$argstr = $data['arguments'];
					console::writeLn("    %s %s: %s", __astr('\b{'.$cmd.'}'), __astr($argstr),$data['info']);
				}
				return true;
			} else {
				if (method_exists(self::$_actions[$command]['handler'], $arguments[0])) {
					call_user_func_array(
						array(self::$_actions[$command]['handler'], $arguments[0]),
						array_slice($arguments,1)
					);
					return true;
				} else {
					return false;
				}
			}
		}
	}
	public static function listActions() {
		console::writeLn("Valid actions:");
		foreach(self::$_actions as $action=>$data) {
			console::writeLn("    %s: %s", __astr('\b{'.$action.'}'), $data['info']);
		}
	}
}
