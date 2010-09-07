<?php

	class ViewException extends Exception {}
	class ViewNotFoundException extends ViewException {}

	interface IViewHandler {

	}
	abstract class ViewHandler implements IViewHandler {
		protected $_data = array();
		function set($key,$val) {
			 $this->_data[$key] = $val;
		}
		function getViewData() {
			return $this->_data;
		}
	}

	// TODO: This should only load the plain view. Document how to make use of the rest
	ModuleManager::load('lepton.mvc.viewhandler.*');

	class View {

		static $_handlers = array();
		static function load($view) {

			foreach((array)View::$_handlers as $handler=>$match) {
				if (preg_match('%'.$match.'%',$view)) {
					$vc = new $handler();
					$vc->loadView($view);
					return true;
				}
			}
			throw new BaseException("No matching handler found for requested view");
		}

	}


?>
