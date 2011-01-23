<?php

class StringsAction extends Action {
	public static $commands = array(
		'initialize' => array(
			'arguments' => '\u{lang}',
			'info' => 'Initialize the database with language'
		),
		'update' => array(
			'arguments' => '[\u{path}|all]',
			'info' => 'Update the strings from source files'
		),
		'compile' => array(
			'arguments' => '[\u{lang}[,\u{lang},..]|all]',
			'info' => 'Recompile the translations from database'
		),
		'purge' => array(
			'arguments' => '[\u{lang}[,\u{lang},..]|all]',
			'info' => 'Purge strings from database'
		),
		'translate' => array(
			'arguments' => '\u{tolang} \u{service}',
			'info' => 'Translate using service'
		),
		'spell' => array(
			'arguments' => '\u{lang}',
			'info' => 'Spell check language'
		)
	);
	public function initialize($lang=null) { 
		if (!$lang) {
			console::writeLn("Missing language code for strings initialize");
		} else {
			console::writeLn("Initializing database...");
		}
	}
}

actions::register(
	new StringsAction(),
	'strings',
	'Manages internationalization and translation of strings',
	StringsAction::$commands
);
