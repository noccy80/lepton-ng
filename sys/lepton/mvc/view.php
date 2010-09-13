<?php __fileinfo("View and ViewHandler base functions", array(
	'version' => '1.0'
));

	class ViewException extends Exception {}
	class ViewNotFoundException extends ViewException {}
	class ViewHandlerNotFoundException extends ViewException {}

	interface IViewHandler {
		function loadView($view);
		function display();
	}
	/**
	 * @class ViewHandler
	 * @brief Encapsulates the loading and displaying of views.
	 *
	 * @author Christopher Vagnetoft <noccy@chillat.net>
	 */
	abstract class ViewHandler implements IViewHandler {

		/// The data assigned to the view
		protected $_data = array();

		/**
		 * @brief Set a value in the view data set
		 *
		 * If a key already exists, it will be overwritten. If this is
		 * not what you are after, see the push() method.
		 *
		 * @see ViewHandler::push
		 * @param string $key The key to assign to
		 * @param string $val The value to assign
		 */
		function set($key,$val) {
			 $this->_data[$key] = $val;
		}
		function __set($key,$val) { return $this->set($key,$val); }
		/**
		 * @brief Push a value onto a keyset in the view data set
		 *
		 * If a key already exists and is not an array, it will be
		 * converted into such.
		 *
		 * @see ViewHandler::set
		 * @param string $key The key to push to
		 * @param string $val The value to assign
		 */
		function push($key,$val) {
			 $d = (array)$this->_data;
			 if (!isset($d[$key])) $d[$key] = array();
			 $d[$key][] = $val;
		}
		/**
		 * @brief Get a value from the view data set
		 *
		 */
		function get($key) {
			return (isset($this->_data[$key])?$this->_data[$key]:null);
		}
		function __get($key) { return $this->get($key); }
		/**
		 * @brief Returns the entire view data set
		 *
		 * @return array The view data
		 */
		function getViewData() {
			return $this->_data;
		}
		/**
		 * @brief Replaces the view data with the specified chunk.
		 *
		 * @param array $data The data to assign
		 */
		function setViewData($data) {
			$this->_data = $data;
		}
		
		function includeView($view) {
			include($view);
		}
		
	}

	// TODO: This should only load the plain view. Document how to make use of the rest
	ModuleManager::load('lepton.mvc.viewhandler.*');

	/**
	 *
	 *
	 *
	 */
	class View {

		// TODO: Replace with ViewHandler::register() and ViewHandler::getHandler()
		static $_handlers = array();

		/**
		 * @brief Load and display a view
		 *
		 *
		 */
		static function load($view,$ctl=null) {

			// Go over the registered handlers and see which one match the file name
			foreach((array)View::$_handlers as $handler=>$match) {
				
				if (preg_match('%'.$match.'%',$view)) {
					$vc = new $handler();
					// If controller is specified, get its state
					if (count($ctl->getState()) > 0) {
						$vc->setViewData($ctl->getState());
					}
					$vc->loadView($view);
					$vc->display();
					return true;
				}
			}

			// If we end up here, no handler could be found.
			throw new ViewHandlerNotFoundException("No matching handler found for requested view");
		}

	}


?>
