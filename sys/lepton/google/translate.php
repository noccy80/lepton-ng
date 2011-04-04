<?php

using('lepton.utils.translationservice');
using('lepton.net.httprequest');

class GoogleTranslate extends TranslationService {

	private $fromlang = null;
	private $tolang = null;

	function __construct($fromlang,$tolang) {

		$this->fromlang = reset(split('-',$fromlang));
		$this->tolang = reset(split('-',$tolang));
		
		console::debug('Translator for %s to %s created', $this->fromlang, $this->tolang);
	
	}

	function translate($string) {

		$langs = join('|',array($this->fromlang,$this->tolang));

		$translation = array();

		$url = 'http://ajax.googleapis.com/ajax/services/language/translate';
		$opts = array(
			'v' => '1.0',
			'q' => $string,
			'langpair' => $langs
		);
		$r = new HttpRequest($url,array(
			'parameters' => $opts
		));
		$rd = json_decode($r->responseText(),true);
		$rstr = $rd['responseData']['translatedText'];
		
		return $rstr;

	}

}
