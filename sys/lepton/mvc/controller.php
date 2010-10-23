<?php __fileinfo("Controller implementation", array(
    'version' => '1.0'
));

/**
 * @brief Interface for controllers.
 *
 * @category mvc
 * @package lepton.mvc
 */

interface IController {
    function __request($method,$arguments);
}

/**
 * @brief Controller base class
 *
 * @category mvc
 * @package lepton.mvc
 */
abstract class Controller implements IController {

    private $_state;

    /**
     * @brief Invoke a controller method
     *
     * @param string $controller
     * @param string $method
     * @param array $arguments
     * @return int Exit code
     */
    static function invoke($controller=null,$method=null,Array $arguments=null) {
        if (!$controller) $controller = 'default'; // config
        if (!$method) $method = 'index'; // config
        $ctlpath = APP_PATH.'/controllers/'.$controller.'.php';
        Console::debugEx(LOG_VERBOSE,__CLASS__,'Invoking controller instance %s (method=\'%s\', args=\'%s\')...', $controller, $method, join('\',\'',(array)$arguments));
        $cc = $controller.'Controller';

        if(!class_exists($cc)) {
            if (file_exists($ctlpath)) {
                require($ctlpath);
            } else {
                throw new BaseException("Could not find controller class ". $controller);
                return RETURN_ERROR;
            }
        }

        $ci = new $cc;
        if (!$ci->__request($method,(array)$arguments)) {
            return RETURN_ERROR;
        } else {
            return RETURN_SUCCESS;
        }
    }

    /**
     * Constructor
     */
    function __construct() {
        $this->_state = Array();
    }
    
    /**
     * @brief Handle a request and send it to the right method.
     *
     * @param string $method
     * @param string $arguments
     */
    function __request($method,$arguments) {
        if (method_exists($this,$method)) {
            call_user_func_array(array($this,$method),$arguments);
        } else {
            throw new BaseException("Could not find controller method ".$method);
        }
    }

    /**
     * @brief Retrieve the state (data) of the controller
     *
     * @return array The state
     */
    function getState() {
        return $this->_state;
    }

    /**
     * @brief Set a key of the controllers state
     *
     * @param string $key The key to set
     * @param mixed $value The value to set
     */
    function __set($key,$value) {
        $this->_state[$key] = $value;
    }

    /**
     *
     * @param string $key The key to retrieve
     * @return mixed The data
     */
    function __get($key) {
        return($this->_state[$key]);
    }

    /**
     *
     * @param string $key
     * @return bool
     */
    function __isset($key) {
        return(isset($this->_state[$key]));
    }

    /**
     *
     * @param string $key
     */
    function __unset($key) {
        unset($this->_state[$key]);
    }

    /**
     * @brief Load a library into the controller.
     *
     * @param string $lib Library to attach
     * @param string $as Attach class as
     */
    protected function loadLibrary($lib,$as=null) {
        if ($as == null) $as = $lib;
        $this->{$as} = new $lib();
    }

}

?>
