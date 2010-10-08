<?php __fileinfo("SQL Helpers and Tools");

class TableDefinition {
	private $fields = null;
	private $meta = array();
	private $indexes = array();
	private $tablename;
	function __construct($tablename) {
		$this->tablename = $tablename;
		$this->fields = new BasicList();
	}
	function createTable() {
		$sql = array();
		$sql[] = "CREATE TABLE `".$this->tablename.'` (';
		$field = array();
		foreach($this->fields as $f) {
			$field[] = '`'.$f['field'].'` '.(string)$f['data'];
		}
		foreach($this->indexes as $i) {
			$field[] = $i;
		}
		$meta = array();
		foreach($this->meta as $m) {
			$meta[] = (string)$m;
		}
		$sql[] = '  '.join(",\n  ", $field);
		$sql[] = ')';
		$sqls = join(" ",
			array(
				join("\n",$sql),
				join(" ",$meta)
			)
		).";\n";
		return $sqls;
	}
	function addIndex($name,$type,$fields=null) {
		if ($fields != null) {
			$this->indexes[] = sprintf("%s KEY `%s` (`%s`)", strtoupper($type),
				$name, join('`,`', (array)$fields)
			);
		} else {
			$this->indexes[] = sprintf("%s KEY `%s` (`%s`)", strtoupper($type),
				$name, $name
			);
		}
	}
	function addMeta(SqlTableMeta $type) {
		$this->meta[] = $type;
	}
	function add($fieldname,FieldType $type) {
		$this->fields->add(array(
			'field' => $fieldname,
			'data' => $type
		));
	}
}

abstract class Field {
	// Field flags
	const FT_INT = 'int';
	const FT_CHAR = 'char';
	const FT_VARCHAR = 'varchar';
	const FT_FLOAT = 'float';
	// Field flags
	const FF_AUTO = 'auto_increment';
	const FF_NOTNULL ='not null';
	const FF_NULL = 'null';
	const FF_UNIQUE = 'unique';
}

abstract class SqlTableMeta { }
class SqlTableFunction extends SqlTableMeta {
	protected $fname = null;
	protected $value = null;
	function __construct($value) {
		$this->value = $value;
	}
	function __toString() {
		return sprintf("%s(%s)",$this->fname,$this->value);
	}
	function set($value) {
		$this->value = $value;
	}
}
class SqlTableSetting extends SqlTableMeta {
	protected $fname = null;
	protected $value = null;
	protected $sep = '=';
	function __construct($value) {
		$this->value = $value;
	}
	function __toString() {
		return sprintf("%s%s%s",$this->fname,$this->sep,$this->value);
	}
	function set($value) {
		$this->value = $value;
	}
}
class Table extends SqlTableSetting {
	function __construct($type,$sep,$value) {
		$this->fname = $type;
		$this->sep = $sep;
		parent::__construct($value);
	}
	static function type($ft) { 
		return new Table('TYPE','=',strtoupper($ft)); 
	}
	static function charset($cs) { 
		return new Table('CHARSET',' ',$cs);
	}
}

class FieldType {
	private $type;
	private $meta = array();
	function __construct($type, $options=null) {
		if (is_array($options)) {
			$this->meta = $options;
		} else {
			$args = func_get_args();
			$this->meta = array_slice(2,$args);
		}
		$this->type = $type;
	}
	function __set($key,$value) {
		$this->meta[$key] = $value;
	}
	function __get($key) {
		if ($key == 'type') {
			return $this->type;
		}
		return $this->meta[$key];
	}
	function __toString() {
		switch($this->type) {
			case field::FT_INT:
				$size = $this->meta[0];
				$props = array_slice($this->meta,1);
				foreach($props as $i=>$k) {
					$props[$i] = strtoupper($k);
				}
				$r = join(" ", array_merge(
					array("VARCHAR(".$size.")")
					,$props));
				return $r;
			case field::FT_CHAR:
				$size = $this->meta[0];
				$props = array_slice($this->meta,1);
				foreach($props as $i=>$k) {
					$props[$i] = strtoupper($k);
				}
				$r = join(" ", array_merge(
					array("VARCHAR(".$size.")")
					,$props));
				return $r;
			case field::FT_VARCHAR;
				$size = $this->meta[0];
				$props = array_slice($this->meta,1);
				foreach($props as $i=>$k) {
					$props[$i] = strtoupper($k);
				}
				$r = join(" ", array_merge(
					array("VARCHAR(".$size.")")
					,$props));
				return $r;
			case field::FT_FLOAT;
				$size = $this->meta[0];
				$decimal = $this->meta[1];
				$props = array_slice($this->meta,2);
				foreach($props as $i=>$k) {
					$props[$i] = strtoupper($k);
				}
				$r = join(" ", array_merge(
					array("FLOAT(".join(',',array($size,$decimal)).")")
					,$props));
				return $r;
		}	
	}
}

function IntType($opt=null) { $arg=(is_array($opt)?$opt:func_get_args()); return new FieldType(Field::FT_INT, $arg); }
function CharType($opt=null) { $arg=(is_array($opt)?$opt:func_get_args()); return new FieldType(Field::FT_CHAR, $arg); }
function VarcharType($opt=null) { $arg=(is_array($opt)?$opt:func_get_args()); return new FieldType(Field::FT_VARCHAR, $arg); }
function FloatType($opt=null) { $arg=(is_array($opt)?$opt:func_get_args()); return new FieldType(Field::FT_FLOAT, $arg); }

class TableField {

}
