<?php

///// Events /////////////////////////////////////////////////////////////////

class EventHandler {

    private $_class = null;
    private $_method = null;
    private $_uid = null;

    /**
     * Constructor
     *
     * @param Object $class The class name or a class instance
     * @param Mixed $method The method to invoke
     */
    public function __construct($class, $method) {
        $this->_class = $class;
        $this->_method = $method;
        $this->_uid = uniqid('ev', true);
    }

    /**
     * Called when the event is invoked. Normal users don't have to bother
     * with this.
     *
     * @param Mixed $event The event that is being dispatched
     * @param Array $data The data being passed to the event
     */
    public function dispatch($event, Array $data) {
        if ($this->_class) {
            if (is_string($this->_class)) {
                $ci = new $this->_class;
            } else {
                $ci = $this->_class;
            }
            return (call_user_func_array(array($ci, $this->_method), array($event, $data)) == true);
        } else {
            return (call_user_func_array($this->_method, array($event, $data)) == true);
        }
    }

    /**
     * Returns an events unique ID.
     *
     * @return Mixed The unique ID assigned to the event
     */
    public function getUniqueId() {
        return $this->_uid;
    }

}

/**
 * Manages various events
 */
abstract class Event {

    private static $_handlers = array();

    /**
     * Register an event handler
     *
     * @param Mixed $event The event to register
     * @param EventHandler $handler The EventHandler in charge of the event.
     */
    static function register($event, EventHandler $handler) {
        if (!arr::hasKey(self::$_handlers, strtolower($event))) {
            self::$_handlers[$event] = array();
        }
        self::$_handlers[$event][$handler->getUniqueId()] = $handler;
    }

    /**
     * Invoke a specif event
     *
     * @param Mixed $event The event to invoke
     * @param Array $data The data to pass to the handler
     */
    static function invoke($event, Array $data) {
        if (arr::hasKey(self::$_handlers, strtolower($event))) {
            foreach (self::$_handlers[$event] as $evt) {
                if ($evt->dispatch($event, $data) == true)
                    return true;
            }
        }
        return false;
    }

}

interface IEventList { }

abstract class CoreEvents implements IEventList {
    const EVENT_BEFORE_APPLICATION = 'lepton.application.before';
    const EVENT_AFTER_APPLICATION = 'lepton.application.after';
}
