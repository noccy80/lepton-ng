<?php

interface ITableSchema {
	function define();
}

abstract class SqlTableSchema implements ITableSchema {

	const COL_AUTO 		= 0x0001;
	const COL_NULLABLE	= 0x0002;
	const COL_FIXED 	= 0x0004;
	const COL_BINARY	= 0x0008;
	
	const KEY_INDEX 	= 0x0100;
	const KEY_UNIQUE 	= 0x0200;
	const KEY_FULLTEXT 	= 0x0400;
	const KEY_PRIMARY	= 0x0800;

	protected $name = null;
	protected $columns = array();
	protected $keys = array();
	protected $drop = false;

	protected function dropOnCreate($drop=true) {
		$this->drop = $drop;
	}
	protected function setName($name) {
		$this->name = $name;
	}
	protected function addColumn($name,$type,$options=null) {
		// do checks
		$this->columns[] = array(
			'name' => $name,
			'type' => $type,
			'opts' => $options
		);
	}
	protected function addIndex($name,array $columns,$type=self::KEY_INDEX) {
		// do checks
		$this->keys[] = array(
			'name' => $name,
			'type' => $type,
			'cols' => $columns
		);
	}
	static function apply(SqlTableSchema $schema) {
		$db = new DatabaseConnection();
		$sm = $db->getSchemaManager();
		$sm->apply($schema);
	}
	public function __construct() {
		$this->define();
	}
	public function getDefinition() {
		return array(
			'name' => $this->name,
			'columns' => $this->columns,
			'keys' => $this->keys,
			'drop' => $this->drop
		);
	}
}

interface ISqlTableSchemaManager {
	function apply(SqlTableSchema $schema);
}

abstract class SqlTableSchemaManager implements ISqlTableSchemaManager {
	protected $conn = null;
	function __construct($conn) {
		$this->conn = $conn;
	}
}
