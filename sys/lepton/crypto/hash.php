<?php

__fileinfo("Hashing Functions");

abstract class Hash {

    static function md5($string) {

        if (extension_loaded("hash")) {
            return hash("MD5", $string);
        } else {
           return md5($string);
        }

    }

    static function sha1($string) {

        if (extension_loaded("hash")) {
            return hash("SHA1", $string);
        } else {
            return sha1($string);
        }

    }

}
