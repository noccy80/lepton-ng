<?

interface IDebugProvider {
    function inspect($data,$table=false);
}

class Debug {

    const EVT_DEBUG = 'lepton.debug.message';
    const EVT_OPTIMIZATION = 'lepton.debug.optimizationhint';

    private static $provider = null;

    private static function getHeader($h, $def=null) {
        if (arr::hasKey($_SERVER,$h)) return $_SERVER[$h];
        return $def;
    }

    static function getDebugInformation() {

        if (class_exists('Request')) {
            if (request::isSecure()) {
                $ssl = 'Yes ('.$_SERVER['SSL_TLS_SNI'].')';
            } else {
                $ssl = 'No';
            }
        } else {
            $ssl = 'n/a';
        }
        $dbgbase = array(
            'Request time'  => date(DATE_RFC822,$_SERVER['REQUEST_TIME']),
            'Base path'     => base::basePath(),
            'App path'      => base::appPath(),
            'Sys path'      => base::sysPath(),
            'User-Agent'    => self::getHeader('HTTP_USER_AGENT')
        );
        $dbgrequest = array(
            'Request method'=> self::getHeader('REQUEST_METHOD'),
            'Request URI'   => self::getHeader('REQUEST_URI'),
            'Remote IP'     => self::getHeader('REMOTE_ADDR')." (".gethostbyaddr(self::getHeader('REMOTE_ADDR')).")",
            'Secure'        => $ssl,
        );
        if (class_exists('request')) {
            $dbgrequest = array_merge($dbgrequest,request::getAllHeaders());
        }
        $dbgsession = array(
            'Authenticated user'    => (user::isAuthenticated()?user::getActiveUser()->uuid:'n/a')
        );
        $dbgserver = array(
            'Running as'    => sprintf("%s (uid=%d, gid=%d) with pid %d", get_current_user(), getmyuid(), getmygid(), getmypid()),
            'Server'        => sprintf("%s", $_SERVER['SERVER_SOFTWARE'])." (".php_sapi_name().")",
            'Memmory alloc' => sprintf("%0.3f KB (Total used: %0.3f KB)", (memory_get_usage() / 1024 / 1024), (memory_get_usage(true) / 1024 / 1024)),
            'Platform'      => LEPTON_PLATFORM_ID,
            'Runtime'       => sprintf("PHP v%d.%d.%d (%s)", PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION, PHP_OS)
        );
        if (class_exists('Cache')) {
            $dbgserver['Cache'] = Cache::getBackend();
        } else {
            $dbgserver['Cache'] = 'None';
        }

        $debug = array();
        if (config::get('lepton.debug.all',false) || config::get('lepton.debug.base',true)) $debug = array_merge($debug,$dbgbase);
        if (config::get('lepton.debug.all',false) || config::get('lepton.debug.request',true)) $debug = array_merge($debug,$dbgrequest);
        if (config::get('lepton.debug.all',false) || config::get('lepton.debug.session',false)) $debug = array_merge($debug,$dbgsession);
        if (config::get('lepton.debug.all',false) || config::get('lepton.debug.server',false)) $debug = array_merge($debug,$dbgserver);
        return $debug;

    }

    static function getDebugInformationString($html=false) {

        $dbg = self::getDebugInformation();
        $sout = '';
        $dk = array_keys($dbg);
        $klen = 0;
        foreach($dk as $dkl) if (strlen($dkl)>$klen) $klen = strlen($dkl);
        $klen+= 2;
        foreach($dbg as $k=>$v) {

            if (strlen($v) > 0) {
                if (intval($k) == 0) {
                    $swrap = explode("\n",wordwrap($v,config::get('lepton.debug.html.wrappos',90)));
                    if ($html) {
                        $sout.= sprintf("<b>%{$klen}s:</b> %s\n", $k, $swrap[0]);
                    } else {
                        $sout.= sprintf("%{$klen}s: %s\n", $k, $swrap[0]);
                    }
                    for($n = 1; $n < count($swrap); $n++) {
                        $sout.= sprintf("%{$klen}s  %s\n", '', $swrap[$n]);
                    }
                } else {
                    $sout.= sprintf("%s\n", $v);
                }
            }

        }
        return $sout;

    }

    /**
     * Enable error reporting
     *
     * @param bool $notices Set to false to hide notices
     */
    static function enable($notices = true) {
        error_reporting(E_ERROR | E_WARNING | E_PARSE | (($notices) ? E_NOTICE : 0));
    }

    static function setDebugProvider(IDebugProvider $provider) {
        self::$provider = $provider;
    }

    /**
     *
     *
     */
    static function disable() {
        error_reporting(0);
    }

    static function inspect($array, $halt=true, $table=false) {
        if ($array) {
            if (self::$provider) call_user_func_array(array(self::$provider,'inspect'),array($array,$table));
        }
        if ($halt) die();
    }

    /**
     * @brief Print a debug backtrace
     *
     * @param integer $trim Number of items to trim from the top of the stack
     * @param array $stack The stack, if null will get current stack
     */
    static function backtrace($trim=1, $stack=null) {
        if (!$stack) {
            $stack = debug_backtrace(false);
        }
        $trace = array();
        foreach ($stack as $i => $method) {
            $args = array();
            if ($i > ($trim - 1)) {
                if (isset($method['args'])) {
                    foreach ($method['args'] as $arg) {
                        $args[] = gettype($arg);
                    }
                }
                $mark = (($i == ($trim)) ? 'in' : '  invoked from');
                if (!isset($method['file'])) {
                    if (isset($method['type'])) {
                        $trace[] = sprintf("  %s %s%s%s(%s) - %s:%d", $mark, $method['class'], $method['type'], $method['function'], join(',', $args), '???', 0);
                    } else {
                        $trace[] = sprintf("  %s %s(%s) - %s:%d", $mark, $method['function'], join(',', $args), '???', 0);
                    }
                } else {
                    if (isset($method['type'])) {
                        $trace[] = sprintf("  %s %s%s%s(%s) - %s:%d", $mark, $method['class'], $method['type'], $method['function'], join(',', $args), str_replace(SYS_PATH, '', $method['file']), $method['line']);
                    } else {
                        $trace[] = sprintf("  %s %s(%s) - %s:%d", $mark, $method['function'], join(',', $args), str_replace(SYS_PATH, '', $method['file']), $method['line']);
                    }
                }
            }
        }
        return join("\n", $trace) . "\n";
    }


}

class Profiler {

	private static $t = null;
	private static $e = null;
	private static $c = 0;
	private static $ci = null;
	
	const PE_ENTER = 1;
	const PE_LEAVE = 2;

	static function begin() {
		self::$t = new Timer(true);
		self::$e = array();
		self::$ci = array();
	}
	
	private static function getCi() {
		if (count(self::$ci) > 0) {
			return self::$ci[count(self::$ci)-1];
		}
		return null;
	}

	static function enter($routine) {
		self::$e[$c] = array(
			'event' => self::ENTER, 
			'parent' => self::getCi(), 
			'routine' => $routine,
			'time' => self::$t->getElapsed(),
			'elapsed' => null
		);
		self::$ci[] = $c;
		return($c++);
	}
	
	static function leave($cit) {
		while($ci != $cit) {
			$ci = self::getCi();
			array_pop(self::$ci);
		}
		$evt = self::$e[$ci];
		$evt['elapsed'] = self::$t->getElapsed() - $evt['time'];
		self::$e[$c] = array(
			'event' => self::ENTER, 
			'parent' => self::getCi(), 
			'routine' => $routine
		);
		self::$ci[] = $c;
		return($c++);
	}
	
	public function __destruct() {
		// debug::inspect(self::$e);
	}
	
}

$__profiler_scope = new Profiler();
