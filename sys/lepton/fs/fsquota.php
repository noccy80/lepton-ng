<?php

	ModuleManager::load('lepton.fs.fs');

	class FsContainer {

		protected $_contents = array();
		public $_size = null;
		
		public function __construct($paths = null) {
			if (is_array($paths)) {
				foreach($paths as $path) { 
					$this->add($path);
				}
			}
		}

		public function recalculateSize() {
			$this->_size = 0;
			foreach ($this->_contents as $p) {
				$o = FsObject::get($p);
				$this->_size += $o->getSize();
			}
			return $this->_size;
		}

		public function add($path) {
			if (FsObject::exists($path)) {
				$this->_contents[] = $path;
			}
			if ($this->_size == null) $this->_size = 0;
			$this->_size += FsObject::getSize($path);
		}

		public function getSize($force = false, $formatted = false, $precision = 2) {
			if (($this->_size == null) || $force) {
				$this->recalculateSize();
			}
			return ($formatted ? FsUtil::bytesToHuman($this->_size, $precision) : $this->_size);
		}

	}

	class FsQuota extends FsContainer {

		const TYPE_USAGE = 1;
		const TYPE_FILE = 2;
		
		protected $_limit = null;
		
		public function setLimit($limit) {
			$this->_limit = $limit;
		}

		public function isFull() {
            if ($this->getSize() >= $this->_limit) {
                return true;
            }
            return false;
        }

		public function checkAgainst($path) {
            $f = FsObject::get($path);
            if (($this->getSize() + $f->getSize()) > $this->_limit) {
                return false;
            }
            return true;
        }

        public function getUsage() {
            return $this->getSize();
        }
		
		public function getUsagePercentage($limit = null) {
			if ($this->_limit == null && $limit == null) {
				throw new FsQuotaException("You must specify a limit!", FsQuotaException::ERR_NOLIMIT);
			} elseif ($limit == null) {
				$limit = $this->_limit;
			}
			return (($this->getUsage()/$limit)*100);
		}

	}

	class FsQuotaException extends Exception {
		const ERR_GENERIC = 0;
		const ERR_NOLIMIT = 1;
	}

?>
