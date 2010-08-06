<?php

	/**
	 * Utility class for dealing with filesystem permissions.
	 *
	 * @author Christopher Vagnetoft <noccy@chillat.net>
	 */
	class FsMode {

		static function apply($current, $new) {
			if (is_string($new)) {
				// Expected format: u+rw g-x a-rwx
				$modifiers = explode(' ',$new);
				foreach($modifiers as $modifier) {
					if (preg_match('/([gua])([\+\-])([rwx]{3})/', $modifier, $op)) {
						// Find what set to alter
						switch($op[1]){
							case 'g': $base=0; break;
							case 'u': $base=3; break;
							case 'a': $base=6; break;
						}
						$mod = 0;
						// Set bits for flags
						if (strpos($op[3],'r')>=0) $mod=$mod|1;
						if (strpos($op[3],'w')>=0) $mod=$mod|2;
						if (strpos($op[3],'x')>=0) $mod=$mod|4;

						if ($op[2] == '+') {
							// If we're setting we or it shifted left by $base
							$current = $current | ($mod << $base);
						} else {
							// If we're removing we're anding it with a not mask
							$current = $current & ~($mod << $base);
						}
						return $current;
					} else {
						throw new FsException('Invalid mode', FsException::ERR_INVALID_MODE);
					}
				}
			} else {
				throw new FsException('Invalid mode', FsException::ERR_INVALID_MODE);
			}
		}

	}

	/**
	 * Commonly used filesystem functions.
	 *
	 * @todo complete
	 * @author David Gidwani <dav@fudmenot.info>
	 */
	class FsUtil {

		/**
		 * Join multiple paths
		 *
		 * @param array $paths An array of paths to join
		 * @return string
		 */
		static function joinPaths($paths) {
			if (!is_array($paths)) {
				$paths = func_get_args();
			}
			$prefix = '';
			if (substr($paths[0], 0, 1) == '/') {
                $prefix = '/';
            }
			foreach ($paths as &$path) {
                $path = trim($path, DIRECTORY_SEPARATOR);
			}
			return $prefix . join(DIRECTORY_SEPARATOR, $paths);
		}

		/**
		 * Convert a number of bytes to a human readable string.
		 *
		 * @param int $bytes
		 * @param int $precision Precision in number of decimal places
		 * @return string
		 */
		static function bytesToHuman($bytes, $precision = 2) {
			$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
			$i = 0;
			while ($bytes >= 1000) {
				$i++;
				$bytes = round(($bytes/1000), $precision);
			}
			return $bytes . " " . $units[$i];
		}

		static function humanToBytes($string) {
			// TODO: handle KiB, mbit, etc
			$units = array_flip(array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'));
			if (preg_match('/([\d\.]+) ([KMGTPEZY]?B)/', $string, $matches)) {
				$size = (float)$matches[1];
				$unit = $matches[2];
				if ($unit == 'B') return $size;
				return ($size*pow(1000,$units[$unit]+1));
			}
		}

		static function getHeader($filename) {

			$header = false;
			$meta = array();
			$fi = file($filename);
			foreach($fi as $line) {
				if (substr($line, 0,2) == "//") {
					$line = trim(substr($line,3));
					$header = true;
					if (strpos($line,':') > 0) {
						$i = strpos($line,':');
						$key = substr($line,0,$i);
						$data = substr($line,$i+1);
						$meta[$key] = trim($data);
					}
				} else {
					if ($header == true) break;
				}
			}

			return $meta;

		}

	}

	/**
	 * Represents a filesystem object.
	 *
	 * Use the factory method FsObject::get() to obtain a proper instance, i.e.,
	 * either FsFile or FsDirectory.
	 *
	 * @todo finishing!
	 * @author David Gidwani <dav@fudmenot.info>
	 */
	abstract class FsObject {

		protected $_path;

		public function __construct($path) {
			if (!file_exists($path)) {
				throw new FsException('Path not found', FsException::ERR_NONEXISTANT);
			}
			$this->_path = $path;
		}

		static function get($path) {
			$absolute = realpath($path);
			if (!file_exists($absolute)) {
				throw new FsException('Path not found', FsException::ERR_NONEXISTANT);
			} elseif (is_file($absolute)) {
				return new FsFile($absolute);
			} elseif (is_dir($absolute)) {
				return new FsDirectory($absolute);
			} else {
				throw new FsException('Unhandled file type', FsException::ERR_UNHANDLED_TYPE);
			}
		}

		static function globPath($path) {
			return array_map(array(FsObject,'get'), glob($path));
		}

		/**
		 * Return the "raw" path, as passed to the constructor.
		 *
		 * @note this is not necessarily relative, if you did anything like joinPath or so.
		 * @return string
		 */
		public function getRaw() {
			return $this->_path;
		}

		/**
		 * Get the absolute path (realpath) of the current path.
		 *
		 * @return string String if the path exists, otherwise false.
		 */
		public function getAbsolute() {
			return realpath($this->_path);
		}

		/**
		 * Get the parent directory of the current path.
		 *
		 * @return string
		 */
		public function getDirname() {
			return dirname($this->getAbsolute());
		}

		/**
		 * Get the basename of the current path.
		 *
		 * @return string
		 */
		public function getBasename() {
			return basename($this->getAbsolute());
		}

		/**
		 * Get disk usage statistics, similar to what is returned by the gnu
		 * du command. Use this method together with the list php command
		 * to parse the array:
		 *   list($total,$used) = $f->getDiskUsage();
		 *
		 * @param bool $human If true return human readable sizes
		 * @return array Array of total and used space
		 */
		public function getDiskUsage($human=false) {
			$total = disk_total_space($this->getAbsolute());
			$used = $total - disk_free_space($this->getAbsolute());
			if ($human) {
				return array(FsUtil::bytesToHuman($total), FsUtil::bytesToHuman($used));
			} else {
				return array($total, $used);
			}
		}

		/**
		 * Check if the path exists. Useful if you need to know on the fly.
		 *
		 * @return bool True if the path exists, otherwise false.
		 */
		public function exists($path = null) {
			if (isset($this) && get_class($this) == __CLASS__) {
				return file_exists($this->getAbsolute());
			} else {
				return file_exists($path);
			}
		}

		/**
		 * Delete this file or directory.
		 *
		 * @return bool True on success, false on failure.
		 */
		public function remove() {
			return unlink($this->getAbsolute());
		}

		public function rename($newname, $preservepath = true) {
			if ($preservepath) {
				$newname = $this->joinPath($newname);
			} else {
				$this->_path = $newname;
			}
			return rename($this->getAbsolute(), $newname);
		}

		public function move($dest) {
			return $this->rename($dest, false);
		}

		/**
		 * Check if the path is a file.
		 *
		 * @return bool
		 */
		public function isFile() {
			return is_file($this->getAbsolute());
		}

		/**
		 * Check if the path is a directory.
		 *
		 * @return bool
		 */
		public function isDirectory() {
			return is_dir($this->getAbsolute());
		}

		/**
		 * Check if the path is a symbolic link.
		 *
		 * @note does not work with Windows "shortcuts"
		 * @return bool
		 */
		public function isLink() {
			return is_link($this->getAbsolute());
		}

		/**
		 * Join another path into the current path.
		 *
		 * @note the new path will always be absolute.
		 * @param bool $replace Whether or not to replace this path.
		 * @return string The new, concatenated path.
		 */
		public function joinPath($path, $replace = true) {
			$newpath = FsUtil::joinPaths(array($this->getAbsolute(), $path));
			if ($replace && file_exists($newpath)) {
				$this->_path = $newpath;
			}
			return $newpath;
		}

		/**
		 * Create a directory.
		 *
		 * @param string $path Either absolute, or relative to the current path
		 * @param int $mode Octal mode, defaults to 0777.
		 * @param bool $recursive If true, create any directory hierarchies recursively.
		 * @return bool True on success, otherwise false.
		 */
		function mkdir($path, $mode = 0777, $recursive = false) {
			if (isset($this) && get_class($this) == __CLASS__) {
				return mkdir($this->joinPath($path), $mode, $recursive);
			} else {
				return mkdir($path, $mode, $recursive);
			}
		}

		/**
		 * Creates or modifies the access time of a file.
		 *
		 * @param int $filename The filename to create
		 * @param int $time The touch time
		 * @param int $atime The access time
		 * @return True on success, false on failure.
		 */
		public function touch($filename = null, $time = null, $atime = null) {
			$time = $time ? (int)$time : time();
			$atime = $atime ? $atime : $time;
			if (isset($this) && get_class($this) == __CLASS__) {
				return touch($this->getAbsolute(), $time, $atime);
			} else {
				if ($filename) {
					if ($this->isFile()) {
						return touch($filename, $time, $atime);
					} elseif ($this->isDirectory()) {
						return touch($this->joinPath($filename, false), $time, $atime);
					}
				} else {
					throw new FsException('You must provide a filename to touch', FsException::ERR_GENERIC);
				}
			}
		}

		/**
		 * Checks if the resource matches the specified pattern. Can only be
		 * used in an object context.
		 *
		 * @param string $pattern The pattern to match against
		 * @return boolean True if the pattern matches
		 */
		public function like($pattern) {
			if (isset($this) && get_class($this) == __CLASS__) {
				return (fnmatch($pattern,$this->getBasename()));
			} else {
				throw new FsException('FsObject::like() is only available in object context', FsException::ERR_GENERIC);
			}
		}

		public function getSize($path = null) {
			if (!(isset($this) && get_class($this) == __CLASS__)) {
				$o = FsObject::get($path);
				if ($o->isFile()) {
					return $o->getSize();
				} elseif ($o->isDirectory()) {
					return $o->getTotalSize();
				}
			}
		}
	}

	/**
	 * Represents a file.
	 *
	 * @todo allow multiple open handles.
	 * @author David Gidwani <dav@fudmenot.info>
	 */
	class FsFile extends FsObject {

		const MODE_READ = 'r';
		const MODE_WRITE = 'w';
		const MODE_APPEND = 'a+';

		protected $_handle;

		public function __construct($path) {
			parent::__construct($path);
			if (!$this->isFile()) {
				throw new FsException('"'.$path.'" is not a file', FsException::ERR_NOT_A_FILE);
			}
		}

		/**
		 * Destructor, close the handle if it's opened
		 */
		public function __destruct() {
			$this->close();
		}

		/**
		 * Open a file and return a handle.
		 *
		 * @param string $mode See PHP's documentation on fopen
		 * @return handle Handle to the file
		 */
		public function open($mode = FsFile::MODE_READ) {
			if (!$this->_handle) {
				$this->_handle = fopen($this->getAbsolute(), $mode);
			}
		}

		/**
		 * Close a file handle.
		 *
		 * @return True on success, false on failure, null if no handle was open.
		 */
		public function close() {
			if ($this->_handle) {
				clearstatcache();
				$r = fclose($this->_handle);
				unset($this->_handle);
				return $r;
			}
			return null;
		}

		/**
		 * Read a line from a file.
		 *
		 * @param int $length At which point to stop reading
		 * @return string
		 */
		public function readLine($length = 4096) {
			$this->open();
			$buf = fgets($this->_handle, $length);
			if (feof($this->_handle)) {
				$this->close();
			}
			return $buf;
		}

		/**
		 * Binary safe read.
		 *
		 * @return string The data on success, otherwise false.
		 */
		public function readBytes($length) {
			$this->open();
			$buf = fread($this->_handle, $length);
			if (feof($this->_handle)) {
				$this->close();
			}
			return $buf;
		}

		/**
		 * Read all data from a file.
		 *
		 * @return string The contents of the file. False on failure to read the file.
		 */
		public function readAll() {
			return file_get_contents($this->getAbsolute());
		}

		/**
		 * Get the size of the file.
		 *
		 * @param bool $formatted If false, return size in bytes. If true, return size in a human readable format.
		 * @param int $precision The precision in decimal places.
		 * @return An integer if $formatted is false, otherwise a string.
		 */
		public function getSize($formatted = false, $precision = 2) {
			$bytes = filesize($this->getAbsolute());
			return ($formatted ? FsUtil::bytesToHuman($bytes, $precision) : $bytes);
		}

		/**
		 * Write data to a file.
		 *
		 * @param string $data The data to write
		 * @param int $length At which point to stop writing
		 * @return True on success, false on failure.
		 */
		public function write($data, $length = null) {
			$this->open(FsFile::MODE_APPEND);
			if (!$length) {
				$rv = fwrite($this->_handle, $data);
			} else {
				$rv = fwrite($this->_handle, $data, $length);
			}
			return $rv;
		}

	}

	/**
	 * Represents a directory.
	 *
	 * @author David Gidwani <dav@fudmenot.info>
	 */
	class FsDirectory extends FsObject implements IteratorAggregate {

		protected $_handle;

		public function __construct($path) {
			parent::__construct($path);
			if (!$this->isDirectory()) {
				throw new FsException('"'.$path.'" is not a directory', FsException::ERR_NOT_A_DIR);
			}
		}

		/**
		 * Opens and returns a directory handle.
		 *
		 * @return handle
		 */
		public function getHandle() {
			if (!$this->_handle) $this->_handle = opendir($this->getAbsolute());
			return $this->_handle;
		}

		/**
		 * Close the directory handle
		 *
		 * @return If the directory was open, returns true. Otherwise false.
		 */
		public function close() {
			if ($this->_handle) {
				closedir($this->_handle);
				return true;
			}
			return false;
		}

		/**
		 * Just like the built-in glob.
		 *
		 * @return Array on success, false on failure.
		 */
		public function glob($pattern, $flags = 0) {
			return array_map(FsObject::get, glob($this->joinPath($pattern, false), $flags));
		}

		/**
		 * Get the total size (recursive) of a directory.
		 *
		 * @return float Number of bytes on success, or false on failure.
		 */
		public function getTotalSize($formatted = false, $precision = 2) {
			$bytes = 0;
			$iter = new RecursiveDirectoryIterator($this->getAbsolute());
		    foreach(new RecursiveIteratorIterator($iter) as $p) {
		    	$bytes += $p->getSize();
		    }
			return ($formatted ? FsUtil::bytesToHuman($bytes, $precision) : $bytes);
		}

		/**
		 * Retrieve an array of all files and folders. (as FsObject objects)
		 *
		 */
		public function getListing() {
			$result = array();
			foreach (new DirectoryIterator($this->getAbsolute()) as $p) {
				if (!$p->isDot()) {
                    $result[] = FsObject::get($p->getPathname());
                    return $result;
                }
			}
			return $result;
		}
		
		public function getIterator() {
			return $this->getListing();
		}

	}

	class FsException extends BaseException {
		const ERR_GENERIC = 0;
		const ERR_NONEXISTANT = 1;
		const ERR_NOT_A_FILE = 2;
		const ERR_NOT_A_DIR = 3;
		const ERR_INVALID_MODE = 4;
		const ERR_UNHANDLED_TYPE = 5;
	}

?>
