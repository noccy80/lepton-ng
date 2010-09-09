<?php

	class AuditLog {

		static function getLogger($component) {
			return new AuditLogger($component);
		}

	}

	class AuditLogger {

		private $_component;

		public function __construct($component) {
			$this->_component = $component;
		}

		public function addEvent(AuditEvent $event) {

		}

	}

	class AuditEvent {

		

	}