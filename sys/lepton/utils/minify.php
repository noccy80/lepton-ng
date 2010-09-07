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
			$sepchars = array('(',')',';','}','{','/*','*/',':');
			$killchars = array("\t","\r","\n");
			$buffer = $this->_data;
			foreach($killchars as $char) $buffer = str_replace($char," ",$buffer);
			$result = array();
			preg_match_all( '/(?ims)([a-z0-9\s\.\:#_\-@\>\*]+)\{([^\}]*)\}/', $buffer, $arr); 
			foreach ($arr[0] as $i => $x) {
				$selector = trim($arr[1][$i]);
				$rules = explode(';', trim($arr[2][$i]));
				if (!isset($result[$selector])) {
					$result[$selector] = array(
						'selector' => $selector,
						'rules' => array()
					);
				}
				foreach ($rules as $strRule) {
					if (!empty($strRule)) {
						$rule = explode(":", $strRule);
						$result[$selector]['rules'][trim($rule[0])] = trim($rule[1]);
					}
				}
			}
			sort($result);
			$sout = "";
			foreach($result as $selector=>$rules) {
				$sout.=$rules['selector'].'{';
				$ob = array();
				foreach($rules['rules'] as $rule=>$value) {
					$ob[] = $rule.':'.$value;
				}
				$sout.=join(';',$ob).';}';
			}

			return $sout;

		}

		function minifytok($flags) {
			$sepchars = array('(',')',';','}','{','/*','*/',':');
			$killchars = array("\t","\r","\n");
			$buffer = $this->_data;

			// Replace tokens that are delimiters and remove the kill characters.
			foreach($sepchars as $char) $buffer = str_replace($char," ".$char." ",$buffer);
			foreach($killchars as $char) $buffer = str_replace($char," ",$buffer);
			while(strpos($buffer,'  ')>0) $buffer=str_replace('  ',' ',$buffer);

			// Explode our parsed buffer into tokens and process them
			$toks = explode(" ",$buffer);
			$bout = array();
			$boutd = array();
			$mute = false; // in comment
			$defn = false; // in definition
			for($n=0;$n<count($toks);$n++) {
				if ($toks[$n] == "/*") {
					$mute = true;
				} elseif( $toks[$n] == "*/") {
					$mute = false;
				} elseif( $toks[$n] == "{") {
					$defn = true;
				} elseif( $toks[$n] == "}") {
					for($i=0;$i<count($boutd);$i++) {
						if (($boutd[$i] != ';') && ($boutd[$i] != ':')) {
							$boutd[$i] = $boutd[$i].' ';
						}
					}
					$boutds = join('',$boutd);
					$boutds = str_replace(' ;',';',$boutds);
					$boutds = str_replace(' :',':',$boutds);
					$bout[] = "{".$boutds."}";
					$boutd = array();
					$defn = false;
				} elseif( (!$mute) && (strlen($toks[$n])>0) ) {
					$token = $toks[$n];
					// print("[".$token."]");
					if ($token[0] == $token[strlen($token)-1]) {
						if (($token[0] == '"')
						|| ($token[0] == "'")) {
							$token = substr($token,1,strlen($token)-2);
						}
					}
					elseif (($token[0] == '#') && (strlen($token) == 7)) {
						if (($token[1] == $token[2])
						&& ($token[3] == $token[4])
						&& ($token[5] == $token[6])) {
							// print("[COLOR:".$token."]");
							$token = '#'.$token[1].$token[3].$token[5];
						}
					}
					if ($defn) { $boutd[] = $token; } else { $bout[] = $token; }
				}
			}
			$buffer = join('',$bout);
			return($buffer);
		}

	}

?>
