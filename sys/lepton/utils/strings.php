<?php __fileinfo("String Manipulation Utilities");

abstract class String {

    public static function toLowerCase($string) {
        return strToLower($string);
    }

    public static function toUpperCase($string) {
        return strToLower($string);
    }
    
    public static function length($string) {
        return strlen($string);
    }

}
