<?php

	class ViewException extends Exception {}
	class ViewNotFoundException extends ViewException {}

	interface IViewHandler {

	}
	abstract class ViewHandler implements IViewHandler {

	}

	ModuleManager::load('lepton.mvc.viewhandler.php');

	class View {

		static function load($view) {

			$vh = new PlainViewHandler();
			$vh->loadView($view);

/*

			$path = BASE_PATH.'views/'.$view;
			Console::debugEx(LOG_BASIC,__CLASS__,"Attempting to invoke view from %s", $path);
			if (file_exists($path)) {
				if 
				(preg_match('/\.php$/',$path)) {
					Console::debugEx(LOG_BASIC,__CLASS__,"Invoking as Pure PHP View");
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
*/
		}

	}


?>
