<?php

	class StringBuffer {

		private $str;

		function __construct() {
			$this->str = '';
		}

		function __toString() {
			return $this->str;
		}

		function write($str) {
			$this->str .= $str;
		}

		function writeln($str='') {
			$this->str .= $str . "\n";
		}

		function get() {
			return $this->str;
		}

	}

	class StringTable {

		private $strings;

		function __set($key,$string) {
			$this->strings[$key] = $string;
		}

		function __get($key) {
			return $this->strings[$key];
		}

		function __call($key, $args) {
			$args = array_merge(array($this->strings[$key]),$args);
			print_r($args);
			return call_user_func_array('sprintf', $args);
		}
	}

	/**
	 * String utility class
	 */
	class StringUtil {

		/**
		 * Returns the namespace and the URI of the specified URI.
		 *
		 * @param string $defaultns The default namespapce to apply
		 * @param string $uri The URI to extract the namespace from
		 * @return array The namespace and the URI
		 */
		static function getNamespaceURI($defaultns,$uri) {
			if (String::find(':',$uri) >= 0) {
				$nsuri = explode(':',$uri);
				return array((string)$nsuri[0],(string)$nsuri[1]);
			} else {
				return array((string)$defaultns,(string)$uri);
			}

		}

		/**
		 * Pad a string with the specified padding character up to the specific length.
		 *
		 * @param string $string The string to pad
		 * @param int $length The length to pad to
		 * @param string $padchar The character to pad with
		 * @param int $padtype Where to pad (STR_PAD_LEFT or STR_PAD_RIGHT)
		 * @return string The padded string
		 */
		static function padString($string,$length,$padchar=' ',$padtype=STR_PAD_RIGHT) {
			return str_pad($string,$length,$padchar,$padtype);
		}

	}

	class StringParser {
		private $string;
		function __construct($str) {
			$this->string = $str;
		}
		function replace($match,$repl=null) {
			if (get_class($repl) == 'Callback') {
				$this->string = preg_replace_callback($match, $repl->getCallback(), $this->string);
			} else {
				if (is_array($match)) {
					foreach($match as $cmatch => $crepl) {
						$this->string = preg_replace($cmatch,$crepl,$this->string);
					}
				} else {
					$this->string = preg_replace($match,$repl,$this->string);
				}
			}
		}
		function replaceEach($what,$patn,$repl) {
			foreach($what as $item) {
				$tpatn = preg_replace('/\$ITEM\$/',$item,$patn);
				$this->string = preg_replace($tpatn,$repl,$this->string);
			}
		}

		public function get() {
			return $this->string;
		}


	}

?>
