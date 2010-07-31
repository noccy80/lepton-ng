<?php

	abstract class Model {
		function find($by,$value) {

		}
	}

	abstract class ActiveModel extends Model {

	}

	abstract class AbstractModel extends Model {

		public function __construct() {
			if (isset($this->fields)) {
				foreach($this->fields as $field=>$meta) {
					if (!$this->addField($field,$meta)) {
						Console::warn('Failed to define field %s as %s', $field, $meta);
					}
				}
			}
		}

		protected function addField($field,$meta) {
			// TODO: Verify the meta format
			return false;
		}

		public function __get($field) { }
		public function __set($field,$value) { }

	}


//////////////////////////// TESTING CODE /////////////////////////////////////

	class TestModel extends AbstractModel {
		var $fields = array(
			'name' => 'string',
			'age' => 'int'
		);
	}

	$tm = new TestModel();

?>
