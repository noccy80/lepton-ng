<?php

/**
 * @brief Interface for preferences storage
 *
 */
interface IPrefs {
	/**
	 * @brief Save the preferences set
	 *
	 * Saves the preferences set to the storage
	 */
	function flush();
	/**
	 * @brief Destroy the preferences set
	 *
	 * This is NOT a destructor. It will drop the table/delete the file/void your
	 * dataset entirely!
	 */
	function destroy();
}

/**
 * @brief Preference Storage Base Class
 *
 *
 */
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

	public function get($name,$default=null) {
		if (isset($this->data[$name])) return $this->data[$name];
		return $default;
	}

	public function set($name,$value) {
		$this->data[$name] = $value;
	}

	public function  __isset($name) {
		return (isset($this->data[$name]));
	}

}

/**
 * @brief Filesystem backed Preference Storage
 *
 * Stores preferences in a gzip-compressed file consisting of serialized data
 * representing the options.
 */
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

/**
 * @brief Database backed Preferences Storage
 *
 *
 */
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

/**
 * @brief INI-file backed Preferences Storage
 *
 * This storage is read only
 */
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

/**
 * @brief JSON-file backed Preferences Storage
 *
 *
 */
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

/**
 * @brief Array backed Preferences Storage
 *
 * This storage is read only
 */
class ArrayPrefs extends Prefs {

	public function flush() { }

	public function destroy() { }

	public function __construct(Array $data) {
		$this->data = (array)$data;
	}

}
