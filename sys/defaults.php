<?php module("Default settings for Lepton Application Framework", array(
    'version' => '2011.02.01'
));

    // Strict mode
    config::set('lepton.base.strict',false); // Fail on deprecation warning

    // Charset to use
    config::set('lepton.charset','utf-8');

    // The default router instance
    config::set('lepton.mvc.router', 'DefaultRouter');

    // Syslog configuration
    config::set('lepton.debug.syslog',false);
    config::set('lepton.debug.syslog.facility', LOG_DAEMON); // Will use LOG_USER on Windows platforms
    config::set('lepton.debug.syslog.level',null); // Highest debug level to save
    config::set('lepton.debug.syslog.tee',null); // Tee to a file

    // Loggers. Set first parameter of constructor to true to echo errors to
    // stderr, parameter two can be used to set the target facility.
    logger::registerFactory(new SyslogLoggerFactory());
    logger::registerFactory(new EventLoggerFactory());
    // You can also register a DatabaseLoggerFacility:
    // logger::registerFactory(new DatabaseLoggerFacility("logtable"));

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
    // Log to audit log (in db)
    config::set('lepton.mvc.exception.audit',true);

    // Should view::embed throw exceptions? This will force output buffering.
    config::set('lepton.mvc.embed.exception',false);

    // Session save handler - default to null
    config::set('lepton.session.savehandler', null);

    // Authentication backend to use, you probably want to leave this at nouveau.
    config::set('lepton.user.authbackend','NouveauAuthBackend');
    // Hashing algorithm, can be any supported by hash_algos()
    config::set('lepton.user.hashalgorithm','md5');
    // If users should be disabled by default, if this is true users need to be
    // activated before being allowed to log in.
    config::set('lepton.user.disabledbydefault', false);
    // Defaults for new user backend
    config::set('lepton.user.hashing.rounds', 4);
    config::set('lepton.user.hashing.saltlen', 16);
    config::set('lepton.user.hashing.algorithms', array(
        'sha512',
        'sha256',
        'snefru256',
        'ripemd256',
        'sha224',
        'whirlpool',
        'ripemd160',
        'sha1',
        'md5'
    ));

    // What class should be responsible for showing the available payment options?
    config::set('lepton.ec.paymentselector', 'DefaultPaymentSelector');

    // Mail backends
    config::set('lepton.mail.backends', array(
       'lepton.net.mailbox.filesystem'
    ));

    // Strict sessions protect against session hijacking attacks
    config::set('lepton.security.strictsessions', true);
    
    // Use SecurityException in case of a security-related failure. Unless
    // you specifically handle the exceptions, you should leave this as
    // false, causing the execution to end.
    config::set('lepton.security.useexceptions', false);

    // Transitional include of viewhandlers
    using('lepton.mvc.viewhandler.php');
