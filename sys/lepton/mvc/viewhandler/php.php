<?php

ModuleManager::load('lepton.mvc.view');

class PlainViewHandler extends ViewHandler {
	function __initialize() {
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

class SmartyViewHandler extends ViewHandler {
	private $smarty;
	function __initialize() {
		// put full path to Smarty.class.php
		require('/usr/local/lib/php/Smarty/Smarty.class.php');
		$this->smarty = new Smarty();
		$this->smarty->template_dir = '/web/www.domain.com/smarty/templates';
		$this->smarty->compile_dir = '/web/www.domain.com/smarty/templates_c';
		$this->smarty->cache_dir = '/web/www.domain.com/smarty/cache';
		$this->smarty->config_dir = '/web/www.domain.com/smarty/configs';
	}
	function loadView($view) {
		foreach($this->getViewData() as $key=>$value) {
			$this->smarty->assign($key, $value);
		}
		$this->smarty->display($view);
	}
}
