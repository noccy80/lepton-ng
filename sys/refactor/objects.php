<?php

class RefactorFileset {

    public $files = array();

    public function __construct() {

        printf(__astr("[\b{fileset}] Created new refactoring fileset\n"));

    }

    public function addFiles($pattern) {

        $last = count($this->files);
        $dir = dirname($pattern);
        $match = basename($pattern);
        if (($dir[0] != '/') && ($dir[0] != '.')) {
            $dir = './'.$dir;
        }
        
        $files = file_find_all($dir,$match);
        foreach($files as $file) {
            $this->files[] = $file;
        }
        printf(__astr("[\b{fileset}] Added %s, %d files total (%d new)\n"), $pattern, count($files), count($files)-$last);

    }

}

