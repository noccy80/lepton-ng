<?php

class Readline {
	static $autocompleter = null;
	static function setAutoCompleteHandler($ac) {
		self::$autocompleter = $ac;
	}
	static function getAutocompleteHandler() {
		return self::$autocompleter;
	}
	static function read($prompt = null) {
		if (self::$autocompleter != null) readline_completion_function(self::$autocompleter);
		return readline($prompt);
	}
	static function addHistory($command) {
		readline_add_history($command);
	}
}
