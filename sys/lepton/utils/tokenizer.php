<?php __fileinfo("String and Argument tokenizers");

	class Tokenizer implements IteratorAggregate {

		private $_tokens;

		function getIterator() {
			return new ArrayIterator($this->_tokens);
		}

		function __construct($matchtok,$str) {

			$md = explode(' ',$str); $mi = 0;
			$mo = array();
			Console::debugEx(LOG_DEBUG2,__CLASS__,"Parsing quotes in array for %s", $matchtok);
			Console::debugEx(LOG_DEBUG2,__CLASS__," \$md = {'%s'}", join("','", $md));
			while($mi < count($md)) {
				Console::debugEx(LOG_DEBUG2,__CLASS__,"Current token: %s", $md[$mi]);
				$qt = $md[$mi][0];
				if (($qt == '"') || ($qt == "'" )) {
					$buf = array();
					while($mi < count($md)) {
						$str = $md[$mi];
						$buf[] = $md[$mi++];
						Console::debugEx(LOG_DEBUG2,__CLASS__," -- Quoted token: %s (%s)", $str, $str[strlen($str)-1]);
						if ($str[strlen($str)-2] == $qt) break;
					}
					$bufstr = join(' ',$buf);
					$bufstr = substr($bufstr,1,strlen($bufstr)-2);
					$mo[] = $bufstr;
					Console::debugEx(LOG_DEBUG2,__CLASS__,"Joined quoted statement: %s", $bufstr);
				} else {
					$mo[] = $md[$mi++];
				}
			}
			$md = $mo;
			Console::debugEx(LOG_DEBUG2,__CLASS__," \$md = {'%s'}", join("','", $md));
			$mi = 0;
			$matchtoks = explode(' ',$matchtok);
			while($mi < count($md)) {
				Console::debugEx(LOG_DEBUG1,__CLASS__,'Parsing tokenized data for %s', $md[$mi]);
				$token = strtolower($md[$mi]);
				foreach($matchtoks as $tok) {
					$ti = explode(':',$tok);
					if ($ti[0] == $token) {
						Console::debugEx(LOG_DEBUG2,__CLASS__,"Matched token %s", $token);
						$this->_tokens[$ti[0]] = join(' ',array_slice($md,$mi+1,$ti[1]));
						$mi+=$ti[1];
						break;
					}
				}

				$mi++;
			}

		}

		public function getTokens() {

			return $this->_tokens;

		}
	}

	class ArgumentTokenizer implements IteratorAggregate {

		private $_tokens;

		function getIterator() {
			return new ArrayIterator($this->_tokens);
		}

		function __construct($str) {

			if (is_array($str)) $str = join('',$str);
			$md = explode(' ',$str); $mi = 0;
			$mo = array();
			while($mi < count($md)) {
				$qt = $md[$mi][0];
				if (($qt == '"') || ($qt == "'" )) {
					$buf = array();
					while($mi < count($md)) {
						$str = $md[$mi];
						$buf[] = $md[$mi++];
						if ($str[strlen($str)-2] == $qt) break;
					}
					$bufstr = join(' ',$buf);
					$bufstr = substr($bufstr,1,strlen($bufstr)-2);
					$mo[] = $bufstr;
				} else {
					$mo[] = $md[$mi++];
				}
			}
			$this->_tokens = $mo;

		}

		public function getTokens() {

			return $this->_tokens;

		}
	}

?>
