<?php

	// Lepton MVC bootstrapper

	ModuleManager::load('lepton.mvc.model');
	ModuleManager::load('lepton.mvc.view');
	ModuleManager::load('lepton.mvc.controller');
	ModuleManager::load('lepton.user.authentication');
	ModuleManager::load('lepton.mvc.routers.defaultrouter');

	// ModuleManager::load('lepton.web.*');

	class MvcApplication extends Application {
		function run() {
			Console::debug('I am routing right now!');
			// Create new router and invoke it
			$r = new DefaultRouter();
			$r->route();
		}
	}

?>
