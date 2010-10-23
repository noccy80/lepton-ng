<?php __fileinfo("Request wrapper for MVC support");

/**
 * 
 */
class Request {

    /**
     * @brief Return a request variable
     *
     * @param string $key The field to return.
     * @param mixed $def Default value if not present
     * @return string The posted data or the default value if not present
     */
    function get($key, $def = null) {
        if (isset($_REQUEST[$key])) return($_REQUEST[$key]);
        return $def;
    }

    /**
     * @brief Return the raw data of a post request.
     *
     * @return string The data posted
     */
    function getInput() {
        if (isset($HTTP_RAW_POST_DATA)) {
            $data = HTTP_RAW_POST_DATA;
        } else {
            if (COMPAT_INPUT_BROKEN) {
                $data = file_get_contents('php://input');
            } else {
                $fh = fopen('php://input','r');
                $data = '';
                if ($fh) while(!feof($fh)) {
                        $data .= fread($fh,10000);
                    }
                fclose($fh);
            }
        }
        return $data;
    }

    /**
     * @brief Check if the client is still connected.
     *
     * @return bool True if the client is still connected
     */
    function clientConnected() {
        return (!client_aborted());
    }

    /**
     * @brief Configures whether the script should continue even if the client
     *   disconnects.
     * 
     * You can use Request::clientConnected() to check if the user is still
     * connected.
     *
     * @see User::clientConnected
     * @param bool $value The new value
     * @return bool Previous value
     */
    function ignoreDisconnect($value=true) {
        return (ignore_user_abort($value)==1)?true:false;
    }

    /**
     * @brief Check if a request is a http post request.
     *
     * @return bool True if the request is a http post request
     */
    function isPost() {
        if (!isset($_SERVER['REQUEST_METHOD'])) return (count($_POST)>0);
        return ($_SERVER['REQUEST_METHOD'] == 'POST');
    }

    /**
     * @brief Check if a request is a http get request.
     * 
     * @return bool True if the request is a http get request
     */
    function isGet() {
        if (!isset($_SERVER['REQUEST_METHOD'])) return (count($_GET)>0);
        return ($_SERVER['REQUEST_METHOD'] == 'GET');
    }

}
