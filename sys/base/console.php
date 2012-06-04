<?php

class Readline {
    static $completer = null;
    
    /**
     * @brief Set the default autocompleter
     *
     */
    static function setAutoCompleter(ReadlineAutoCompleter $completer = null) {
        self::$completer = $completer;
    }
    
    /**
     * @brief Return the default autocompleter instance
     *
     */
    static function getAutoCompleter() {
        return self::$completer;
    }
    
    /**
     * @brief Read a line, optionally setting the completer
     *
     * @param string $prompt The prompt to display
     * @param ReadlineAutoCompleter $completer The completer instance
     * @param boolean $autohistory Add command to history automatically (default is false)
     */
    static function read($prompt = null, ReadlineAutoCompleter $completer = null, $autohistory = false) {
        $oldcompleter = null;
        // If we got an autocompleter, assign it
        if ($completer) {
            // Save a copy of the old completer if any
            if ((self::$completer) && ($completer !== self::$completer)) {
                $oldcompleter = self::$completer;
            }
            // Assign and set up the proxy call
            self::$completer = $completer;
            readline_completion_function(array('Readline','_rlAutoCompleteProxy'));
        }

        // Read the line
        $ret = readline($prompt);

        // Restore old completer (if any) and add the command to the history
        // if autohistory is enabled.
        if ($oldcompleter) self::$completer = $oldcompleter;
        if ($autohistory) self::addHistory($ret);
        
        return $ret;
    }
    
    /**
     * @brief Add a command to the history
     *
     */
    static function addHistory($command) {
        readline_add_history($command);
    }
    
    /**
     * @brief Proxy for autocomplete calls
     * @internal
     */
    static function _rlAutoCompleteProxy($input,$index) {
        // Collect some information and perform the call if we have a completer
        // assigned.
        $info = readline_info();
        if (self::$completer) {
            $matches = self::$completer->complete($input,$index,$info);
        } else {
            // Maybe return true here if no data would help from the crash?
            $matches = array();
        }
        // Check the number of matches, and push a null character if empty,
        // in order to avoid the php segfault bug.
        if (count($matches) == 0) $matches[] = chr(0);
        // Return the data
        return $matches;
    }
}

/**
 * @brief Readline AutoCompleter abstract base class
 */
abstract class ReadlineAutoCompleter {
    abstract function complete($input,$index,$info);
}

/**
 * @brief Array-based autocompleter
 *
 */
class BasicReadlineAutoCompleter extends ReadlineAutoCompleter {
    private $commands = array();
    function __construct(Array $commands) {
        $this->commands = $commands;
    }
    function complete($input,$index,$info) {

        // Figure out what the entire input is
        $full_input = substr($info['line_buffer'], 0, $info['end']);

        // Initialize an empty array for our return data and then get all the
        // matches based on the entire input buffer.
        $matches = array();
        foreach ($this->commands as $phrase) {
            // Add any matches to the return array
            if (substr($phrase,0,strlen($input)) == $input) {
                $matches[] = $phrase;
            }
        }
        // And return it
        return $matches;
    
    }

}
