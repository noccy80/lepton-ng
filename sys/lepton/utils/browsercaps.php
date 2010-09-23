<?php

    class Browser {
        static $__caps = null;
        static function __getCap($cap) {
            if (self::$__caps == null) 
                self::$__caps = get_browser(null, true);
            return (self::$__caps[$cap]);
        }
        static function isCrawler() {
            return self::__getCap('crawler');
        }
        static function getAllCaps() {
            if (self::$__caps == null) 
                self::$__caps = get_browser(null, true);
            return (self::$__caps);
        }
    }

?>
