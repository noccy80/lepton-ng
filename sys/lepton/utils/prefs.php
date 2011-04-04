<?php

interface IPrefs {
	function flush();
	function destroy();
}

abstract class Prefs {

	protected $data;

	function __destruct() {
		$this->flush();
	}

	public function  __set($name, $value) {
		$this->data[$name] = $value;
	}

	public function  __get($name) {
		if (isset($this->data[$name])) return $this->data[$name];
		return null;
	}

	public function  __unset($name) {
		if (isset($this->data[$name])) unset($this->data[$name]);
	}

	public function  __isset($name) {
		return (isset($this->data[$name]));
	}

}

class FsPrefs extends Prefs {

	private $filename;
	private $compress;

	public function __construct($filename,$compress=true) {
		$this->filename = $filename;
		$this->compress = $compress;
		if (!file_exists($this->filename)) {
			$this->data = array();
		} else {
			if ($this->compress) {
				$this->data = unserialize(gzuncompress(file_get_contents($this->filename)));
			} else {
				$this->data = unserialize(file_get_contents($this->filename));
			}
		}
	}

	public function flush() {
		// Save data to file
		if ($this->compress) {
			file_put_contents($this->filename, gzcompress(serialize($this->data)));
		} else {
			file_put_contents($this->filename, serialize($this->data));
		}
	}

}

class DbPrefs extends Prefs {

	private $table;
	private $db = null;

	public function __construct($table,$connection=null) {
		$this->db = new DatabaseConnection($connection);
		$this->table = $table;
		try {
			$tcheck = $this->db->getSingleRow("SHOW CREATE TABLE ".$this->table);
		} catch(Exception $e) {
			$tcheck = null;
		}
		$this->data = array();
		if (!$tcheck) {
			try {
				$this->db->exec("CREATE TABLE ".$this->table." (prefskey VARCHAR(64) NOT NULL PRIMARY KEY, data BLOB)");
			} catch(Exception $e) { }
		} 
		$keys = $this->db->getRows("SELECT * FROM ".$this->table);
		foreach((array)$keys as $row) {
			$this->data[$row['prefskey']] = unserialize($row['data']);
		}
	}

	public function flush() {
		if ($this->db) {
			foreach($this->data as $key=>$value) {
				$this->db->updateRow("REPLACE INTO ".$this->table." (prefskey,data) VALUES (%s,%s)", $key, serialize($value));
			}
		}
	}
	
	public function destroy() {
		if ($this->db) {
			$this->db->exec("DROP TABLE ".$this->table);
			unset($this->db);
		}
	}
	
}

class IniPrefs extends Prefs {

	public function __construct($filename) {
		$this->data = parse_ini_file($filename,true);
	}

	public function flush() {
		return; // This is read only
	}
	
	public function destroy() {
		return;
	}
		
}

class JsonPrefs extends Prefs {

	private $filename;

	public function __construct($filename) {
		$this->filename = $filename;
		if (file_exists($filename)) {
			$this->data = (array)json_decode(file_get_contents($filename),true);
		} else {
			$this->data = array();
		}
	}

	public function flush() {
		file_put_contents($this->filename, json_encode($this->data));
	}

	public function destroy() {
		return;
	}
	
}
