<?php
/**
 * @file index.php
 *
 * Lepton/NG: MVC Application Entrypoint
 *
 * Part of Lepton/NG - (c) 2010, Noccy Labs
 * Distributed under the GNU GPL v3
 *
 * NOTE:
 * You probably don't have to change anything in here. Just make sure your
 * configuration is correct (located in your app/config directory)
 *
 * @license GPL v2
 * @author Christopher Vagnetoft <noccy@chillat.net>
 */

// Load configuration and the base system.
require('sys/base.php');
// debug::enable();

// Initialize an MVC application to handle the request for us
ModuleManager::load('lepton.base.mvc');
Lepton::run('MvcApplication','app');

?>
