<?php

/**
 * On-Demand loading of Lepton Modules
 * 
 * Note: A method for modules to specify that they are required to load is
 * neccessary. Also a method for the module to report what classes it supports.
 * This could be something like;
 * 
 * module::volatile(); // Mark as required
 * module::info('My Module'); // Describe module
 * module::provides('feature');
 * module::requires('feature');
 * 
 */

interface IModuleLoader {
    function queryClass($classname);
    function loadModule($modulename);
}

class OnDemandModuleLoader implements IModuleLoader {
    private $database;
    public function __construct() {
        $this->database = new FsPrefs(base::appPath().'/.modcache');
    }
    // autoload will be directed to this method
    public function queryClass($classname) {
        
    }
    // using() will be directed to this method
    public function loadModule($modulename) {
        
    }
}

// spl_autoload_register(array('ModuleManager','queryClass'),true,true);
modulemanager::setManager(new OnDemandModuleLoader());
