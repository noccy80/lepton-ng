<?php __fileinfo("ANSI Highlighting and markup");

class Ansi {
    static $fgcolor = array(
        'black' => '0;30',
        'gray' => '1;30',
        'blue' => '0;34',   
        'ltblue' => '1;34',
        'green' => '0;32',
        'ltgreen' => '1;32',
        'cyan' => '0;36',
        'ltcyan' => '1;36',
        'red' => '0;31',
        'ltred' => '1;31',
        'purple' => '0;35',
        'ltpurple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'ltgray' => '0;37',
        'white' => '1;37',
        'default' => '39'
    );
    static function parse($str) {
        $s = $str;
        $s = preg_replace_callback('/\\\\b\{(.*?)\}/', array('Ansi','_cb_bold'), $s);
        $s = preg_replace_callback('/\\\\u\{(.*?)\}/', array('Ansi','_cb_undl'), $s);
        $s = preg_replace_callback('/\\\\i\{(.*?)\}/', array('Ansi','_cb_invert'), $s);
        $s = preg_replace_callback('/\\\\c\{(.*?)\}/', array('Ansi','_cb_color'), $s);
        $s = preg_replace_callback('/\\\\g\{(.*?)\}/', array('Ansi','_cb_gray'), $s);
        return $s;
    }
    static function _cb_bold($str) {
        $str = $str[1];
        return chr(27).'[1m'.$str.chr(27).'[0m';
    }
    static function _cb_undl($str) {
        $str = $str[1];
        return chr(27).'[4m'.$str.chr(27).'[0m';
    }
    static function _cb_invert($str) {
        $str = $str[1];
        return chr(27).'[3m'.$str.chr(27).'[0m';
    }
    static function _cb_gray($str) {
        return self::_cb_color(array(null,'ltgray '.$str[1]));
    }
    static function _cb_color($str) {
        $str = $str[1];
        $sa = explode(' ',$str);
        $color = $sa[0];
        $text = join(" ",array_slice($sa,1));
        return chr(27).'['.Ansi::$fgcolor[$color].'m'.$text.chr(27).'['.Ansi::$fgcolor['default'].'m';
    }
}

/**
 * Helper function to format a string with ANSI markup
 * 
 * @param String $str The string with markup
 * @return String The string with ANSI sequences
 */
function __astr($str) { return Ansi::parse($str); }

