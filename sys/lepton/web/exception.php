<?php

	class MvcExceptionHandler extends ExceptionHandler {

		static $ico_error;
		static $css;
		static $js;

		static function saveFeedback($id) {

			header('content-type: text/html; charset=utf-8');
			echo '<html><head><title>Thank you for your feedback</title>'.
				self::$css.
				self::$js.
				'</head><body>' .
				'<div id="box"><div id="left"><img src="'.self::$ico_error.'" width="24" height="24"></div><div id="main">' .
				'<h1>Thank you for your feedback</h1>' .
				'<hr noshade>'.
				'<p>Your message have been saved and will hopefully lead to the problem being found and dealt with.</p>'.
				'<hr noshade>';
			echo '<p><a href="/">Back to front page</a></p>';

			return 0;

		}

		function exception(Exception $e) {

			$id = uniqid();
			$dbg = sprintf("Unhandled exception: (%s) %s\n  in %s:%d", get_class($e), $e->getMessage(), str_replace(SYS_PATH,'',$e->getFile()), $e->getLine())
			     . Console::backtrace(0,$e->getTrace(),true)
			     . "\n"
			     . "Loaded modules:\n"
			     . ModuleManager::debug()
			     . "\n"
			     . "Request time: ".date(DATE_RFC822,$_SERVER['REQUEST_TIME'])."\n"
			     . "Event id: ".$id."\n"
			     . "User-agent: ".$_SERVER['HTTP_USER_AGENT']."\n"
			     . "Request URI: ".$_SERVER['REQUEST_URI']."\n"
			     . "Request method: ".$_SERVER['REQUEST_METHOD']."\n"
			     . "Remote IP: ".$_SERVER['REMOTE_ADDR']." (".gethostbyaddr($_SERVER['REMOTE_ADDR']).")\n"
			     . "Hostname: ".$_SERVER['HTTP_HOST']."\n"
			     . "Referrer: ".$_SERVER['HTTP_REFERER']."\n"
			     . sprintf("Running as: %s (uid=%d, gid=%d) with pid %d", get_current_user(), getmyuid(), getmygid(), getmypid())."\n"
			     . sprintf("Memory allocated: %0.3f KB (Total used: %0.3f KB)", (memory_get_usage() / 1024 / 1024), (memory_get_usage(true) / 1024 / 1024))."\n"
			     . "Platform: ".LEPTON_PLATFORM_ID."\n"
			     . sprintf("Runtime: PHP v%d.%d.%d (%s)", PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION, PHP_OS)."\n"
			     ;

			if (config::get('lepton.mvc.exception.log',false)==true) {
				$logfile = config::get('lepton.mvc.exception.logfile',"/tmp/".$_SERVER['HTTP_HOST']."-debug.log");
				$log = "=== Unhandled Exception ===\n\n".$dbg."\n";
				$lf = fopen($logfile, "a+");
				fputs($lf,$log);
				fclose($lf);
			}

			header('content-type: text/html; charset=utf-8');
			echo '<html><head><title>Unhandled Exception</title>'.
				self::$css.
				self::$js.
				'</head><body>' .
				'<div id="box"><div id="left"><img src="'.self::$ico_error.'" width="24" height="24"></div><div id="main">' .
				'<h1>An Unhandled Exception Occured</h1>' .
				'<hr noshade>'.
				'<p>This means that something didn\'t go quite go as planned. This could be '.
				'caused by one of several reasons, so please be patient and try '.
				'again in a little while.</p>';
			if (config::get('lepton.mvc.exception.feedback',false) == true):
				echo '<p>The administrator of the website has been notified about this error. You '.
					'can help us find and fix the problem by writing a line or two about what you were doing when this '.
					'error occured.</p>';
				echo '<p id="feedbacklink"><a href="javascript:doFeedback();">If you would like to assist us with more information, please click here</a>.</p>';
				echo '<div id="feedback" style="display:none;"><p>Describe in a few short lines what you were doing right before you encountered this error:</p><form action="/errorevent.feedback/'.$id.'" method="post"><div><textarea name="text" style="width:100%; height:50px;"></textarea></div><div style="padding-top:5px; text-align:right;"><input type="button" value=" Close " onclick="closeFeedback();"> <input type="submit" value=" Submit Feedback "></div></form></div>';
			endif;
			if (config::get('lepton.mvc.exception.showdebug',false) == true):
				echo '<hr noshade>'.
					'<a href="javascript:toggleAdvanced();">Details &raquo;</a>'.
					'<pre id="advanced" style="display:none; height:300px;">'.$dbg.'</pre>';
			endif;
				echo '<div>'.
					'</body></html>';

		}

	}

	if (config::get('lepton.mvc.exception.feedback',false)==true) Router::hookRequestUri('^\/errorevent\.feedback\/(.*)$', array('MvcExceptionHandler','saveFeedback'));

	// MvcExceptionHandler::$ico_error = file_get_contents(SYS_PATH.'/res/ico_error.b64');
	// Old icon
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
	MvcExceptionHandler::$ico_error = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAMAAADXqc3KAAAAAXNSR0IArs4c6QAAAv1QTFRFwAAs
AAEAQAwKbQEFVwoNnwAQhwoRrAAXqwAcXBgZtAAUrgIYtAAhtgActwAirwQTrwQZghIVvQAruAIp
vQAwvwArpAklvwAxtwMuwQAyuQQkwgEurwglyAAwyAA1wgIzyQA2ywAyygA3ygA8ywA9uggqwwU0
zAI+0wBMzAND0wBRuwsrnxQpqRIo1AFSzQY/xAo61gNOzQdE1QVTzgpFvREyvRI3zQxKxhA8xhBB
1gpZrRo2tRg5zw9LzhBQ0BJMzxJR1g9f0RRN2BFb0BVS0BZXuR9C0hdT2BVh0RhYyxxK0hpZ2hdi
0xxaviVGxiNJ1R5a1B9gyCVK2x1p2CFX1SFh0SVUyShR1iNh1SNn2SVe1iVo3iNx2Cdp2Shq2Clv
4Cdz2Spw2ytr1S5j4il02ixx2y1y3S9z2TJm1DVa0zVf3jB03TF53zJ12TZy3zR74DV83Dhv4jd9
2z1k3zx34zqE0kdp4kCA4UNp4EGF1Ehq3kR54UKG40KB5EOC4Ud85Uhy5UaJ5klz5keK50p04kqE
4kyK4E5+0lVv5E2L5U6M2FV351CO41OH21d52Fx65leQ6VqT5F2S4F+L516N5l+U6GCV6WGW42SV
6mOX62SY52eY6Wma62qb7Gyc6myi7W2d53Ci6HGj6nGe63Kf7HOg7XSh7HWn73Wi8Haj2oGU7Xqk
7Hyr732m8H6n7ICn7oOp6oWp74Sq8Yez7Yms7Imy74qt6oyt8Iuu8Yyv5JKo8Y639I+z8ZO08JS6
8pS185W29Za39Je+7pm88Ju/06a28pzA56K07p/B66G795+98KHD+KC+9aO//KK696XB8afA8KjH
86nC+ajD+qnE86vK9azL86/G967N7LXB9bLJ+LTL9rnO+LvQ873Q/rrR9L7R+73S+MLU+sTX+8XY
/8TZ8srZ/sfa+crb+8vc/8rd9s/d7tLX/c7e8t3g+d3i+OTm8Obs9+Xt8eft+fTy+vXz//T7+/j9
//r59P75//z6/vz/+f///P/7/v/8JYXqvwAAAAF0Uk5TAEDm2GYAAAABYktHRACIBR1IAAAACXBI
WXMAAAsSAAALEgHS3X78AAAAB3RJTUUH2ggLARcwupiHJAAAAiVJREFUKM9jYLhw4fTZi5euXb9+
+87dh48ePXn+8u3rZwwMDIenOHrEB+fklzZOnDh70fLNW/cePn354VEGhq12ARFJ6fm1lW0TJ81Z
uXrT1oMHj1278YqBYbFdRHB8enppZduEifPnL1m/Y/exM+evPGVgmG3n6RMdn5dfVFnZNmnh/DVb
d+47dPrGfQaGKeYBQLOScnMO//uxeOaSles27zl15vwZBoZec9fAwODUnEnfi6b/mT5/yeotW/cd
WtnCwNBu4uIaGJkU+312Zefdx5MWrlm/43C9PVCi2sje1TMw4uydnIWL2n6smb1ozcooY/cmBoZC
dQcXT8/0P/Gz//yZtObHrAXVRub29g0MDBnqJi6eru8Xxy/+/WtO1+vj6uYa9ubmFQwMURbm9u7b
nzlGT/j3uzRQ6XeUORCYFDMwhCiZmJv/iYiOTf3zxVJTs++DtJG+pVEmA0O4hb6lSZalsrL0548W
BgYG93ZZWKpbhDEw+KmpGRgAkbJSf5+yAZDzByit5sXA4KYG4kmqqYl0l0mC2B3v1dTU/BkYbKQl
xYXExU3F/f/8ERKSlJT0+yMqLmTKwGAtLCEhLiEhIizx8Y328T9//nxdJSwhIsHAoKsqJSwlDAQ8
3Dw8vFxAAOLKMDCIcfBx8PBwACGQlOGAsDk4GBh0gDS3AIjDzcMLFAUr4WBlYGDmZ+cGAhDBzg5m
Atm8LMDUwCnIhgGYGBlwAgDeuckvbbM0gwAAAABJRU5ErkJggg==";


	MvcExceptionHandler::$css = '<style type="text/css">'.
			'body { background-color:#202020; }'.
			'hr { height:1px; color:#C0C0C0; background-color:#C0C0C0; border:solid 1px transparent; padding:0px; margin:10px 0px 10px 0px; }'.
			'#box { -moz-border-radius:10px; -webkit-border-radius:10px; -opera-border-radius:10px; background-color:#E0E0E0; padding:15px; width:690px; margin:50px auto 20px auto; overflow:hidden; border:solid 2px #C0C0C0; -moz-box-shadow:5px 5px 25px #000; }'.
			'#left { float:left; width:20px; padding:2px 25px 0px 10px; }'.
			'#main { float:left; width:630px; }'.
			'h1 { margin:4px 0px 4px 0px; padding:0px; font:bold 14pt sans-serif; color:#404040; }'.
			'p { margin:4px 0px 4px 0px; padding:0px; font:8pt sans-serif; color:#404040; }'.
			'textarea { overflow-y:scroll; overflow-x:hidden; font-size:8pt; padding:5px; background-color:#F8F8F8; border:inset 1px #F0F0F0; }'.
			'pre { overflow-x:scroll; overflow-y:scroll; font-size:8pt; padding:5px; background-color:#F8F8F8; border:inset 1px #F0F0F0; }'.
			'a { color:#A06060; text-decoration:underline; font: 8pt sans-serif; text-decoration:none; }'.
			'a:hover { text-decoration:underline; }'.
			'input[type=button] { font:8pt sans-serif; color:#606060; }'.
			'input[type=submit] { font:8pt sans-serif; color:#202020; }'.
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
			'function doFeedback() {'.
			'document.getElementById("feedbacklink").style.display = "none";'.
			'document.getElementById("feedback").style.display = "block";'.
			'}'.
			'function closeFeedback() {'.
			'document.getElementById("feedbacklink").style.display = "block";'.
			'document.getElementById("feedback").style.display = "none";'.
			'}'.
			'</script>';

	
	Lepton::setExceptionHandler('MvcExceptionHandler');
	
?>
