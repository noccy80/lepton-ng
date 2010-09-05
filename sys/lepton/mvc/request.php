<?php

	class Request {

		function get($key, $def = null) {
			if (isset($_REQUEST[$key])) return($_REQUEST[$key]);
			return $def;
		}

	}
