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
			Console::write("Initializing database ... ");
			$this->cachedb = new PDO("sqlite:".SYS_PATH.'/.l2cache');
			$this->cachedb->query("CREATE TABLE packages (id INT PRIMARY, package TEXT, description TEXT, version TEXT");
			Console::writeLn("Done");
		}

		function loadCache() {
			$this->cachedb = new PDO("sqlite:".SYS_PATH.'/.l2cache');
		}

		function saveCache() {

		}

		function registerPackage($package,$data) {

			
		}

		public function installPackage(L2Package $pkg) {

			Console::writeLn(__astr("Preparing package: \b{%s}"), $pkg->getFilename());
			$files = $pkg->getFiles();
			Console::writeLn(__astr("    \b{Name} : %s"), $pkg->getTitle());
			Console::writeLn(__astr("    \b{Files} : %d"), count($files));
			Console::write(__astr("Checking dependencies : "));
			$warn = 0;
			foreach($files as $file) {
				$fn = $file['filename'];
				$q = $this->cachedb->query(sprintf("SELECT * FROM files WHERE filename='%s'", $fn));
				if ($q) {
					Console::writeLn("Warning: File %s collides with other package", $fs);
					$warn++;
				} else {
					$lcpath = APP_PATH.str_replace('app','',$fn);
					if (file_exists($lcpath)) {
						Console::writeLn("Warning: File %s already exist in filesystem", $lcpath);
						// $warn++;
					}
				}
			}
			Console::writeLn(__astr(" Ok"));
			if($warn == 0) {
				foreach($files as $file) {
					$fn = $file['filename'];
					$fsrc = $fn;
					$fdest = APP_PATH.str_replace('app','',$fn);
					if (!file_exists(dirname($fdest))) {
						$dirname = dirname($fdest);
						$dn = $dirname;
						Console::writeLn(__astr("    \c{ltgray mkdir} %s"), $dirname);
						mkdir($dirname);
					}
					Console::writeLn(__astr("    \c{ltgray copy} %s => %s"), $fsrc, $fdest);
					$fnin = 'zip://'.$pkg->getFilename(true).'#'.$fsrc;
					$fr = fopen($fnin,'rb');
					$fw = fopen($fdest,'wb');
					if ($fr && $fw) {
						while (!feof($fr)) {
							$db = fread($fr,4096);
							fwrite($fw,$db);
						}
					} else {
						Console::writeLn(__astr("    \c{red error}: Could not open file"));
					}
					// TODO: Copy file, log everyting including mkdirs
				}
			}

		}

		public function removePackage(L2Package $pkg) {

			Console::writeLn(__astr("Preparing package: \b{%s}"), $pkg->getFilename());
			$files = $pkg->getFiles();
			Console::writeLn(__astr("    \b{Name} : %s"), $pkg->getTitle());
			Console::writeLn(__astr("    \b{Files} : %d"), count($files));

			foreach($files as $file) {
				$fn = $file['filename'];
				$fsrc = $fn;
				$fdest = APP_PATH.str_replace('app','',$fn);
				if (file_exists($fdest)) {
					Console::writeLn(__astr("    \c{ltgray delete} %s"), $fdest);
					unlink($fdest);
				}
			}

		}

		public function listPackages() {

			Console::writeLn(__astr("\b{Installed packages:}"));
			Console::writeLn(__astr("\b{Available packages:}"));
			$g = glob(APP_PATH.'/pkg/*.l2?');
			foreach($g as $package) {
				Console::write(__astr("    \b{%s}: "), basename($package));
				$p = new L2Package($package);
				Console::writeLn("%s (%s)", $p->getTitle(), $p->getVersion());
			}

		}

	}

	class L2Package {

		const PT_SITEPACK = 'l2package:app';
		const PT_SYSPACK = 'l2package:sys';

		private $title;
		private $package;
		private $packagename;
		private $description;
		private $filedb;
		private $version;
		private $filename;

		function __construct($package = null) {
			if ($package) $this->load($package);
		}

		function load($package) {
			$pn = APP_PATH.'/pkg/'.str_replace(APP_PATH.'/pkg/','',$package);
			if (file_exists($pn.".l2p")) {
				$pn .= ".l2p";
			}
			$this->package = $package;
			$this->packagename = $pn;
			if ($package && file_exists($pn)) {

				$this->filename = $package;

				$fn = 'zip://'.$pn.'#package.xml';
				$manifest = DOMDocument::load($fn);
				$xp = new DOMXPath($manifest);

				$q = $xp->query("/manifest/title");
				$this->title = ($q->length > 0)?$q->item(0)->nodeValue:'';

				$q = $xp->query("/manifest/version");
				$this->version = ($q->length > 0)?$q->item(0)->nodeValue:'';

				$q = $xp->query("/manifest/description");
				$this->description = ($q->length > 0)?$q->item(0)->nodeValue:'';

				$q = $xp->query("/manifest/filedb");
				$this->filedb = ($q->length > 0)?$q->item(0)->getAttribute('src'):'';

			}
		}

		public function getFilename($full=false) {
			if ($full) return $this->packagename;
			return $this->filename;
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

		public function getFiles() {
			Console::write("Reading file database: ");
			$fn = 'zip://'.$this->packagename.'#'.$this->filedb;
			Console::write("%s ... ", $fn);
			$fh = fopen($fn,'r');
			$files = array();
			while(!feof($fh)) {
				$fl = fgets($fh);
				while(strpos($fl,"  ") !== false) { $fl = str_replace("  "," ",$fl); }
				$fd = explode(" ", str_replace("\n","",$fl));
				$files[] = array(
					'filename' => $fd[1],
					'md5' => $fd[0]
				);
			}
			Console::writeLn("Done");
			return $files;
		}

	}
