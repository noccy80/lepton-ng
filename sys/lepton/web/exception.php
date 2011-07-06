<?php module("MVC Exception Handler");

using('resource.resource');
using('lepton.mvc.router');

class MvcExceptionHandler extends ExceptionHandler {

    static $ico_error;
    static $css;
    static $js;

    static function saveFeedback($id) {

	$ico_error = resource::get('warning.png');
        header('content-type: text/html; charset=utf-8');
        echo '<html><head><title>Thank you for your feedback</title>'.
            self::$css.
            self::$js.
            '</head><body>' .
            '<div id="box"><div id="left"><img src="'.$ico_error.'" width="24" height="24"></div><div id="main">' .
            '<h1>Thank you for your feedback</h1>' .
            '<hr noshade>'.
            '<p>Your message have been saved and will hopefully lead to the problem being found and dealt with.</p>'.
            '<hr noshade>';
        echo '<p><a href="/">Back to front page</a></p>';

        return 0;

    }

    function exception(Exception $e) {

        @ob_end_clean();

        response::setStatus(500);
        logger::emerg("Unhandled exception: (%s) %s in %s:%d", get_class($e), $e->getMessage(), str_replace(BASE_PATH,'',$e->getFile()), $e->getLine());

        header('HTTP/1.1 501 Server Error', true);

        if (($_SERVER['HTTPS'] != 'off') && ($_SERVER['HTTPS'] != null)) {
            $ssl = 'Yes ('.$_SERVER['SSL_TLS_SNI'].')';
        } else {
            $ssl = 'No';
        }
        $id = uniqid();
        $dbg = sprintf("Unhandled exception: (%s) %s\n  in %s:%d", get_class($e), $e->getMessage(), str_replace(SYS_PATH,'',$e->getFile()), $e->getLine())
            . Console::backtrace(0,$e->getTrace(),true)
            . "\n"
            . "Loaded modules:\n"
            . ModuleManager::debug()
            . "\n"
            . "Request time: ".date(DATE_RFC822,$_SERVER['REQUEST_TIME'])."\n"
            . "Event id: ".$id."\n"
            . "Base path: ".base::basePath()."\n"
            . "App path: ".base::appPath()."\n"
            . "Sys path: ".base::sysPath()."\n"
            . "User-agent: ".$_SERVER['HTTP_USER_AGENT']."\n"
            . "Request URI: ".$_SERVER['REQUEST_URI']."\n"
            . "Request method: ".$_SERVER['REQUEST_METHOD']."\n"
            . "Remote IP: ".$_SERVER['REMOTE_ADDR']." (".gethostbyaddr($_SERVER['REMOTE_ADDR']).")\n"
            . "Hostname: ".$_SERVER['HTTP_HOST']."\n"
            . "Secure: ".$ssl."\n"
            . "Referrer: ".(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'null')."\n"
            . sprintf("Running as: %s (uid=%d, gid=%d) with pid %d", get_current_user(), getmyuid(), getmygid(), getmypid())."\n"
            . sprintf("Server: %s", $_SERVER['SERVER_SOFTWARE'])." (".php_sapi_name().")\n"
            . sprintf("Memory allocated: %0.3f KB (Total used: %0.3f KB)", (memory_get_usage() / 1024 / 1024), (memory_get_usage(true) / 1024 / 1024))."\n"
            . "Platform: ".LEPTON_PLATFORM_ID."\n"
            . sprintf("Runtime: PHP v%d.%d.%d (%s)", PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION, PHP_OS)."\n"
        ;

        logger::emerg($dbg);

        if (config::get('lepton.mvc.exception.log',false)==true) {
            $logfile = config::get('lepton.mvc.exception.logfile',"/tmp/".$_SERVER['HTTP_HOST']."-debug.log");
            $log = "=== Unhandled Exception ===\n\n".$dbg."\n";
            $lf = @fopen($logfile, "a+");
            if ($lf) {
                fputs($lf,$log);
                fclose($lf);
            }
        }

	$ico_error = resource::get('warning.png');
        header('content-type: text/html; charset=utf-8');
        echo '<html><head><title>Unhandled Exception</title>'.
            self::$css.
            self::$js.
            '</head><body>' .
            '<div id="box"><div id="left"><img src="'.$ico_error.'" width="32" height="32"></div><div id="main">' .
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
