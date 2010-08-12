<?php

	/*
	   Uncomment this option to create a debug log with all exceptions and errors.
	*/
	// config::set('lepton.mvc.debuglog',"/tmp/".$_SERVER['HTTP_HOST'].".log");

	config::set('lepton.mvc.exception.showdebug',true);
	config::set('lepton.mvc.exception.feedback',true);

	config::set('lepton.mvc.exception.log',false);
	config::set('lepton.mvc.exception.logfile',null); // Default: /tmp/HOSTNAME.log

?>
