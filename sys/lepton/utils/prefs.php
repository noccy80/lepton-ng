<?php

interface IPrefs {
	function flush();
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
		return $this->data[$name];
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
	private $db;

	public function __construct($table) {
		$this->db = new DatabaseConnection();
		$this->table = $table;
		try {
			$tcheck = $this->db->getSingleRow("SHOW CREATE TABLE ".$this->table);
		} catch(Exception $e) {
			$tcheck = null;
		}
		$this->data = array();
		if (!$tcheck) {
			$this->db->exec("CREATE TABLE ".$this->table." (prefskey VARCHAR(64) NOT NULL PRIMARY KEY, data BLOB)");
		} else {
			$keys = $this->db->getRows("SELECT * FROM ".$this->table);
			foreach((array)$keys as $row) {
				$this->data[$row['prefskey']] = unserialize($row['data']);
			}
		}
	}

	public function flush() {
		foreach($this->data as $key=>$value) {
			$this->db->updateRow("REPLACE INTO ".$this->table." (prefskey,data) VALUES (%s,%s)", $key, serialize($value));
		}
	}
	
}