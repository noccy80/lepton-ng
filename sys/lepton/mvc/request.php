<?php module("Request wrapper for MVC support");

/**
 * @interface IRequestObject
 * @brief Interface for any request content object
 * 
 * This interface should be inherited by all data that is returned by the
 * request methods for request::get() and request::post() and it is
 * automatically implemented by the abstract base class RequestObject
 */
interface IRequestObject {
	function __construct($data);
	function getType();
	function sanitize($options);
	function validate($options);
	function __toString();
}

/**
 * @class RequestObject
 * @brief Abstract base class for request objects
 */
abstract class RequestObject implements IRequestObject {
    /**
     * @brief Return the type of the request object
     * 
     * @return string The type of request object
     */
	function getType() { return class_name($this); }
    
    /**
     * @brief Explicitly cast the object value to a string and return it
     * 
     * @return string The string casted object value
     */
	function toString() {
		return $this->__toString();
	}
    
    /**
     * @brief Explicitly cast the object value to a int and return it
     * 
     * @return int The string casted object value cast to an int 
     */
    function toInt() {
        return intval($this->__toString());
    }
    
    /**
     * @brief Explicitly cast the object value to a float and return it
     * 
     * @return float The string casted object value cast to a float
     */
    function toFloat() {
        return floatval($this->__toString());
    }
}

/**
 * @class RequestString
 * @brief Stores a string value
 */
class RequestString extends RequestObject {
	private $data = null;
	function __construct($data) { $this->data = $data; }
	function __toString() { return sprintf('%s',(string)$this->data); }
	function sanitize($options) { }
	function validate($options) { }
}

/**
 * @class RequestFile
 * @brief Stores a POSTed file
 */
class RequestFile extends RequestObject {
	private $key = null;
    private $index = null;
	private $name = null;
	private $type = null;
	private $tempname = null;
	private $size = null;
	private $md5 = null;
	function __construct($key,$index = null) {
		$this->key = $key;
        $this->index = $index;
        if ($index != null) {
            $this->name = $_FILES[$key]['name'][$index];
            $this->type = $_FILES[$key]['type'][$index];
            $this->size = $_FILES[$key]['size'][$index];
            $this->error = $_FILES[$key]['error'][$index];
            $this->tempname = $_FILES[$key]['tmp_name'][$index];
        } else {
            $this->name = $_FILES[$key]['name'];
            $this->type = $_FILES[$key]['type'];
            $this->size = $_FILES[$key]['size'];
            $this->error = $_FILES[$key]['error'];
            $this->tempname = $_FILES[$key]['tmp_name'];
        }
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
    static function get($key, $def = null) {
        if (arr::hasKey($_REQUEST,$key)) 
            return(new RequestString($_REQUEST[$key]));
        return new RequestString($def);
    }

    static function has($key) {
        return (
            arr::hasKey($_REQUEST,$key) || 
            arr::hasKey($_POST,$key) || 
            arr::hasKey($_FILES,$key)
        );
    }

    static function hasGet($key) {
        return (arr::hasKey($_REQUEST,$key));
    }

    static function hasPost($key) {
        return (
            arr::hasKey($_POST,$key) || 
            arr::hasKey($_FILES,$key)
        );
    }
    
    static function post($key, $def = null) {
    	// Check if the request field is a file
        if (arr::hasKey($_FILES,$key)) {
            if (count($_FILES[$key]['name']) > 0) {
                $ret = array();
                for($n = 0; $n < count($_FILES[$key]); $n++) {
                    $ret[] = new RequestFile($key,$n);
                }
                return $ret;
            } else {
                return(new RequestFile($key));
            }
        }
        if (arr::hasKey($_POST,$key)) return(new RequestString($_POST[$key]));
        return new RequestString($def);
    }

	static function useSts() {
		$use_sts = config::get('lepton.security.sts');
		if ($use_sts && isset($_SERVER['HTTPS'])) {
		  header('Strict-Transport-Security: max-age=500');
		} elseif ($use_sts && !isset($_SERVER['HTTPS'])) {
		  header('Status-Code: 301');
		  header('Location: https://'.$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']);
		}
	}

    static function getDebugInformation() {
        if (($_SERVER['HTTPS'] != 'off') && ($_SERVER['HTTPS'] != null)) {
            $ssl = 'Yes ('.$_SERVER['SSL_TLS_SNI'].')';
        } else {
            $ssl = 'No';
        }
        return join("\n",array(
            "Request time: ".date(DATE_RFC822,$_SERVER['REQUEST_TIME']),
            /* "Event id: ".$id, */
            "Base path: ".base::basePath(),
            "App path: ".base::appPath(),
            "Sys path: ".base::sysPath(),
            "User-agent: ".$_SERVER['HTTP_USER_AGENT'],
            "Request URI: ".$_SERVER['REQUEST_URI'],
            "Request method: ".$_SERVER['REQUEST_METHOD'],
            "Remote IP: ".$_SERVER['REMOTE_ADDR']." (".gethostbyaddr($_SERVER['REMOTE_ADDR']).")",
            "Hostname: ".$_SERVER['HTTP_HOST'],
            "Secure: ".$ssl,
            "Referrer: ".(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'null'),
            sprintf("Running as: %s (uid=%d, gid=%d) with pid %d", get_current_user(), getmyuid(), getmygid(), getmypid()),
            sprintf("Server: %s", $_SERVER['SERVER_SOFTWARE'])." (".php_sapi_name().")",
            sprintf("Memory allocated: %0.3f KB (Total used: %0.3f KB)", (memory_get_usage() / 1024 / 1024), (memory_get_usage(true) / 1024 / 1024)),
            "Platform: ".LEPTON_PLATFORM_ID,
            sprintf("Runtime: PHP v%d.%d.%d (%s)", PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION, PHP_OS)
        ));
        
    }
    
    static function getDomain() {
        if (arr::hasKey($_SERVER,'HTTP_HOST')) {
            return strtolower($_SERVER['HTTP_HOST']);
	} else {
            return 'localhost';
        }
    }

    static function getQueryString() {
    	$data = $_GET;
    	if (isset($data['/index_php'])) {
    		$data = array_slice($data,2);
    	}
    	return $data;
    }

    static function getUserAgent() {
        return new RequestUserAgent();
    }

    static function getRawQueryString() {
        $data = $_SERVER['QUERY_STRING'];
        return $data;
    }

    /**
     * @brief Return the raw data of a post request.
     *
     * @return string The data posted
     */
    static function getInput() {
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
    static function clientConnected() {
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
    static function ignoreDisconnect($value=true) {
        return (ignore_user_abort($value)==1)?true:false;
    }

    /**
     * @brief Check if a request is a http post request.
     *
     * @return bool True if the request is a http post request
     */
    static function isPost() {
        if (!isset($_SERVER['REQUEST_METHOD'])) return (count($_POST)>0);
        return ($_SERVER['REQUEST_METHOD'] == 'POST');
    }

    /**
     * @brief Check if a request is a http get request.
     *
     * @return bool True if the request is a http get request
     */
    static function isGet() {
        if (!isset($_SERVER['REQUEST_METHOD'])) return (count($_GET)>0);
        return ($_SERVER['REQUEST_METHOD'] == 'GET');
    }
    
    /**
     * @brief Return the upper case http method
     *
     * @return string The request method
     */
    static function getRequestMethod() {
        if (!isset($_SERVER['REQUEST_METHOD'])) {
        	if (count($_POST)>0) { 
				$method = 'POST';        	
        	} elseif (count($_GET)>0) {
        		$method = 'GET';
        	} else {
        		$method = null;
        	}
        } else {
        	if (arr::hasKey($_SERVER,'REQUEST_METHOD')) {
        		$method = strToUpper($_SERVER['REQUEST_METHOD']);
        	} else {
        		$method = null;
        	}
        }
    	return $method;
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
        $host = GetHostByName(self::getRemoteIp());
        if ($host) return $host;
        return self::getRemoteIp();
    }

    static function getURL() {
        if (isset($_SERVER['HTTP_HOST'])) {
            if ($_SERVER['HTTPS']) {
                $proto = 'https://';
                $port = (intval($_SERVER['SERVER_PORT'])!=443)?':'.$_SERVER['SERVER_PORT']:'';
            } else {
                $proto = 'http://';
                $port = (intval($_SERVER['SERVER_PORT'])!=80)?':'.$_SERVER['SERVER_PORT']:'';
            }
            if ($_SERVER['QUERY_STRING']!='?') {
                $querystring = $_SERVER['QUERYSTRING'];
            } else {
                $querystring = '';
            }
            return $proto . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'] . $querystring;
        } else {
            return null;
        }
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
