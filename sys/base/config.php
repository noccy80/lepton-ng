<?php

////// Configuration //////////////////////////////////////////////////////////

/**
 * @class Config
 * @brief Configuration management
 *
 * This is a light re-implementation to give global access to config-
 * uration values.
 * 
 * @author Christoper Vagnetoft <noccy@chillat.net>
 */
abstract class Config {

    static $values = array();

    /**
     * @brief Get a configuration value (or an array of them).
     * Call with the key you want to query, or with a wildcard.
     *
     * @param String $key The key to query
     * @param Mixed $default The value to return if key is empty
     * @return Mixed The value or the default value.
     */
    static function get($key, $default=null) {
        if (strpos($key, '*') !== false) {
            $ol = array();
            foreach (Config::$values as $ckey => $val) {
                if (preg_match('/' . str_replace('*', '.*', $key) . '/', $ckey)) {
                    $ol[$ckey] = $val;
                }
            }
            return $ol;
        } else {
            if (isset(Config::$values[$key])) {
                return Config::$values[$key];
            } else {
                return $default;
            }
        }
    }

    /**
     * @brief Set a configuration value.
     *
     * @param String $key The key to set
     * @param Mixed $value The value to set
     */
    static function set($key, $value) {
        Config::$values[$key] = $value;
    }

    /**
     * @brief Push the value onto a configuration key
     *
     * @param String $key The key to update
     * @param Mixed $value The value to push
     */
    static function push($key, $value) {
        if (isset(Config::$values[$key])) {
            $old = (array) Config::$values[$key];
        } else {
            $old = array();
        }
        $old[] = $value;
        Config::$values[$key] = $old;
    }

    /**
     * @brief Check if a key is set.
     *
     * @param String $key The key to check
     * @return Bool True if the key is set
     */
    static function has($key) {
        return (isset(Config::$values[$key]));
    }

    /**
     * @brief Set a default value.
     * Will only change the value if it's not set.
     *
     * @param String $key The key to set
     * @param Mixed $default The default to set if empty
     */
    static function def($key, $default) {
        if (!isset(Config::$values[$key]))
            Config::$values[$key] = $default;
    }

    /**
     * @brief Clear a configuration key
     *
     * @param String $key The key to clear
     */
    static function clr($key) {
        if (strpos($key, '*') !== false) {
            $kv = array();
            foreach (Config::$values as $ckey => $val) {
                if (preg_match('/' . str_replace('*', '.*', $key) . '/', $ckey)) {
                    unset(config::$values[$ckey]);
                    $kv[] = $ckey;
                }
            }
            return $kv;
        } else {
            unset(config::$values[$key]);
            return $key;
        }
    }

}

/**
 * Configuration helper function
 *
 * @param String $key The key to query/set
 * @param Mixed $value The new value (optional)
 * @return Mixed The configuration value
 */
function config($key,$value=null) {
    $ret = config::get($key);
    if ($value) config::set($key,$value);
    return $ret;
}
