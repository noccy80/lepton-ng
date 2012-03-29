<?php

/**
 * @brief String methods
 * @class string
 *
 *
 */
class String {
    const CHS_ALPHA='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-';
    const CHS_NUMERIC='0123456789';

    const KEY_CHARSET='lepton.string.charset';

    /**
     * @brief Strip a string, returning all that match the character set provided.
     *
     * @param string $string The input string
     * @param string $charset The character set to apply
     * @return string The output string.
     */
    static function strip($string, $charset) {
        $out = '';
        for ($ci = 0; $ci < strlen($string); $ci++) {
            if (strpos($charset, $string[$ci]) !== false) {
                $out.=$string[$ci];
            }
        }
        return $out;
    }

    function dequote($str) {
        $str = trim($str);
        $qt = $str[0];
        if (($qt == '"') || ($qt == "'" )) {
            if ($str[strlen($str) - 1] == $qt) {
                return substr($str, 1, strlen($str) - 2);
            }
        }
    }

    static function replace($str, $find, $replace) {
        return str_replace($find, $replace, $str);
    }

    static function rereplace($str, $find, $replace) {
        return preg_replace($find, $replace, $str);
    }

    /**
     * @brief Truncate a string, only returning a defined number of characters.
     * 
     * This method breaks on spaces for now but should be improved to keep
     * sentences intact.
     * 
     * @todo Fix the separation
     * 
     * @param string $string The string to crop
     * @param int $maxlen The maximum number of characters
     * @param string $append The string to apppend if the string is cut.
     * @return type 
     */
    static function truncate($string, $maxlen, $append='...') {
        if (strlen($string) > $maxlen) {
            // Find end of last word before maxlen
            $space = strrpos($string,' ',-(strlen($string) - $maxlen));
            if ($space <= 0) $space = $maxlen;
            return substr($string, 0, $space) . $append;
        }
        return $string;
    }

    /**
     * @brief Create a slug from a string.
     *
     * This will convert the string to lower case and replace all spaces and
     * non alpha characters with a dash (-) creating a string that can be used
     * in URLs to reference an item.
     *
     * @param string $string The input string
     * @return string The slug
     */
    static function slug($string) {
        $s = strToLower($string);
        $charset = strToUpper(config::get(self::KEY_CHARSET, 'utf-8'));
        $s = @iconv($charset, 'ASCII//TRANSLIT', $s);
        $s = preg_replace('/[^a-z0-9]/', '-', strToLower($s));
        $s = preg_replace('/-{2,}/', '-', $s);
        return $s;
    }

    static function cast($var) {
        return strval($var);
    }

    /**
     * @brief Escapes HTML entities in a string.
     *
     * @param string $str The string to process
     * @return string
     */
    static function htmlencode($str) {
        return htmlentities($str,ENT_COMPAT,config::get(self::KEY_CHARSET, 'utf-8'));
    }

    static function parseUri($str,$defaultns=null) {
        if (strpos(NS_SEPARATOR, $str) > 0) {
            $segments = explode(NS_SEPARATOR, $str);
            return array($segments[0], $segments[1]);
        } else {
            return array($defaultns,$str);
        }
    }

    public static function toLowerCase($string) {
        return strToLower($string);
    }

    public static function toUpperCase($string) {
        return strToLower($string);
    }

    public static function toProperCase($string) {
        return ucwords($string);
    }

    public static function length($string) {
        return strlen($string);
    }

    public static function like($pattern,$string) {
        if (typeOf($pattern) == 'array') {
            foreach($pattern as $pat) {
                if (fnmatch($pat,$string,FNM_CASEFOLD)) return true;
            }
            return false;
        } else {
            return fnmatch($pattern,$string,FNM_CASEFOLD);
        }
    }

    function encode($str=null) {
        if ($str==null) $str=$this->string;
        return htmlentities($str);
    }

    public function in(Array $list,$str=null) {
        if ($str==null) $str=$this->string;
        foreach($list as $item) {
            if (preg_match('/'.$item.'/',$str)) return true;
        }
        return false;
    }

    public function find($needle,$haystack,$offset=0,$ignorecase=true) {
        if ($ignorecase) {
            $pos = stripos($haystack,$needle,$offset);
        } else {
            $pos = strpos($haystack,$needle,$offset);
        }
        if ($pos === false) return null;
        return $pos;
    }


    public function likein(Array $list,$string) {
        foreach($list as $item) {
            if (fnmatch($item,$string)) return true;
        }
        return false;
    }

    public function getFrom($what,$after=true,$str=null) {
        if ($str==null) $str=$this->string;
        $pos = String::find($what,$str);
        if ($pos != String::NOTHING) {
            if ($after) $pos += String::len($what);
            return String::part(++$pos,String::len($str),$str);
        } else {
            return '';
        }
    }

    public function getTo($what,$before=true,$str=null) {
        if ($str==null) $str=$this->string;
        $pos = String::find($what,$str);
        if ($pos != String::NOTHING) {
            if ($before) $pos -= String::len($what);
            return String::part(1,$pos,$str);
        } else {
            return '';
        }
    }

    public function getNewline($what) {
        if (String::find("\r\n",$what) != String::NOTHING) return "\r\n";
        if (String::find("\r",$what) != String::NOTHING) return "\r";
        return "\n";
    }

    public function has($key,$str=null) {
        if ($str==null) $str=$this->string;
        $str = ' '.$str.' ';
        $pos = String::find(String::toUpperCase($key),
                            String::toUpperCase($str));
        return ($pos != String::NOTHING);
    }

    public function stripquotes($str) {
        $quotes = String::left(1,$str).String::right(1,$str);
        if ($quotes == '\'\'') {
            return String::part(2,String::len($str)-2,$str);
        } elseif ($quotes == '""') {
            return String::part(2,String::len($str)-2,$str);
        }
        return $str;
    }

    public function ifEmpty($str,$alt) {
        if ($str == '') return $alt;
        return $str;
    }

    public function getNamespace($default,$str=null) {
        if ($str==null) $str=$this->string;
        if (String::find(':',$str) >= 0) {
            $ns = explode(':',$str);
            return (string)$ns[0];
        } else {
            return $default;
        }
    }

    public function getLocation($str=null) {
        if ($str==null) $str=$this->string;
        if (String::find(':',$str) >= 0) {
            $ns = explode(':',$str);
            return $ns[1];
        } else {
            return $str;
        }
    }

    public function match($pattern,$str=null) {
        if ($str==null) $str=$this->string;
        return (bool)(preg_match($pattern,$str));
    }

    static function pad($string,$length,$padchar=' ',$padtype=STR_PAD_RIGHT) {
        return str_pad($string,$length,$padchar,$padtype);
    }

}

function string($s) { return new String($s); }
