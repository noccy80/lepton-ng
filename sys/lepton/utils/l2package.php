<?php

	class L2PackageManager {

		const LOCK_FILE = '/tmp/l2lock';

		private $cachedb;
		private $hlockfile;

		function __construct() {

			$this->hlockfile = fopen(L2PackageManager::LOCK_FILE,"w");
			flock($this->hlockfile,LOCK_EX);

			$cachefile = file_exists(SYS_PATH.'/.l2cache');
			if ($cachefile) {
				Console::debugEx(LOG_BASIC,__CLASS__,"Loading package cache from %s", $cachefile);
				$this->loadCache();
			} else {
				Console::debugEx(LOG_BASIC,__CLASS__,"Package cache not found");
				$this->initCache();
			}

		}

		function __destruct() {
			$this->saveCache();
			fclose($this->hlockfile);
			unlink(L2PackageManager::LOCK_FILE);
		}

		function loadCache() {
			$this->cachedb = DOMDocument::load(SYS_PATH.'/.l2cache');
		}

		function saveCache() {

		}

		function registerPackage($package,$data) {

			
		}

		public function install(L2Package $pkg) {
			
		}

	}

	class L2Package {

		const PT_SITEPACK = 'l2package:app';
		const PT_SYSPACK = 'l2package:sys';

		function __construct($package = null) {

		}

	}
