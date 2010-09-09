<?php

	class Document {

		const DT_HTML401_STRICT = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
		const DT_HTML401_TRANS = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
		const DT_HTML401_FRAMESET = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
		const DT_XHTML1_STRICT = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		const DT_XHTML1_TRANS = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		const DT_XHTML1_FRAMESET = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
		const DT_XHTML1_DTD = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
		const DT_XHTML11_BASIC = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">';

		static $_started = false;
		static $_doctype = null;
		static $_contenttype = null;

		static function begin($doctype = self::DT_HTML401_TRANS) {
			switch ($doctype) {
				case self::DT_HTML401_STRICT:
				case self::DT_HTML401_TRANS:
				case self::DT_HTML401_FRAMESET:
					Document::$_contenttype = 'text/html; charset='.config::get('lepton.charset');
					break;
				case self::DT_XHTML1_STRICT:
				case self::DT_XHTML1_TRANS:
				case self::DT_XHTML1_FRAMESET:
				case self::DT_XHTML1_DTD:
				case self::DT_XHTML11_BASIC:
					Document::$_contenttype = 'text/xhtml';
					break;
			}
			Document::$_doctype = $doctype;
			// @ob_clean();
			// ob_start(array(&$this,'obhandler'));
			if (!headers_sent()) {
				header('Content-type: '.Document::$_contenttype);
			}
			printf(Document::$_doctype."\n");
		}

		static function buffer() {
			@ob_start();
		}

		static function end() {
			if (ob_get_length()) {
				@ob_flush();
				@flush();
				@ob_end_flush();
			}
		}

		function includeView($view) {

		}

		function insertString($str) {
			return eval($str);
		}

		function obhandler($str) {
			// TODO: implement handler hooks to inject stylesheets etc
			return $str;
		}

		static function flush() {
			if (ob_get_length()) {
				@ob_flush();
				@flush();
				@ob_end_flush();
			}
			@ob_start();
		}

		function write() {
			$args = func_get_args();
			printf($args[0],array_slice($args,1));
		}

	}

?>
