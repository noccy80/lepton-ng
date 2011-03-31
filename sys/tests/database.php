<?php

using('lunit.*');

/**
 * @description Database Tests
 */
class LeptonDbTests extends LunitCase {

	private $dbh = null;
	private $dbfn = null;

	function __construct() {
		$this->dbfn = $this->getTempFile();
		$this->dbh = new DatabaseConnection('pdo::sqlite:'.$this->dbfn);
	}

	/**
	 * @description SQLite: Create table
	 */
	function sl_create_table() {
		$this->dbh->exec('CREATE TABLE test (id INT, str TEXT)');
	}

	/**
	 * @description SQLite: Insert data into table
	 */
	function sl_insert_data() {
		$this->dbh->insertRow("INSERT INTO test (id,str) VALUES (%d,%s)", 1, 'foo');
	}

	/**
	 * @description SQLite: Delete data from table
	 */
	function sl_delete_data() {
		$this->dbh->updateRow("DELETE FROM test WHERE id=%d", 1);
	}

	/**
	 * @description SQLite: Dropping a table
	 */
	function sl_drop_table() {
		$this->dbh->exec('DROP TABLE test');
	}

	/**
	 * @description SQLite: Close by unset wrapper
	 */
	function sl_unset_singleton() {
		unset($this->dbh);
	}

}

Lunit::register('LeptonDbTests');

