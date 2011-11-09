<?php

class Documentor {

    function __construct($filename) {
        $source = file_get_contents($filename);
        $tokens = token_get_all($source);
        $seg = array();
        $special = null;
        foreach ($tokens as $token) {
            if (!is_string($token)) {
                // var_dump($token);
                list($id, $text, $line) = $token;
                $tokname = token_name($id);
                // console::writeLn('%s: %s', $tokname, $text);
                switch($tokname) {

                    // Class flags
                    case 'T_ABSTRACT':
                        $seg['abstract'] = true; break;
                    case 'T_FINAL':
                        $seg['final'] = true; break;

                    // Implements and Extends
                    case 'T_IMPLEMENTS':
                        $special = 'implements';
                        break;
                    case 'T_EXTENDS':
                        $special = 'extends';
                        break;

                    // Object types
                    case 'T_CLASS':
                        $seg['type'] = 'class';
                        $special = 'class';
                        break;
                    case 'T_FUNCTION':
                        $seg['type'] = 'function';
                        $seg['args'] = array();
                        $special = 'function';
                        break;
                    case 'T_INTERFACE':
                        $seg['type'] = 'interface';
                        $special = 'interface';
                        break;

                    case 'T_VAR':
                        console::writeLn("VAR  : %s", $text);
                        break;
                    case 'T_VARIABLE':
                        //console::writeLn("VARIA: %s", $text);
                        if ($special) $seg['args'][] = $text;
                        break;
                    case 'T_STRING':
                        if ($special != null) { $seg[$special] = $text; }
                        // console::writeLn("%s %s", $special, $text);
                        if ($special != 'function') $special = null;
                        break;
                    case 'T_DOC_COMMENT':
                        var_dump($seg);
                        // console::writeLn("DOC  : %s", $text);
                        break;

                    case 'T_DOUBLE_COLON':
                        //console::writeLn('%s::', $lasttok);
                        break;
                    case 'T_OBJECT_OPERATOR':
                        //console::writeLn('%s->', $lasttok);
                        break;

                    default:
                        //console::writeLn("%s %s", $tokname, $text);
                }
            } else {
                if (($token == ')') && ($special == 'function')) $special = null;
            }
        }
    }

}

?>