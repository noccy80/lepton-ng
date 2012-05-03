<?php

////// Logging Functionality //////////////////////////////////////////////////

interface ILoggerFactory {
    function __logMessage($priority, $message);
}

abstract class LoggerFactory implements ILoggerFactory {

}

class SyslogLoggerFactory extends LoggerFactory {

    private $verbose;
    private $logger;

    function __construct($perror=false, $facility=LOG_LOCAL0) {
        $this->verbose = $perror;
        $flag = LOG_PID;
        if ($perror)
            $flag |= LOG_PERROR;
        $this->logger = openlog(Lepton::getServerHostname(), $flag, $facility);
    }

    function __logMessage($prio, $msg) {
        syslog($prio, __fmt($msg));
    }

    function __destruct() {
        closelog();
    }

}

class DatabaseLoggerFactory extends LoggerFactory {

    function __logMessage($prio, $msg) {
        
    }

}

class EventLoggerFactory extends LoggerFactory {

    function __logMessage($prio, $msg) {
        event::invoke(debug::EVT_DEBUG, array($prio,$msg));
    }

}

class ConsoleLoggerFactory extends LoggerFactory {

    private static $level = array(
        'Base','Emerge','Alert','Critical','Warning','Notice','Info','Debug'
    );

    function __logMessage($prio,$msg) {
        $ts = @date("M-d H:i:s", time());
        $lines = explode("\n", $msg);
        foreach ($lines as $line) {
            if (defined('STDERR'))
                fprintf(STDERR, "%s %-20s %s\n", $ts, self::$level[$prio], $line);
            else
                printf("%s %-20s %s\n", $ts, self::$level[$prio], $line);
            //fprintf(STDERR, "%s | %-10s | %s\n", $ts, self::$level[$prio-1],$line);
        }
    }

}

class FileLoggerFactory extends LoggerFactory {

    private $filename = null;

    private static $level = array(
        'BASE','EMERG','ALERT','CRIT','WARN','NOTICE','INFO','DEBUG'
    );

    function __construct($filename) {
        $this->filename = $filename;
    }

    function __logMessage($prio,$msg) {
        $ts = @date("M-d H:i:s", time());
        $lines = explode("\n", $msg);
        $fh = fopen($this->filename,'a+');
        foreach ($lines as $line) {
            fprintf($fh, "%s %-20s %s\n", $ts, self::$level[$prio], $line);
            //fprintf(STDERR, "%s | %-10s | %s\n", $ts, self::$level[$prio-1],$line);
        }
        fclose($fh);
    }

}

abstract class Logger {

    static $_loggers = array();
    static $_logger = null;

    public static function emerg($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_EMERG, __fmt($arg));
    }

    public static function alert($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_ALERT, __fmt($arg));
    }

    public static function crit($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_CRIT, __fmt($arg));
    }

    public static function err($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_ERR, __fmt($arg));
    }

    public static function warning($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_WARNING, __fmt($arg));
    }

    public static function notice($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_NOTICE, __fmt($arg));
    }

    public static function info($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_INFO, __fmt($arg));
    }

    public static function debug($msgfmt) {
        $arg = func_get_args();
        self::__log(LOG_DEBUG, __fmt($arg));
    }

    public static function log($msgfmg) {
        $arg = func_get_args();
        self::__log(LOG_INFO, __fmt($arg));
    }

    public static function registerFactory(LoggerFactory $factory) {
        foreach(self::$_loggers as $logger) {
            if (typeOf($logger) == typeOf($factory)) {
                logger::warning('Attempting to register logger %s twice',typeOf($factory));
                return;
            }
        }
        self::$_loggers[] = $factory;
    }

    private static function __log($prio, $msg) {
        if ($prio <= base::logLevel()) {
            foreach (self::$_loggers as $logger) {
                $logger->__logMessage($prio, $msg);
            }
        }
    }

    public static function logEx($prio,$msg) {
        self::__log($prio,$msg);
    }

}