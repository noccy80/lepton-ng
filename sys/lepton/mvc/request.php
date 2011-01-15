<?php __fileinfo("Request wrapper for MVC support");

interface IRequestObject {
	function __construct($data);
	function getType();
	function sanitize($options);
	function validate($options);
	function __toString();
}
abstract class RequestObject implements IRequestObject {
	function getType() { return class_name($this); }
	function toString() {
		return $this->__toString();
	}
    function toInt() {
        return intval($this->__toString());
    }
}
class RequestString extends RequestObject {
	private $data = null;
	function __construct($data) { $this->data = $data; }
	function __toString() { return sprintf('%s',(string)$this->data); }
	function sanitize($options) { }
	function validate($options) { }
}

class RequestFile extends RequestObject {
	private $key = null;
	private $name = null;
	private $type = null;
	private $tempname = null;
	private $size = null;
	private $md5 = null;
	function __construct($key) {
		$this->key = $key;
		$this->name = $_FILES[$key]['name'];
		$this->type = $_FILES[$key]['type'];
		$this->size = $_FILES[$key]['size'];
		$this->error = $_FILES[$key]['error'];
		$this->tempname = $_FILES[$key]['tmp_name'];
		if ($this->error == UPLOAD_ERR_OK) {
			if (function_exists('mime_content_type')) {
				$this->type = @mime_content_type($this->tempname);
			} else {
				if (function_exists('finfo_open')) {
					$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
    				$this->type = finfo_file($finfo, $this->tempname);
					finfo_close($finfo);
				}
			}
			$this->md5 = md5_file($this->tempname);
		}
	}
	function sanitize($options) { }
	function validate($options) { }
	function save($dest) {
		if (is_writable(dirname($dest))) {
			return (move_uploaded_file($this->tempname, $dest));
		} else {
			throw new SecurityException("Can not write to destination file ".$dest." during upload");
		}
	}
	function getContents() {
		return file_get_contents($this->tempname);
	}
	function __toString() {
		$size = $this->size; $unit='b';
		if ($size>1024) { $size=$size/1024; $unit='Kb'; }
		if ($size>1024) { $size=$size/1024; $unit='Mb'; }
		return sprintf('%s (%s) %.1f%s, %s', $this->name, $this->type, $size, $unit, $this->getErrorString());
	}
	function getSize() { return $this->size; }
	function getName() { return $this->name; }
	function getType() { return $this->type; }
	function isError() { return ($this->error != UPLOAD_ERR_OK); }
	function getError() { return $this->error; }
	function getErrorString() {
		switch($this->error) {
			case UPLOAD_ERR_OK:
				return 'Success';
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return 'Maximum size of upload exceeded.';
			case UPLOAD_ERR_PARTIAL:
				return 'The uploaded file was only partially uploaded.';
			case UPLOAD_ERR_NO_FILE:
				return 'No file was uploaded.';
			case UPLOAD_ERR_NO_TMP_DIR:
				return 'Missing a temporary folder.';
			case UPLOAD_ERR_CANT_WRITE:
				return 'Failed to write file to disk.';
			case UPLOAD_ERR_EXTENSION:
				return 'The upload was blocked by an extension.';
		}
	}
}

class RequestUserAgent {

    private $_tokens = null;
    private $_useragent = null;
    private $_mobile = false;

    function __construct() {
        if (array_key_exists('HTTP_USER_AGENT',$_SERVER)) {
            $this->_useragent = $_SERVER['HTTP_USER_AGENT'];
            $this->_mobile = (strpos(strToLower($this->_useragent),' mobile ') > 0);
            $s = explode('(',$this->_useragent);
            $s = explode(')',$s[1]);
            $this->_tokens = explode(';',$s[0]);
        } else {
            $this->_useragent = 'PHP '.phpversion();
            $this->_tokens = array();
            $this->_mobile = false;            
        }
    }

    function __toString() {
        return $this->_useragent;
    }

    function isMobileDevice() {
        return $this->_mobile;
    }

    function getTokens() {
        return $this->_tokens;
    }

    function hasToken($tok) {
        return in_array($tok,$this->tokens);
    }

}

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
        if (isset($_REQUEST[$key])) return(new RequestString($_REQUEST[$key]));
        return new RequestString($def);
    }

    function has($key) {
        return (isset($_REQUEST[$key]));
    }
    
    function post($key, $def = null) {
    	// Check if file
   	if (isset($_FILES[$key])) return(new RequestFile($key));
        if (isset($_POST[$key])) return(new RequestString($_POST[$key]));
        return new RequestString($def);
    }
    
    function getQueryString() {
    	$data = $_GET;
    	if (isset($data['/index_php'])) {
    		$data = array_slice($data,2);
    	}
    	return $data;
    }

    function getUserAgent() {
        return new RequestUserAgent();
    }

    function getRawQueryString() {
        $data = $_SERVER['QUERY_STRING'];
        return $data;
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

    /**
     * @brief Inspect the state of the request
     */
    static function inspect() {
        debug::inspect($_REQUEST);
    }

    /**
     * @brief Return the remote IP
     * @see request::getRemoteHost
     * @return string The remote IP address
     */
    static function getRemoteIp() {
        if (!isset($_SERVER['REMOTE_ADDR'])) {
            return '127.0.0.1';
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**
     * @brief Return the remote hostname
     * @see request::getRemoteIp
     * @return string The remote hostname
     */
    static function getRemoteHost() {
        return GetHostByName($ip);
    }

}
/*
$use_sts = TRUE;

if ($use_sts && isset($_SERVER['HTTPS']) {
  header('Strict-Transport-Security: max-age=500');
} elseif ($use_sts && !isset($_SERVER['HTTPS'])) {
  header('Status-Code: 301');
  header('Location: https://'.$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']);
}*/