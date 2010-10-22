<?php __fileinfo("Internationalization (i18n) support");

class intl {

	static $strings = array();
	static $language = null;

	function str() {
		$args = func_get_args();
		if (intl::$language) {
			if (count($args)>0) {
				if (isset(intl::$string[intl::$language])) {
					$str = intl::$string[intl::$language][$args[0]];
				} else {
					$str = intl::$string[intl::$language][$args[0]];
				}
				if (count($args)>1) {
					$str = sprintf($str,array_slice($args,1));
				}
			} else {
				$str = '';
			}
		} else {
			if (count($args) > 0) {
				$str = $args[0];
				if (count($args) > 1) {
					$str = sprintf($str,array_slice($args,1));
				}
			} else {
				$str = '';
			}
		}
		return $str;
	}

}
