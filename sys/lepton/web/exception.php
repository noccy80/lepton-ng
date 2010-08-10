<?php

	class MvcExceptionHandler extends ExceptionHandler {

		static $ico_error;
		static $css;
			
		function image($img) {
			return "data:image/png;base64," . $img;
		}

		function exception(Exception $e) {
			echo '<html><head><title>Unhandled Exception</title>'.self::$css.'</head><body>' .
				'<div id="box"><div id="left"><img src="'.$this->image(self::$ico_error).'"></div><div id="main">' .
				'<h1>An Unhandled Exception Occured</h1>' .
				'<p>This means things didn\'t quite go as planned. Try agan in a bit</p>' .
				'</body></html>';

		}
	
	}

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
	MvcExceptionHandler::$css = '<style type="text/css">'.
			'body { background-color:#202020; }'.
			'#box { -moz-border-radius:10px; -webkit-border-radius:10px; -opera-border-radius:10px; background-color:#E0E0E0; padding:15px; width:690px; margin:20px auto 20px auto; overflow:hidden; }'.
			'#left { float:left; width:50px; text-align:center; padding-top:5px; }'.
			'#main { float:left; width:600px; }'.
			'h1 { margin:3px 0px 3px 0px; padding:0px 0px 3px 0px; font:bold 14pt sans-serif; color:#404040; border-bottom:solid 1px #808080;}'.
			'p { margin:3px 0px 3px 0px; padding:0px; font:8pt sans-serif; color:#404040; }'.
			'</style>';

	
	Lepton::setExceptionHandler('MvcExceptionHandler');
	
?>
