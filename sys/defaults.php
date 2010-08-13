<?php

	// Charset to use
	config::set('lepton.charset','utf-8');

	// The default router instance
	Config::set('lepton.mvc.router', 'DefaultRouter');


	// If true, debug information will be shown when an unhandled exception 
	// occurs.
	config::set('lepton.mvc.exception.showdebug',true);
	// If true, the feedback form will be displayed. This requires either log, 
	// db or email below to be enabled.
	config::set('lepton.mvc.exception.feedback',false);
	// Save exceptions to a logfile. If true, specify filename or leave as
	// null. Default filename is /tmp/HOSTNAME-debug.log
	config::set('lepton.mvc.exception.log',false);
	config::set('lepton.mvc.exception.logfile',null);
	// Send exception information via e-mail. Requires an e-mail transport to
	// be enabled.
	config::set('lepton.mvc.exception.email',false);
	config::set('lepton.mvc.exception.email.to', null);

?>
