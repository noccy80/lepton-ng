<?php __fileinfo("Default settings for Lepton Application Framework", array(
    'version' => '2010.09.06'
));

    // Charset to use
    config::set('lepton.charset','utf-8');


    // The default router instance
    config::set('lepton.mvc.router', 'DefaultRouter');


    // Syslog configuration
    config::set('lepton.debug.syslog',false);
    config::set('lepton.debug.syslog.facility', LOG_DAEMON); // Will use LOG_USER on Windows platforms


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


    // Session save handler - default to null
    config::set('lepton.session.savehandler', null);


    // Authentication backend to use, you probably want to leave this at default.
    config::set('lepton.user.authbackend','DefaultAuthBackend');
    // Hashing algorithm, can be any supported by hash_algos()
    config::set('lepton.user.hashalgorithm','md5');
    // If users should be disabled by default
    config::set('lepton.user.disabledbydefault', false);    
    
?>
