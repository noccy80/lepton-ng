<?php

class SqlWhere {

	private $where;
	private $db;

	function __construct($field,$value) {
		$this->db = new DatabaseConnection();
		$this->where = $this->db->escape($field.'=%s',$value);
	}

	function andCond($field,$value) {
		$this->where.= ' AND '.$this->db->escape($field.'=%s',$value);
	}

	function orCond($field,$value) {
		$this->where.= ' AND '.$this->db->escape($field.'=%s',$value);
	}

	function __toString() {
		return 'WHERE '.$this->where;
	}

}

class SqlUpdateStatement {

	private $table;
	private $update;
	private $where;

	function __construct($table) {
		$this->table = $table;
		$this->update = $update;
		$this->where = $where;
	}

	function setData(Array $data) {
		$this->update = $data;
	}

	function setWhere(SqlWhere $where) {
		$this->where = (string)$where;
	}

	function execute() {
		$db = new DatabaseConnection();
		$stmt = "UPDATE ".$this->table ." SET ";
		$su = array();
		foreach($this->update as $key=>$val) {
			$su[] = $db->escape($key.'=%s', $val);
		}
		$stmt.= join(', ',$su);
		$stmt.=' '.$this->where;

		return $db->updateRow($stmt);
	}

}

class SqlInsertStatement {

	private $table;
	private $insert;

	function __construct($table) {
		$this->table = $table;
		$this->insert = array();
	}

	function setData(Array $data) {
		$this->insert[0] = $data;
	}

	function addData(Array $data) {
		$this->insert[] = $data;
	}

	function execute() {
		$db = new DatabaseConnection();
		$stmt = "INSERT INTO ".$this->table ." ";
		$rd = array();
		foreach($this->insert[0] as $key=>$val) {
			$rd[] = $key;
		}
		$stmt.= "(".join(',',$rd).") VALUES ";
		$si = array();
		foreach($this->insert as $row) {
			$sr = array();
			foreach ($row as $key=>$val) {
				$sr[] = $db->escape('%s', $val);
			}
			$si[] = '('.join(',',$sr).')';
		}
		$stmt.= join(', ',$si);

		return $db->insertRow($stmt);
	}

}