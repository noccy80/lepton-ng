<?php

class mvc {

	function setEncoding($enc) {
		response::contentType('text/html; charset='.$enc);
	}

	function utfdecode($str) {
		$enc = mb_detect_encoding($str);
		if ($enc == 'UTF-8') return utf8_decode($str);
		return $str;
	}

}
