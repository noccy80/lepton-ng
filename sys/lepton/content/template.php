<?php

class TemplateParser {

	private $_template = null;
	private $_vars = array();
	private $_parsed = null;

	function __construct($template) {
		$this->_template = $template;
	}

	function __toString() {
		return $this->parse();
	}

	function parse() {
		$this->_parsed = $this->_template;
		$this->_parsed = preg_replace_callback('/\$\{(.+?)\}/', array($this,'parsefield'), $this->_parsed);
		return $this->_parsed;
	}

	function addField($field,$value) {
		$this->_vars[$field] = $value;
	}

	function setFields(array $fields) {
		$this->_vars = $fields;
	}

	function parsefield($data) {
		$str = $data[1];
		$strx = explode(' ',$str);
		$svar = $strx[0];
		if (arr::hasKey($this->_vars,$svar)) {
			$rval = $this->_vars[$svar];
		} else {
			$rval = "[".$svar."]";
		}
        foreach(array_slice($strx,1) as $strs) {
            $alist = explode(':',$strs);
            switch($alist[0]) {
                case 'indent':
                    $rvalarr = explode("\n",$rval);
                    $indentstr = str_repeat(' ',intval($alist[1]));
                    var_dump($rvalarr);
                    $rval = $indentstr.join($indentstr, $rvalarr);
                    break;
                case 'wrap':
                    $rval = wordwrap($rval,intval($alist[1]));
                    break;
            }
        }
		return $rval;
	}

}
