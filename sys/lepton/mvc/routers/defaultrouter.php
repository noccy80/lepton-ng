<?php

	ModuleManager::load('lepton.mvc.router');
	ModuleManager::load('lepton.mvc.controller');

	class DefaultRouter extends Router {
		function route() {
			
			Controller::invoke($method, $arguments);
		}
	}

?>
