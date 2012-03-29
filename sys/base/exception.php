<?php

////// Exceptions /////////////////////////////////////////////////////////////

/*
  Exception classes. Should all be derived from BaseException
 */
class BaseException extends Exception { }
class ModuleException extends BaseException { }
class NavigationException extends BaseException { }
class FilesystemException extends BaseException { }
class FileNotFoundException extends FilesystemException { 
    function __construct($msg,$filename=null) {
        parent::__construct(sprintf("%s (%s)", $msg, __printable($filename)));
    }
}
class FileAccessException extends FilesystemException { }
class UnsupportedPlatformException extends BaseException { }
class FunctionNotSupportedException extends BaseException { }
class SystemException extends BaseException { }
class ClassNotFoundException extends BaseException { }
class BadPropertyException extends BaseException { 
    function __construct($cname,$pname=null) {
        if (!$pname) { $this->message = $cname; return; }
        $this->message = sprintf("Property %s->%s does not exist", $cname, $pname);
    }
}
class ProtectedPropertyException extends BaseException { 
    function __construct($cname,$pname=null) {
        if (!$pname) { $this->message = $cname; return; }
        $this->message = sprintf("Property %s->%s is protected", $cname, $pname);
    }
}
class BadArgumentException extends BaseException { }
class CriticalException extends BaseException { }
class SecurityException extends CriticalException { 
    const ERR_ACCESS_DENIED = 1;
    const ERR_SESSION_INVALID = 2;
    const ERR_POLICY_BREACH = 3;
}
