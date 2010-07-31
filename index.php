<?php
//
//  You probably don't have to change anything in here. Just make sure your
//  configuration is correct.
//

//  Load configuration and the base system.
require('sys/base.php');

//  Initialize an MVC application
ModuleManager::load('lepton.base.mvc');
Lepton::run('MvcApplication');
