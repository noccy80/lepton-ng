<?php

using('lepton.cli.ansi');

abstract class Action {
    
}

abstract class Actions {

    private static $_actions = array();
    private static $_alias = array();

    public static function autocomplete($commands) {
        foreach (self::$_actions as $action => $data) {
            printf("%s\n", $action);
        }
    }

    public static function register(Action $action, $command, $description, $subcommands) {
        self::$_actions[$command] = array(
            'commands' => $subcommands,
            'info' => $description,
            'handler' => $action
        );
        foreach ($subcommands as $cmd => $cdata) {
            if (array_key_exists('alias', $cdata)) {
                self::$_alias[$cdata['alias']] = array($command, $cmd);
            }
        }
    }

    public static function invoke($command, $arguments) {
        if (array_key_exists($command, self::$_alias) == true) {
            $ca = self::$_alias[$command];
            $command = $ca[0];
            $arguments = array_merge(array($ca[1]), $arguments);
        }
        if (array_key_exists($command, self::$_actions) == true) {
            // Look up the sub command if any, otherwise show help
            if (count($arguments) == 0) {
                console::writeLn("Valid commands for %s:", $command);
                foreach (self::$_actions[$command]['commands'] as $cmd => $data) {
                    $argstr = $data['arguments'];
                    console::writeLn("    %s %s: %s", __astr('\b{' . $cmd . '}'), __astr($argstr), $data['info']);
                }
                return true;
            } else {
                if (method_exists(self::$_actions[$command]['handler'], $arguments[0])) {
                    call_user_func_array(
                        array(self::$_actions[$command]['handler'], $arguments[0]), array_slice($arguments, 1)
                    );
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public static function listActions() {
        console::writeLn("Valid actions:");
        foreach (self::$_actions as $action => $data) {
            console::writeLn("    %s: %s", __astr('\b{' . $action . '}'), $data['info']);
        }
    }

}
