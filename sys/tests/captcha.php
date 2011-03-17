<?php

using('lunit.*');

/**
 * @description Captcha implementation
 */
class LeptonCaptchaTests extends LunitCase {

	private $captchaid;
	private $captchatext;

	function __construct() {
		using('lepton.web.captcha');
		using('lepton.mvc.request');
		using('lepton.mvc.response');
		using('lepton.mvc.session');
	}

	/**
	 * @description Generating a new captcha
	 */
	function testgenerate() {
		config('lepton.captcha.font','arial.ttf');
		$this->captchaid = captcha::generate();
		$this->assertNotNull($this->captchaid);
		$this->captchatext = captcha::getstring($this->captchaid);
		$this->assertNotNull($this->captchatext);
	}
	
	/**
	 * @description Displaying/saving captcha
	 */
	function display() {
		captcha::display($this->captchaid,$this->getTempFile('png'));
	}
	
	/**
	 * @description Validating captcha string
	 */
	function validate() {
		$this->assertTrue(captcha::verify($this->captchatext,$this->captchaid));
	}

}

Lunit::register('LeptonCaptchaTests');
