<?php __fileinfo("L2Package Manager",array(
	'version' => '1.0.0'
));

	class L2PackageManager {

		const LOCK_FILE = '/tmp/l2lock';

		private $cachedb;
		private $hlockfile;

		function __construct() {

			$this->hlockfile = fopen(L2PackageManager::LOCK_FILE,"w");
			flock($this->hlockfile,LOCK_EX);

			$cachefile = SYS_PATH.'/.l2cache';
			if (file_exists($cachefile)) {
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

		function initCache() {
			$this->cachedb = new PDO("sqlite:".SYS_PATH.'/.l2cache');
		}

		function loadCache() {
			$this->cachedb = new PDO("sqlite:".SYS_PATH.'/.l2cache');
		}

		function saveCache() {

		}

		function registerPackage($package,$data) {

			
		}

		public function installPackage(L2Package $pkg) {
			
		}

		public function listPackages() {

			Console::writeLn(__astr("\b{Installed packages:}"));
			Console::writeLn(__astr("\b{Available packages:}"));
			$g = glob(APP_PATH.'/pkg/*.l2?');
			foreach($g as $package) {
				Console::write(__astr("    \b{%s}: "), $package);
				$p = new L2Package($package);
				Console::writeLn("%s (%s)", $p->getTitle(), $p->getVersion());
			}

		}

	}

	class L2Package {

		const PT_SITEPACK = 'l2package:app';
		const PT_SYSPACK = 'l2package:sys';

		private $title;
		private $description;
		private $filedb;
		private $version;

		function __construct($package = null) {
			$fn = 'zip://'.$package.'#'.basename($package,'.l2p').'/package.xml';
			$manifest = DOMDocument::load($fn);
			$xp = new DOMXPath($manifest);

			$q = $xp->query("/manifest/title");
			$this->title = ($q->length > 0)?$q->item(0)->nodeValue:'';

			$q = $xp->query("/manifest/version");
			$this->version = ($q->length > 0)?$q->item(0)->nodeValue:'';

			$q = $xp->query("/manifest/description");
			$this->description = ($q->length > 0)?$q->item(0)->nodeValue:'';

			$q = $xp->query("/manifest/title");
			$this->filedb = ($q->length > 0)?$q->item(0)->getAttribute('src'):'';
			printf($this->filedb);
		}

		public function getTitle() {
			return $this->title;
		}

		public function getDescription() {
			return $this->description;
		}

		public function getVersion() {
			return $this->version;
		}

		public function getFileDb() {
			
		}

	}
