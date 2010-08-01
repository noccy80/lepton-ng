<?php

	class ViewException extends Exception {}
	class ViewNotFoundException extends ViewException {}

	class View {

		static function load($view) {

			$path = BASE_PATH.'app/views/'.$view;
			if (file_exists($path)) {
				include($path);
			} else {
				throw new ViewNotFoundException("The view ".$view." could not be found");
			}

		}

	}


?>
