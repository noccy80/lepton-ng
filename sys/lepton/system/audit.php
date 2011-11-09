<?php

/**
 * AuditLog Static Class
 *
 * Use the getLogger method to get a logger for a specific component
 *
 */
abstract class AuditLog {

    const LOG_SECURITY = 0x80;
    const LOG_WARNING = 0x40;
    const LOG_ERROR = 0x20;
    const LOG_INFORMATIVE = 0x10;

    public static function getLogger($component) {
        return new AuditLogger($component);
    }

    public static function getEventsByComponent($component) { }
    public static function getEventsBySeverity($severity) { }
    public static function getEvents() { }

    public static function purgeEvents($minage = null) { }

}

class AuditLogger {

    private $_component;

    public function __construct($component) {
        $this->_component = $component;
    }

    public function addEvent(AuditEvent $event) {
        $event->setComponent($this->_component);
        $cn = get_class($event);
        $sev = serialize($event);
        // Shove into DB -- echo $sev;
        echo $cn."\n\n".$sev."\n";
        $db = new DatabaseConnection();
        $db->insertRow(
            "INSERT INTO auditlog ".
            "eventclass,component,severity,eventdate,data) ".
            "VALUES (%s,%s,%d,%d,NOW(),%s)",
            $cn, $event->getComponent(), $event->getSeverity(), $event->getAssociatedUserId(), $sev);
    }

}

class AuditEvent {
    private $_message = null;
    private $_severity = null;
    private $_data = null;
    private $_user = null;
    private $_timestamp = null;
    private $_component = null;
    private $_uid = null;
    public function __construct($message,$severity,$data,$user) {
        $this->_message = $message;
        $this->_severity = $severity;
        $this->_data = $data;
        $this->_user = $user;
        $this->_timestamp = time();
    }
    public function setUid($uid) {
        $this->_uid = $uid;
    }
    public function getUid() {
        return $this->_uid;
    }
    public function setComponent($component) {
        $this->_component = $component;
    }
    public function getComponent() {
        return $this->_component;
    }
    public function getAssociatedUser() {
        if ($this->_user) {
            return user::getUser($this->_user);
        } else {
            return null;
        }
    }
    public function getAssociatedUserId() {
        if ($this->_user) {
            return $this->_user;
        } else {
            return null;
        }
    }
    public function getEventDate() {
        return $this->_timestamp;
    }
    public function getData() {
        return $this->_data;
    }
    public function getSeverity() {
        return $this->_severity;
    }
    public function getMessage() {
        return $this->_message;
    }
    public function __toString() {
        return $this->_message;
    }
}

class ExceptionAuditEvent extends AuditEvent { }

// $ae = AuditLog::getLogger('tester');
// $ae->addEvent(new ExceptionAuditEvent('Unhandled Exception',0,array(),user::getActiveUserId()));
// $ae->addEvent(new ExceptionAuditEvent('Unhandled Exception',0,array(),0));
