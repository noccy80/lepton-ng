<?php

using('lepton.utils.translationservice');
using('lepton.net.httprequest');

class GoogleTranslate extends TranslationService {

	private $fromlang = null;
	private $tolang = null;
	private $apiversion = 1;

	function __construct($fromlang,$tolang,$apiversion=1) {

		$this->fromlang = reset(split('-',$fromlang));
		$this->tolang = reset(split('-',$tolang));
		
		console::debug('Translator for %s to %s created', $this->fromlang, $this->tolang);
	
	}

	function translate($string) {

		switch($this->apiversion) {
		case 1:
			$langs = join('|',array($this->fromlang,$this->tolang));
			$translation = array();

			$url = 'http://ajax.googleapis.com/ajax/services/language/translate';
			$r = new HttpRequest($url,array(
				'parameters' => array(
					'v' => '1.0',
					'q' => $string,
					'langpair' => $langs
				)
			));
			$rd = json_decode($r->responseText(),true);
			$rstr = $rd['responseData']['translatedText'];
			break;
		case 2:
		default:
			throw new BaseException("GoogleTranslate API Version 2 not implemented");
			$rstr = $string;
			break;
		}

		return $rstr;
	}

}
