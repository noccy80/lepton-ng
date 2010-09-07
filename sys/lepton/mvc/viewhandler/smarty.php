<?php __fileinfo("Smarty View Handler", array(
	'version' => '1.0',
	'author' => 'Christopher Vagnetoft <noccy@chillat.net>'
));

ModuleManager::load('lepton.mvc.view');

class SmartyViewHandler extends ViewHandler {
	private $smarty;
	private $template;
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
	function loadView($template) {
		$this->template = $template;
		if ($this->smarty) {
			foreach($this->getViewData() as $key=>$value) {
				$this->smarty->assign($key, $value);
			}
		} else {
			throw new BaseException("Smarty view loaded but Smarty not found");
		}
	}
	function display() {
		$this->smarty->display($this->template);
	}
}

View::$_handlers['SmartyViewHandler'] = '.*\.tpl$';
