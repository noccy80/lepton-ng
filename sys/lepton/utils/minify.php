<?php

	class Minifier {

		const MFF_MINIMUM = 		0x00; ///< No options
		const MFF_STRIPCOMMENTS = 	0x01; ///< Strips comments from the file
		const MFF_OPTIMIZECOLORS = 	0x02; ///< Optimizes color codes, f.ex. #FFEEDD -> #FED
		const MFF_MAXIMUM = 		0xFF; ///< All options

		private $_data;

		function loadFromString($string) {
			$this->_data = $string;
		}

		function loadFromFile($file) {
			if (file_exists($file)) {
				$this->_data = file_get_contents($file);
			} else {
				throw new FileNotFoundException("Couldn't find file to minify: ".$file);
			}
		}

		function minify($flags) {
			$buffer = 
			return $buffer;
		}

	}

?>
