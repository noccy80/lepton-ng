#!/usr/bin/php
<?php

require('sys/base.php');

if (!modulemanager::has('lepton.db.database')) {
    die("You need to configure your database connection first!\n");
}

class WpImportApplication extends ConsoleApplication {
    private $_xml = null;
    private $_xp = null;
    public $arguments = array(
        array('f:','file','File name to import from'),
        array('v','verbose','Be verbose'),
        array('p','posts','Import posts'),
        array('c','categories','Import categories'),
        array('t','tags','Import tags'),
        array('g','pages','Import pages'),
        array('a','all','Import everything (-pct)'),
        array('h','help','Show this help')
    );
    public $description = 'WordPress Data Importer';
    public function main($argv,$argc) {
        if (!$this->hasArgument('f')) {
            console::error('You need to specify a file to import with -f');
            return 1;
        }
        $filename = $this->getArgument('f');
        console::writeLn("Importing from %s ...",basename($filename));
        $this->_xml = DomDocument::load($filename);
        $this->_xp = new DomXpath($this->_xml);
        console::write("Analyzing feed ");
    }
}

lepton::run('WpImportApplication');
