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

__fileinfo("CLI Database Management", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));

class DatabaseAction extends Action {
    private $extn;
    public static $commands = array(
        'deploy' => array(
            'arguments' => '[\g{rootuser}]',
            'info' => 'Deploy the database tables',
            'alias' => 'deploy-db'
        ),
        'upgrade' => array(
            'arguments' => '',
            'info' => 'Apply upgrades from the dist/upgrades/*.sql',
            'alias' => 'db-upgrade'
        )
    );
    public function deploy($rootuser=null) {
        $db = config::get('lepton.db.default');
        switch($db['driver']) {
        case 'pdo/mysql':
        case 'mysql':
            console::writeLn("Using MySQL ...");
            break;
        default:
            console::fatal('This version of the script does not support anything else than MySQL');
            exit(1);
        }
    }
}

actions::register(
	new DatabaseAction(),
	'database',
	'Manage the databases',
	DatabaseAction::$commands
);
