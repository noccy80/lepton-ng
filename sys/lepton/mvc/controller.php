<?php __fileinfo("Controller implementation", array(
    'version' => '1.0'
));

    interface IController {
        function __request($method,$arguments);
    }

    abstract class Controller implements IController {
        private $_state;
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
        function __construct() {
            $this->_state = Array();
        }
        function __request($method,$arguments) {
            if (method_exists($this,$method)) {
                return call_user_func_array(array($this,$method),$arguments);
            } else {
                throw new BaseException("Could not find controller method ".$method);
            }
        }
        function getState() {
            return $this->_state;
        }
        function __set($key,$value) {
            $this->_state[$key] = $value;
        }
        function __get($key) {
            return($this->_state[$key]);
        }
        function __isset($key) {
            return(isset($this->_state[$key]));
        }
        function __unset($key) {
            unset($this->_state[$key]);
        }
        protected function loadLibrary($lib,$as=null) {
            if ($as == null) $as = $lib;
            $this->{$as} = new $lib();
        }
    }

?>
