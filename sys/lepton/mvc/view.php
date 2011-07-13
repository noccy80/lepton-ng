<?php module("View and ViewHandler base functions", array(
    'version' => '1.0'
));

class ViewException extends BaseException {

}
class ViewNotFoundException extends ViewException {

}
class ViewHandlerNotFoundException extends ViewException {

}

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
    protected $useragent = null;

    function __construct() {
        $this->useragent = new RequestUserAgent();
    }

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
    function __set($key,$val) {
        return $this->set($key,$val);
    }
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
    function __get($key) {
        return $this->get($key);
    }
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
        view::embed($view);
        // include(base::appPath().'/views/'.$view);
    }

}

/**
 *
 *
 *
 */
class View {

    const KEY_EMBED_EXCEPTION = 'lepton.mvc.embed.exception';

    // TODO: Replace with ViewHandler::register() and ViewHandler::getHandler()
    static $_handlers = array();
    static $_viewdata = array();
    static $_primaryview = null;

    /**
     * @brief Load and display a view
     *
     *
     */
    static function load($view,$ctl=null) {

		if (!headers_sent()) {
			response::contentType("text/html; charset=utf-8");
		}

        if (!self::$_primaryview) {
            if (config::get(self::KEY_EMBED_EXCEPTION,false) == true) ob_start();
            self::$_primaryview = $view;
        }
        // Go over the registered handlers and see which one match the file name
        foreach((array)View::$_handlers as $handler=>$match) {

            if (preg_match('%'.$match.'%',$view)) {

				$vc = new $handler();
                // If controller is specified, get its state
                if (($ctl) && count($ctl->getState()) > 0) {
                    $vc->setViewData($ctl->getState());
                }
                $vc->setViewData(View::$_viewdata);
                $vc->loadView($view);
                $vc->display();
                return true;
            }
        }

        // If we end up here, no handler could be found.
        throw new ViewHandlerNotFoundException("No matching handler found for requested view");
    }

    /**
     * @brief Embed a view inside another one
     *
     * @param string $view The view to embed
     * @param array $data Optional data to pass to the view
     */
    static function embed($view,$data=null) {
        $vp = base::expand($view,'/views/');
        if (file_exists($vp)) {
			if (is_array($data)) View::set($data);
            View::load($view);
        } else {
            if (config::get(self::KEY_EMBED_EXCEPTION,false) == true) {
                throw new ViewException("Embedded view ".$view." not found");
            }
            printf('<div style="display:block;"><div style="color:white; background-color:red; border:dotted 1px white; padding:5px; margin:1px;">View %s not found</div></div>', $view);
        }
    }

    /**
     * @brief Set a view key
     *
     * @param string $key The key to set, or an associative array of keys and values to set
     * @param mixed $value Data to set
     */
    static function set($key,$value=null) {
        if (is_array($key)) {
            // Set the contents of the array
            view::$_viewdata = array_merge(
            	view::$_viewdata, 
            	$key
            );
        } else {
            view::$_viewdata[$key] = $value;
        }
    }


    static function inspect() {
        debug::inspect(view::$_viewdata);
    }

}

