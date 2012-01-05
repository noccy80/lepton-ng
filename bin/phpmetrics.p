#!/usr/bin/php
<?php require('lepton-ng');



class MetricsApp extends ConsoleApplication {

	public $arguments = array(
		array('v','verbose','Verbose operation'),
		array('q','quiet','Quiet operation'),
		array('p:','path','Path to scan'),
		array('h','help','Show help')
	);
	public $commands = array(
		array("scan", "Scan the source code and generate statistics")
	);
	protected $verbose = false;
	protected $quiet = false;

	function main($argc,$argv) {

		if ($this->getParameterCount() == 0) {
			$this->usage();
			return 1;
		}

		if ($this->hasArgument('p')) {
			$path = $this->getArgument('p');
		} else {
			$path = '.';
		}

		$this->verbose = $this->hasArgument('v');
		$this->quiet = $this->hasArgument('q');

		if (!$this->quiet) console::writeLn("Scanning source tree...");

		$items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
		$files = array();
		foreach($items as $item) {
			if (string::like('*.php',$item)) {
				$files[] = $item->getPathname();
			}
		}

		$tlines = 0;
		$tblanks = 0;
		$tbytes = 0;
		$tcomments = 0;
		foreach($files as $file) {
			$fc = file_get_contents($file);
			$bytes = strlen($fc);
			$fcs = explode("\n",$fc);
			$blanks = 0;
			$comments = 0;
			$cmtblock = false;
			foreach($fcs as $fl) {
				if (strpos($fl,'/*') !== false) { $cmtblock = true; $comments++; }
				if (strpos($fl,'*/') !== false) { $cmtblock = false; }
				if ($cmtblock) $comments++;
				if (substr(trim($fl),0,2) == '//') $comments++;
				if (trim($fl) == '') $blanks++;
			}
			$lines = count($fcs);
			if ($this->verbose) console::writeLn('%s: %d lines, %d characters', $file, $lines, $bytes);
			$tlines+= $lines;
			$tbytes+= $bytes;
			$tblanks+= $blanks;
			$tcomments+= $comments;
		}
		console::writeLn("Total lines:      %d", $tlines);
		console::writeLn("Total characters: %d", $tbytes);
		console::writeLn("Comment lines:    %d", $tcomments);
		console::writeLn("Empty lines:      %d", $tblanks);
		console::writeLn("Avg. line length: %.1f", $tbytes/($tlines-$tblanks));

	}

}

lepton::run('MetricsApp');
