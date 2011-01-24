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
	along with Warzone 2100; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

__fileinfo("CLI Package management", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));

class PackageAction extends Action {
    private $extn;
    public static $commands = array(
        'install' => array(
            'arguments' => '[\u{packagefile.l2p}|\u{packagename}|\u{...}]',
            'info' => 'Install a package from the web or a local file'
        ),
        'remove' => array(
            'arguments' => '[\u{packagename}]',
            'info' => 'Remove an installed package'
        ),
        'check' => array(
            'arguments' => '',
            'info' => 'List installed packages'
        ),
        'update' => array(
            'arguments' => '',
            'info' => 'Update package list'
        ),
        'upgrade' => array(
            'arguments' => '',
            'info' => 'Upgrade all installed packages'
        ),
        'search' => array(
            'arguments' => '[\u{querystring}]',
            'info' => 'Search for a package.'
        )
    );

    public function install($packages=null) {
        using('lepton.utils.l2package');
        $pm = new L2PackageManager();
        $pkg = new L2Package($pkgname);
        $pm->installPackage($pkg);
    }
    
    public function remove($package=null) {
        using('lepton.utils.l2package');
        $pm = new L2PackageManager();
        $pkg = new L2Package($pkgname);
        $pm->removePackage($pkg);
    }

    public function check() {
        using('lepton.utils.l2package');
        $pm = new L2PackageManager();
        $pm->listPackages();
    }   
}

actions::register(
	new PackageAction(),
	'package',
	'Package manager',
	PackageAction::$commands
);
