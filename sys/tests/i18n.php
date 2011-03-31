<?php

using('lunit.lunit');
using('lepton.utils.i18n');

/**
 * @description Internationalization and Localization
 */
class LeptonInternationalizationTests extends LunitCase {

	/**
	 * @description Setting languages
	 */
	function setlanguage() {
		intl::setLanguage('sv');
		$this->assertEquals(intl::getLanguage(),'sv');
		$this->assertEquals(intl::getFullLanguage(),'sv');
		intl::setLanguage('en-gb');
		$this->assertEquals(intl::getLanguage(),'en');
		$this->assertEquals(intl::getRegion(),'gb');
		$this->assertEquals(intl::getFullLanguage(),'en-gb');
	}

	/**
	 * @description Setting bad language throws exception
	 */
	function setbadlanguage() {
		intl::setLanguage('sv');
		try {
			intl::setLanguage('swe');
		} catch (Exception $e) {
			$this->assertEquals(intl::getLanguage(),'sv');
			return;
		}
		$this->explicitFail('No exception thrown');
	}

}

Lunit::register('LeptonInternationalizationTests');

