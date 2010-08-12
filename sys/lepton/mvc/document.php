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
	
		private $_started = false;
		private $_doctype = null;
		private $_contenttype = null;
		
		function begin($doctype = self::DT_HTML401_TRANS) {
			switch ($this->_doctype) {
				case self::DT_HTML401_STRICT:
				case self::DT_HTML401_TRANS:
				case self::DT_HTML401_FRAMESET:
					$this->_contenttype = 'text/html; charset=utf-8';
					break;
				case self::DT_XHTML1_STRICT:
				case self::DT_XHTML1_TRANS:
				case self::DT_XHTML1_FRAMESET:
				case self::DT_XHTML1_DTD:
				case self::DT_XHTML11_BASIC:
					$this->_contenttype = 'text/xml+html; charset=utf-8';
					break;
			}
			$this->_doctype = $doctype;
			@ob_clean();
			header('Content-type: '.$this->_contenttype);
			ob_start(array(&$this,'obhandler'));
			printf($this->_doctype."\n");
			$this->_started = true;
		}
		
		function obhandler($str) {
			return $str;
		}
		
		function flush() {
			ob_flush();
		}
		
		function __destruct() {
			if ($this->_started) ob_end_flush();
		}		
		
		function write() {
			$args = func_get_args();
			printf($args[0],array_slice($args,1));
		}
	
	}
	
?>
