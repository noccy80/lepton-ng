<?php

///// Utility Classes ////////////////////////////////////////////////////////

abstract class utils {

    static function iif($cond, $true, $false) {
        return ($cond) ? $true : $false;
    }

    static function ifnull($cond, $value) {
        return ($cond == null) ? $value : $cond;
    }

    static function inPath($path, $parent) {
        $preal = realpath($path);
        return (substr(strtolower($preal), 0, strlen($parent)) == strtolower($parent));
    }

}

// For the lazy developer in you
abstract class u extends utils { }


////// Utility Functions and Aliases //////////////////////////////////////////

function __fileinfo($strinfo, $vars=null) {
    module($strinfo, $vars);
}
function module($strinfo, $vars=null) {
    if (count(ModuleManager::$_order) > 0) {
        $mod = ModuleManager::$_order[count(ModuleManager::$_order) - 1];
        ModuleManager::$_modules[$mod]['modinfo'] = $strinfo;
        if ($vars != null) {
            foreach ($vars as $key => $var) {
                ModuleManager::$_modules[$mod][$key] = $var;
            }
            // Load dependencies
            if (isset($vars['depends']) && is_array($vars['depends'])) {
                $deps = (array) $vars['depends'];
                foreach ($vars['depends'] as $dep) {
                    ModuleManager::load($dep);
                }
            }
        }
    } else {
        Console::warn("Module reported modinfo '%s' without being requested?", $string);
    }
}

/**
 * @brief Return the list of classes that directly inherits from the class.
 * 
 * @param Object $baseclass The baes class to query
 * @return Array The list of descendants
 */
function getDescendants($baseclass) {

    $descendants = array();
    $cl = get_declared_classes();
    foreach($cl as $class) {
        $rc = new ReflectionClass($class);
        $pc = $rc->getParentClass();
        if ($pc) {
            $pcn = $pc->getName();
            if ($pcn == $baseclass) {
                $descendants[] = $rc->getName();
            }
        }
    }
    return $descendants;

}

/**
 * @brief Return the type of an object instance
 * 
 * @param Mixed $obj The object to retrieve the type of (or string, int etc)
 * @return String The type
 */
function typeof($obj) {
    if (is_object($obj)) {
        return get_class($obj);
    } else {
        return gettype($obj);
    }
}

function __fmt($args=null) {
    $args = (array)$args;
    if (count($args) == 0) {
        return "";
    } else if (count($args) == 1) {
        return $args[0];
    } else {
        return call_user_func_array('sprintf', $args);
    }
}

function __experimental() {
    if (config::get('lepton.core.experimental',false) == false) {
        logger::warning("The module %s is marked as experimental.", ModuleManager::getLastModuleName());
    }
}

function __callee() {
    $stack = debug_backtrace(false);
    $ret = sprintf('%s:%d', str_replace(base::basePath(),'',$stack[1]['file']), $stack[1]['line']);
    return $ret;
}

function __deprecated($oldfunc, $newfunc = null) {

    $stack = debug_backtrace(false);
    $method = $stack[1];
    if (!isset($method['file'])) {
        $caller = sprintf("%s%s%s (%s:%d)", $method['class'], $method['type'], $method['function'], '???', 0);
    } else {
        if (isset($method['type'])) {
            $caller = sprintf("%s%s%s (%s:%d)", $method['class'], $method['type'], $method['function'], str_replace(SYS_PATH, '', $method['file']), $method['line']);
        } else {
            $caller = sprintf("%s (%s:%d)", $method['function'], str_replace(SYS_PATH, '', $method['file']), $method['line']);
        }
    }

    // todo: add strict option to make deprecation warnings fatal

    if ($newfunc) {
        logger::warning('%s: Function %s is deprecated in favor of %s', $caller, $oldfunc, $newfunc);
        $msg = sprintf('%s: Function %s is deprecated in favor of %s', $caller, $oldfunc, $newfunc);
    } else {
        logger::warning('%s. Function %s is deprecated', $caller, $oldfunc);
        $msg = sprintf('%s. Function %s is deprecated', $caller, $oldfunc);
    }

    @trigger_error($msg, E_USER_DEPRECATED);
    if (config::get('lepton.base.strict', false) == true) {
        throw new BaseException($msg);
    }

}

function filename($filename) {
    return pathinfo($filename, PATHINFO_FILENAME);
}
function __filename($filename) { return filename($filename); }

function __fileext($filename) {
    return pathinfo($filename, PATHINFO_EXTENSION);
}

function __filepath($filename) {
    return pathinfo($filename, PATHINFO_DIRNAME);
}
    
function __strip_newline($str) {
    $str = str_replace("\r", "", $str);
    $str = str_replace("\n", "", $str);
    return $str;
}

function __fromprintable($str) {
    if (in_array($str[0], array('"', "'"))) {
        $qc = $str[0];
        if ($str[strlen($str) - 1] == $qc) {
            return substr($str, 1, strlen($str) - 2);
        }
    }
    switch ($str) {
        case 'NULL':
            return NULL;
        case 'false':
            return false;
        case 'true':
            return true;
        default:
            return $str;
    }
}

function __printable($var) {
    if (is_null($var)) {
        return "NULL";
    } elseif (is_bool($var)) {
        return ($var) ? 'true' : 'false';
    } elseif (is_string($var)) {
        return '"' . $var . '"';
    } else {
        return $var;
    }
}

function file_find($dir,$match) {

    $pattern = str_replace('//','/',$dir.'/*/'.$match);
    try {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($iterator as $path) {
            if (fnmatch($pattern,$path)) return $path;
        }
        return null;
    } catch(Exception $e) {
        return null;
    }
}


