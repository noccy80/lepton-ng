<?php

    class Document {

	static $doctypes = array(
        	'html/4.01 strict' 	=> '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
        	'html/4.01' 		=> '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
        	'html/4.01 frameset'	=> '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
        	'xhtml/1.0 strict'	=> '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
        	'xhtml/1.0'		=> '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
        	'xhtml/1.0 frameset'	=> '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
        	'xhtml/1.1 dtd'		=> '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
        	'xhtml/1.1 basic'	=> '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">'
	);

        static $_started = false;
        static $_doctype = null;
        static $_contenttype = null;

        static function begin($doctype = 'html/4.01') {
            $ct = explode('/',$doctype);
            switch ($ct[0]) {
                case 'html':
                    Document::$_contenttype = 'text/html; charset='.config::get('lepton.charset');
                    break;
                case 'xhtml':
                    Document::$_contenttype = 'text/xhtml';
                    break;
            }
            if (!isset(Document::$doctypes[$doctype])) {
                throw new BaseException("Unknown doctype ".$doctype);
            }
            Document::$_doctype = Document::$doctypes[$doctype];
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

        static function write() {
            $args = func_get_args();
            printf($args[0],array_slice($args,1));
        }

    }

?>
