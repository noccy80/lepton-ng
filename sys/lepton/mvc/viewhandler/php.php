<?php

ModuleManager::load('lepton.mvc.view');

class PlainViewHandler extends ViewHandler {
	function __construct() {
	}
	function loadView($view) {
		$path = APP_PATH.'views/'.$view;
		Console::debugEx(LOG_BASIC,__CLASS__,"Attempting to invoke view from %s", $path);
		if (file_exists($path)) {
			Console::debugEx(LOG_BASIC,__CLASS__,"Invoking as Pure PHP View");
			include($path);
		} else {
			throw new ViewNotFoundException("The view ".$view." could not be found");
		}
	}
}

View::$_handlers['PlainViewHandler'] = '.*\.php$';
