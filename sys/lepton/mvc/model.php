<?php

	abstract class Model {
		function find($by,$value) {

		}
	}

	abstract class ActiveModel extends Model {

	}

	abstract class AbstractModel extends Model {

		protected $_fields;
		protected $_data;
		protected $_index;

		public function __construct($initial=null) {
			if (!isset($this->model)) {
				Console::warn('Bad model doesn\'t contain $model variable');
			}
			if (isset($this->fields)) {
				foreach($this->fields as $field=>$meta) {
					if (!$this->addField($field,$meta)) {
						Console::warn('Failed to define field %s as %s', $field, $meta);
					}
				}
			}
			$this->clear();
			if ($initial) {
				foreach($this->_fields as $field=>$meta) {
					if (($meta['required']) && (!isset($initial[$field]))) {
						Console::warn('Initializin withoutg required data (%s)', $field);
					}
				}
				foreach($initial as $field=>$data) { $this->_data[$field] = $data; }
			}
		}

		protected function addField($field,$meta) {
			// TODO: Verify the meta format
			$md = explode(' ',$meta); $mi = 0;
			$ftype = null; $fdef = null; $freq = false; $fprot = false;
			while($mi < count($md)) {
				Console::debug('Parsing abstract model field %s: %s', $field, $md[$mi]);
				switch(strtolower($md[$mi])) {
					case 'string':
						$ftype = 'STRING';
						break;
					case 'int':
						$ftype = 'INT';
						break;
					case 'bool':
						$ftype = 'BOOL';
						break;
					case 'required':
						$freq = true;
						break;
					case 'protected':
						$fprot = true;
						break;
					case 'index':
						$this->_index = $field;
						break;
					case 'default':
						$fdef = join(' ',array_slice($md, $mi+1, count($md)));
						$mi = count($md);
				}
				$mi++;
			}
			if (($ftype)) {
				$this->_fields[$field] = array(
					'type' => $ftype,
					'required' => $freq,
					'default' => $fdef,
					'protected' => $fprot
				);
				return true;
			} else {
				Console::warn('Bad type specified for field %s in AbstractModel implementation', $field);
				Console::backtrace();
			}
			return false;
		}

		public function clear() {
			foreach($this->_fields as $field=>$meta) {
				$this->_data[$field] = $meta['default'];
			}
		}

		public function inspect() {
			Console::writeLn('Inspecting model %s:', $this->model);
			foreach($this->_data as $field=>$data) {
				Console::writeLn('  %s = %s', $field, $data);
			}
		}

		public function __get($field) {
			if (isset($this->_fields[$field])) {
				return ($this->_data[$field]);
			} else {
				throw new Exception("No such field in model");
			}
		}
		public function __set($field,$value) {
			if (isset($this->_fields[$field])) {
				$this->_data[$field] = $value;
				Console::writeLn("Setting %s.%s to %s", $this->model, $field, $value);
			} else {
				throw new Exception("No such field in model");
			}
		}

	}


//////////////////////////// TESTING CODE /////////////////////////////////////

	class TestModel extends AbstractModel {
		var $model = 'TestModel';
		var $fields = array(
			'name' => 'string required default untitled user',
			'age' => 'int default 0',
			'active' => 'bool default false'
		);
	}

	$tm = new TestModel(array(
		'age' => 30,
		'name' => 'bob'
	));

?>
