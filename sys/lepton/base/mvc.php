<?php __fileinfo("MVC Web Application framework", array(
	'version' => '1.0',
	'author' => 'Christopher Vagnetoft <noccy@chillat.net>'
));

	// Lepton MVC bootstrapper

	ModuleManager::load('lepton.mvc.model');
	ModuleManager::load('lepton.mvc.view');
	ModuleManager::load('lepton.mvc.controller');
	ModuleManager::load('lepton.mvc.request');
	ModuleManager::load('lepton.mvc.response');
	ModuleManager::load('lepton.mvc.session');
	ModuleManager::load('lepton.user.authentication');
	ModuleManager::load('lepton.mvc.routers.defaultrouter');
	ModuleManager::load('lepton.mvc.templates');
	ModuleManager::load('lepton.mvc.document');

	ModuleManager::load('lepton.web.*');

	class MvcApplication extends Application {
		static $app;
		function run($app='app') {
			MvcApplication::$app = $app;
			Console::debugEx(LOG_VERBOSE,__CLASS__,'Invoking router...');
			// Create new router and invoke it
			$r = new DefaultRouter();
			$r->route();
			return 0;
		}
	}
	
	declare(encoding = 'UTF-8');

?>
