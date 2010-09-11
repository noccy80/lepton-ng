<?php __fileinfo("Pure PHP View Handler", array(
	'version' => '1.0',
	'author' => 'Christopher Vagnetoft <noccy@chillat.net>'
));

ModuleManager::load('lepton.mvc.view');

class PlainViewHandler extends ViewHandler {
	private $path;
	function __construct() {
	}
	function loadView($view) {
		$path = APP_PATH.'views/'.$view;
		Console::debugEx(LOG_BASIC,__CLASS__,"Attempting to invoke view from %s", $path);
		if (file_exists($path)) {
			Console::debugEx(LOG_BASIC,__CLASS__,"Invoking as Pure PHP View");
			$this->path = $path;
		} else {
			throw new ViewNotFoundException("The view ".$view." could not be found");
		}
	}
	function display() {
		require($this->path);
	}
	function includeView($view) {
		$path = APP_PATH.'views/'.$view;
		include($path);
	}
}

View::$_handlers['PlainViewHandler'] = '.*\.php$';
