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

    const KEY_TRANSLATE = 'lepton.mvc.controller.translatenames';

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
        if (config::get(controller::KEY_TRANSLATE,false)==true) $method = str_replace('-','_',$method);
        $ctlpath = base::apppath().'/controllers/'.$controller.'.php';
        Console::debugEx(LOG_VERBOSE,__CLASS__,'Invoking %s:%s (%s)', $controller, $method, $ctlpath);
        $cc = $controller.'Controller';
        if(!class_exists($cc)) {
            if (file_exists($ctlpath)) {
                require($ctlpath);
            } else {
                throw new BaseException("Could not find controller class ". $controller);
                return RETURN_ERROR;
            }
        }
        $cr = new ReflectionClass($cc);
        if ($cr->hasMethod($method)) {
            $mr = $cr->getMethod($method);
            $args = Array();
            for ($n = 0; $n < $mr->getNumberOfParameters(); $n++) {
                if ($n < count($arguments)) { 
                    $args[$n] = $arguments[$n];
                } else {
                    $args[$n] = null;
                }
            }
            $arguments = $args;
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
    function set($key,$value) {
        $this->_state[$key] = $value;
    }

    /**
     *
     * @param string $key The key to retrieve
     * @return mixed The data
     */
    function get($key) {
        if (!isset($this->_state[$key])) {
            return null;
        }
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
        if (class_exists($lib)) {
            if ($as == null) $as = $lib;
            $this->{$as} = new $lib();
        } else {
            throw new ClassNotFoundException("Cound not find requested class ".$lib);
        }
    }
    
    protected function import($lib) {
    	$this->loadLibrary($lib,$lib);
    }

}

