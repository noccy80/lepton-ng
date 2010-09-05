<?php

	class ViewException extends Exception {}
	class ViewNotFoundException extends ViewException {}

	class View {

		static function load($view) {

			$path = BASE_PATH.'views/'.$view;
			Console::debugEx(LOG_BASIC,__CLASS__,"Attempting to invoke view from %s", $path);
			if (file_exists($path)) {
				if 
				(preg_match('/\.php$/',$path)) {
					Console::debugEx(LOG_BASIC,__CLASS__,"Invoking as Pure PHP View");
					$document = new Document();
					include($path);
				} elseif
				(preg_match('/\.txl$/',$path)) {
					Console::debugEx(LOG_BASIC,__CLASS__,"Invoking as LiteTemplate View");
					$document = new LiteTemplate($path);
					echo $document->render();
				}
			
			} else {
				throw new ViewNotFoundException("The view ".$view." could not be found");
			}

		}

	}


?>
