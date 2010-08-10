<?php

	// Lepton MVC bootstrapper

	ModuleManager::load('lepton.mvc.model');
	ModuleManager::load('lepton.mvc.view');
	ModuleManager::load('lepton.mvc.controller');
	ModuleManager::load('lepton.mvc.response');
	ModuleManager::load('lepton.user.authentication');
	ModuleManager::load('lepton.mvc.routers.defaultrouter');

	ModuleManager::load('lepton.web.*');

	class MvcApplication extends Application {
		function run() {
			Console::debugEx(LOG_VERBOSE,__CLASS__,'Invoking router...');
			// Create new router and invoke it
			$r = new DefaultRouter();
			$r->route();
		}
	}
	
	declare(encoding = 'UTF-8');

?>
