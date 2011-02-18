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

__fileinfo("CLI System Information", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));


class ForexAction extends Action {
    private $extn;
    public static $commands = array(
        'update' => array(
            'arguments' => '',
            'info' => 'Update the forex tables'
        ),
        'convert' => array(
            'arguments' => '\g{amount} \g{from} \g{to}',
            'info' => 'Convert the amount from one currency to another'
        )
    );

    public function update() {
        using('lepton.web.forex');
        $cc = new CurrencyExchange();
        console::writeLn("Updating symbols...");
        $cc->update();
    }

    public function convert($fv,$fc,$tc) {
        using('lepton.web.forex');
        $cc = new CurrencyExchange();
        $tv = $cc->convert($fv,$fc,$tc);
        console::writeLn("%0.2f %s = %0.2f %s", $fv, $fc, $tv, $tc);
    }


}

actions::register(
	new ForexAction(),
	'forex',
	'FOReign EXchange - Currency conversion',
	ForexAction::$commands
);
