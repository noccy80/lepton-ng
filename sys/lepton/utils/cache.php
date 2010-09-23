<?php

    class Cache {

        static $_has_apc = false;

        function __initialize() {
            if (function_exists('apc_add')) self::$_has_apc = true;
        }

    }

    Cache::__initialize();

?>
