<?php

class XsnpRequest {

    public function __set($key,$val) { }
    public function __get($key) { }
    public function __unSet($key) { }
    public function __isSet($key) { }

    function setData($data) { }
    function getData() { }

    function toRequest() { }

    function fromRequest($requestdata) { }

}

interface IXsnpHandler {
    function identify(XsnpRequest $request);
    function handleRequest(XsnpRequest $request)
}

abstract class XsnpHandler {

    private static $handlers = array();

    public static function registerHandler(XsnpHandler $handler) {
        self::$handlers[] = $handler;
    }

    public static function findHandlerForRequest(XsnpRequest $request) {
        foreach(self::$handlers as $handler) {
            if ($handler->identify($request)) {
                return $handler;
            }
        }
        return null;
    }

}
