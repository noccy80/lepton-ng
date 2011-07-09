<?php

class LunitDatabaseLogger {

	function __construct($table) {

		$dbs = config::get('lunit.database', null);
		if ($dbs) {
			$db = new Database($dbs);
		} else {
			$db = new Database();
		}
		$this->db = $db;

	}

	function logTestResults($result) {
		var_dump($result);
	}

}
