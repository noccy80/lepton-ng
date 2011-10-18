<?php module("Internationalization (i18n) support");

/**
 * @class intl
 *
 * Internationalisation and localisation functions
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @license GPL v3
 */
class intl {

	public static $strings = array();
	private static $lang = null;
	private static $region = null;

	/**
	 * @brief Return a translated formatted string
	 *
	 *
	 */
	static function str() {
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
					$str = call_user_func_array('sprintf', self::argreorder($str,array_slice($args,1)));
				}
			} else {
				$str = $args[0];
			}
		} else {
			if (count($args) > 0) {
				$str = $args[0];
				if (count($args) > 1) {
					$str = call_user_func_array('sprintf', self::argreorder($str,array_slice($args,1)));
				}
			} else {
				$str = $args[0];
			}
		}
		return $str;
	}
    
    /**
     * @brief Reorder the arguments based on the string and return both.
     * 
     * This is using a syntax that is compatible with GNU Gettext, namely 
     * adding the order followed by a $ in the middle of the declaration, for
     * example: "%2$s" would reference the 2nd argument as a string. This
     * would return an array consisting of "%s" and the 2nd option.
     * 
     * @param string $str The string containing the format
     * @param array $arg The arguments as an array
     * @return array The string and the arguments
     */
    private static function argreorder($str,$arg) {
        
        
        return array_merge((array)$str,(array)$arg);
    }

	/**
	 * @brief Register a new language
	 *
	 *
	 */
	static function registerLanguage($lang,$strings) {
		self::$strings[$lang] = (array)$strings;
	}

	/**
	 * @brief Set the default language
	 *
	 *
	 */
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

	/**
	 * @brief Get the assigned language
	 *
	 *
	 */
	static function getLanguage() {
		return self::$lang;
	}

	/**
	 * @brief Get the geographical region that match the language
	 *
	 *
	 */
	static function getRegion() {
		return self::$region;
	}

	/**
	 * @brief Return the full language string including region
	 *
	 *
	 */
	static function getFullLanguage() {
		return self::$lang.((self::$region!=null)?'-'.self::$region:'');
	}

}
