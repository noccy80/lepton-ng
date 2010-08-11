<?php

	class MvcExceptionHandler extends ExceptionHandler {

		static $ico_error;
		static $css;
		static $js;

		function image($img) {
			return "data:image/png;base64," . $img;
		}

		function exception(Exception $e) {

			$dbg = sprintf("Unhandled exception: (%s) %s\n  in %s:%d", get_class($e), $e->getMessage(), str_replace(SYS_PATH,'',$e->getFile()), $e->getLine())
			     . Console::backtrace(0,$e->getTrace(),true)
			     . "\n"
			     . "Loaded modules:\n"
			     . ModuleManager::debug()
			     . "\n"
			     . "Request time: ".date(DATE_RFC822,$_SERVER['REQUEST_TIME'])."\n"
			     . "User-agent: ".$_SERVER['HTTP_USER_AGENT']."\n"
			     . "Request URI: ".$_SERVER['REQUEST_URI']."\n"
			     . "Remote IP: ".$_SERVER['REMOTE_ADDR']." (".$_SERVER['REMOTE_HOST'].")\n"
			     . "Hostname: ".$_SERVER['HTTP_HOST']."\n"
			     . "Platform: ".LEPTON_PLATFORM_ID."\n"
			     ;

			header('content-type: text/html; charset=utf-8');
			echo '<html><head><title>Unhandled Exception</title>'.
				self::$css.
				self::$js.
				'</head><body>' .
				'<div id="box"><div id="left"><img src="'.$this->image(self::$ico_error).'" width="16" height="16"></div><div id="main">' .
				'<h1>An Unhandled Exception Occured</h1>' .
				'<hr noshade>'.
				'<p>This means that something didn\'t go quite go as planned. This could be '.
				'caused by one of several reasons, so please be patient and try '.
				'again in a little while.</p>' .
				'<p>The administrator of the website has been notified about this error</p>'.
				'<hr noshade>'.
				'<a href="javascript:toggleAdvanced();">Details &raquo;</a>'.
				'<pre id="advanced" style="display:none;">'.$dbg.'</pre>'.
				'</body></html>';

		}

	}

	MvcExceptionHandler::$ico_error = file_get_contents(SYS_PATH.'/res/ico_error.b64');
/*
	MvcExceptionHandler::$ico_error = "iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJ".
			"bWFnZVJlYWR5ccllPAAAAlpJREFUeNqkU8tu2lAQHT8wtlEQcUKUIjVVgaiCVkhIlSq1isSKTdRN".
			"uu5P8AX5Alb9g+6zqZR8QNWmC3ZRa1UJIm0hAWpeNthg/OiMechl00UtHXvuvXPOnbn3mPF9H/7n".
			"4en1nmGAwy+BAUghTjB8iThY5v1EfMatzhB3Lg4Ib3FzfkPwdUSSKulCIZs6PFSkeFykCi1dL95d".
			"Xx81rq7e2JZVxbwPf1WwIkuJxOmL4+Ocz/PSzHHgvtEIFhRFkfdzOTmZTu/ULi5OJ6MRrERYemFZ".
			"KU4UK8VyOTcyTWk4HEKr1YLC+XkAimluPJ1Kz0qlHBuNVoizFsB+Tg7y+ezAMKQRqhuGAaZprkuj".
			"mOZ0XQcDRfYymay7OKdFCw7Aq61kUtH6/TVpPB5Dp9MJSLfYiue6i555Hna3txXi4PDdSuChx7Ki".
			"g3278zkYgwGYkwk0m02IRCLA4jy3Usb1qWmKxAlXAA4u2FQ6VuHjbhGcI3IsFgNh47Q5zHXCtzAH".
			"+GV0u0Vf02QpZCy1VAq+8Y27ntv2lDjrQ0S1T912u7eF/ck4lheGgpKqQrleD2I5BN2y+sQJC5zd".
			"9np1YFlLRldSUhQhCEKwYzRE9jzPas9mN8RZC3hoz4nrVi81TcUFS0KRJM5/yWQCUCwhbCTXxmPV".
			"9LwqcYjLkFUZJDzCwXN042OWreQEIftEEJQEx4mUNHTd6Xfb7qu2fdNAcg1d+IMMSNylAB3mDmIX".
			"7bWfBzjaA3iKV/dgabT7LsDXbwAfcVsM4TdCQ66zEmBDbfL/+IPJURMyKHK9PwIMAA7iHkoee771".
			"AAAAAElFTkSuQmCC";
*/
	MvcExceptionHandler::$css = '<style type="text/css">'.
			'body { background-color:#202020; }'.
			'hr { height:1px; color:#C0C0C0; background-color:#C0C0C0; border:solid 1px transparent; padding:0px; margin:10px 0px 10px 0px; }'.
			'#box { -moz-border-radius:10px; -webkit-border-radius:10px; -opera-border-radius:10px; background-color:#E0E0E0; padding:15px; width:690px; margin:20px auto 20px auto; overflow:hidden; }'.
			'#left { float:left; width:20px; padding:5px 18px 0px 15px; }'.
			'#main { float:left; width:630px; }'.
			'h1 { margin:3px 0px 3px 0px; padding:0px; font:bold 14pt sans-serif; color:#404040; }'.
			'p { margin:3px 0px 3px 0px; padding:0px; font:8pt sans-serif; color:#404040; }'.
			'pre { overflow-x:scroll; overflow-y:hidden; font-size:8pt; padding:5px; background-color:#F8F8F8; border:inset 1px #F0F0F0; }'.
			'a { color:#202020; text-decoration:underline; font: 8pt sans-serif; text-decoration:none; }'.
			'a:hover { text-decoration:underline; }'.
			'</style>';

	MvcExceptionHandler::$js = '<script type="text/javascript">'.
			'function toggleAdvanced() { '.
			'var el = document.getElementById("advanced"); '.
			'if (el.style.display == "none") { '.
			'el.style.display = "block"; '.
			'} else { '.
			'el.style.display = "none"; '.
			'}'.
			'}'.
			'</script>';

	
	Lepton::setExceptionHandler('MvcExceptionHandler');
	
?>
