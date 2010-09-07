<?php

ModuleManager::load('lepton.mvc.view');

class SmartyViewHandler extends ViewHandler {
	private $smarty;
	function __construct() {
		// put full path to Smarty.class.php
		$smartyloc = config::get('smarty.location');
		require($smartyloc);
		if (class_exists('Smarty')) {
			$this->smarty = new Smarty();
			$this->smarty->template_dir = config::get('smarty.dir.templates');
			$this->smarty->compile_dir = config::get('smarty.dir.compiled');
			$this->smarty->cache_dir = config::get('smarty.dir.cache');
			$this->smarty->config_dir = config::get('smarty.dir.config');
			$this->set('LEPTON_PLATFORM_ID', LEPTON_PLATFORM_ID);
		} else {
			throw new BaseException("Smarty view invoked but Smarty not found");
		}
	}
	function loadView($view) {
		if ($this->smarty) {
			foreach($this->getViewData() as $key=>$value) {
				$this->smarty->assign($key, $value);
			}
			$this->smarty->display($view);
		} else {
			throw new BaseException("Smarty view loaded but Smarty not found");
		}
	}
}

View::$_handlers['SmartyViewHandler'] = '.*\.tpl$';
