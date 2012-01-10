<?php

class Posix {
    
    static function getUserInfoById($uid) { 
        return posix_getpwuid($uid);
    }
    
    static function getUserInfoByName($username) {
        return posix_getpwnam($username);
    }
    
    static function getGroupInfoById($gid) {
        return posix_getgrgid($gid);
    }
    
    static function getGroupByName($name) {
        return posix_getgrnam($name);
    }
    
    static function getCurrentWorkingDirectory() {
        return posix_getcwd();
    }
    
    static function getLogin() {
        return posix_getlogin();
    }
    
    static function isFdTty($fd) {
        return posix_isatty($fd);
    }    
    
    static function kill($pid,$sig) {
        return posix_kill($pid,$sig);
    }
    
    static function getResourceLimits() {
        return posix_getrlimit();
    }
    
    static function getProcessTimes() {
        return posix_times();
    }
    
    static function getPid() {
        return posix_getpid();
    }
    
}