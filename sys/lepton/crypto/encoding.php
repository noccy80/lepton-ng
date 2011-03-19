<?php

    interface IEncoding {
        function _encode($str);
        function _decode($str);
    }

    abstract class Encoding implements IEncoding {
        static function encode($encoding,$str) {
            $ecn = $encoding.'Encoding';
            $ec = new $ecn;
            return $ec->_encode($str);
        }
        static function decode($encoding,$str) {
            $ecn = $encoding.'Encoding';
            $ec = new $ecn;
            return $ec->_decode($str);
        }
    }

    class Base64Encoding extends Encoding {
        function _encode($str) { return (base64_encode($str)); }
        function _decode($str) { return (base64_decode($str)); }
    }

