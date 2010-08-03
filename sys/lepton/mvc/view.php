<?php

	class ViewException extends Exception {}
	class ViewNotFoundException extends ViewException {}

	class View {

		static function load($view) {

			$path = BASE_PATH.'app/views/'.$view;
			Console::debugEx(LOG_BASIC,__CLASS__,"Attempting to invoke view from %s", $path);
			if (file_exists($path)) {
				include($path);
			} else {
				throw new ViewNotFoundException("The view ".$view." could not be found");
			}

		}

	}


?>
