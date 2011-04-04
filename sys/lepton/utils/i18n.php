<?php __fileinfo("Internationalization (i18n) support");

class intl {

	public static $strings = array();
	private static $lang = null;
	private static $region = null;

	function str() {
		$args = func_get_args();
		if (self::getFullLanguage()) {
			if (count($args)>0) {
				if (isset(intl::$strings[self::getFullLanguage()])) {
					if (array_key_exists($args[0],intl::$strings[self::getFullLanguage()])) {
						$str = intl::$strings[self::getFullLanguage()][$args[0]];
					} else {
						$str = $args[0];
					}
				} else {
					$str = $args[0];
				}
				if (count($args)>1) {
					$str = sprintf($str,array_slice($args,1));
				}
			} else {
				$str = $args[0];
			}
		} else {
			if (count($args) > 0) {
				$str = $args[0];
				if (count($args) > 1) {
					$str = sprintf($str,array_slice($args,1));
				}
			} else {
				$str = $args[0];
			}
		}
		return $str;
	}

	static function registerLanguage($lang,$strings) {
		self::$strings[$lang] = (array)$strings;
	}

	static function setLanguage($lang) {
		if (preg_match('/^[a-z]{2}$/',$lang)) {
			// Two letter iso code found
			self::$lang = $lang;
			self::$region = null;
		} elseif (preg_match('/^[a-z]{2}-[a-z]{2}$/',$lang)) {
			// Two letter iso country and two letter iso code
			list(self::$lang,self::$region) = explode('-',$lang);
		} elseif ($lang == null) {
			self::$lang = null;
			self::$region = null;
		} else {
			throw new BaseException("Invalid language");
		}
	}

	static function getLanguage() {
		return self::$lang;
	}

	static function getRegion() {
		return self::$region;
	}

	static function getFullLanguage() {
		return self::$lang.((self::$region!=null)?'-'.self::$region:'');
	}

}
