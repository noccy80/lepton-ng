<?php module("MVC Web Application framework", array(
    'version' => '1.0',
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>'
));

// Lepton MVC bootstrapper
using('lepton.mvc.model');
using('lepton.mvc.view');
using('lepton.mvc.controller');
using('lepton.mvc.request');
using('lepton.mvc.response');
using('lepton.mvc.session');
using('lepton.mvc.cookies');
using('lepton.user.authentication');
using('lepton.mvc.routers.defaultrouter');
using('lepton.mvc.templates');
using('lepton.mvc.document');
using('lepton.mvc.forms');
using('lepton.mvc.content');
using('lepton.mvc.secpolicy');
using('lepton.mvc.viewstate');
// Web libraries
using('lepton.web.*');

class MvcApplication extends Application {
	const KEY_MVC_ROUTER = 'lepton.mvc.router';
	static $app;
	function run($app='app') {
		MvcApplication::$app = $app;
		Console::debugEx(LOG_VERBOSE,__CLASS__,'Invoking router...');
		// Create new router and invoke it
		response::setStatus(200);
		$router = config::get(self::KEY_MVC_ROUTER,'DefaultRouter');
		$r = new $router();
		$r->route();
		return 0;
	}
}

declare(encoding = 'UTF-8');
