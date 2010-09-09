<?php

	class Request {

		function get($key, $def = null) {
			if (isset($_REQUEST[$key])) return($_REQUEST[$key]);
			return $def;
		}

		function getInput() {
			if (isset($HTTP_RAW_POST_DATA)) {
				$data = HTTP_RAW_POST_DATA;
			} else {
				if (COMPAT_INPUT_BROKEN) {
					$data = file_get_contents('php://input');
				} else {
					$fh = fopen('php://input','r');
					$data = '';
					if ($fh) while(!feof($fh)) {
						$data .= fread($fh,10000);
					}
					fclose($fh);
				}
			}
			return $data;
		}

		function clientConnected() {
			return (!client_aborted());
		}

		function ignoreDisconnect($value=true) {
			return (ignore_user_abort($value)==1)?true:false;
		}

	}
