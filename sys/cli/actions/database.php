<?php

/*
	This file is part of Lepton Framework.
	Copyright (C) 2001-2010  Noccy Labs

	Lepton Framework is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	Lepton Framework is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with the software; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

module("CLI Database Management", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));

class DatabaseAction extends Action {
    private $extn;
    public static $commands = array(
        'initialize' => array(
            'arguments' => '[\g{rootuser}]',
            'info' => 'Initialize the database tables',
            'alias' => 'initialize-db'
        ),
        'upgrade' => array(
            'arguments' => '',
            'info' => 'Apply upgrades from the dist/upgrades/*.sql',
            'alias' => 'db-upgrade'
        )
    );
    public function initialize($rootuser=null) {
        console::writeLn(__astr('\b{Initializing database}'));
        $db = config::get('lepton.db.default');
        $dbc = config::get('lepton.db.default');
        console::writeLn("  Database:  %s", $dbc['database']);
        console::writeLn("  User:      %s", $dbc['username']);
        console::writeLn("  Host:      %s", $dbc['hostname']);
        switch($db['driver']) {
        case 'pdo/mysql':
        case 'mysql':
            console::writeLn("  Driver:    MySQL");
            break;
        default:
            console::fatal('This version of the script does not support anything else than MySQL');
            exit(1);
        }

        console::writeLn(__astr("\n\b{Creating database and user}"));
        console::writeLn("  The script can create the database and the user. Hit enter to skip this step.");
        console::write("  Password for root user: ");
        $pass = console::readPass();
        if ($pass) {
            $db['database'] = null;
            $db['username'] = 'root';
            $db['password'] = $pass;
            config::set('lepton.db.default', $db);
            $conn = new DatabaseConnection();
            $dblist = $conn->getRows("SHOW DATABASES LIKE %s", $dbc['database']);
            if (count($dblist) == 0) {
                console::writeLn("Creating database...");
                try {
                    $conn->exec(sprintf("CREATE DATABASE %s;", $dbc['database']));
                } catch(Exception $e) {
                    console::writeLn("Not successful, does the database already exist?"); 
                }
            }
            console::writeLn("Creating user...");
            $conn->exec(sprintf("GRANT ALL ON %s.* TO %s@localhost IDENTIFIED BY '%s';", $dbc['database'], $dbc['username'], $dbc['password']));
            $conn->exec("USE ".$dbc['database']);
        } else {
            console::writeLn("No password specified, ignoring database and user creation.");
            $conn = new DatabaseConnection();
        }

        console::writeLn(__astr("\n\b{Importing tables}"));

        $f = glob(base::basePath().'/dist/sql/*.sql');
        foreach($f as $fn) {
            $fc = file_get_contents($fn);
            console::writeLn("  [sys] %s", basename($fn));
            $conn->exec($fc);
        }

        $f = glob(base::appPath().'/sql/*.sql');
        foreach($f as $fn) {
            $fc = file_get_contents($fn);
            console::writeLn("  [app] %s", basename($fn));
            $conn->exec($fc);
        }

        console::writeLn("All done.");
    }
}

actions::register(
	new DatabaseAction(),
	'database',
	'Manage the databases',
	DatabaseAction::$commands
);
