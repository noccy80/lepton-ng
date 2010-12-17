<?php

    /**
     * Response class; wraps functionality related to the response sent to the
     * client, such as content type and cache.
     *
     * @author Christopher Vagnetoft <noccy@chillat.n>
     */
    class response {

        /**
         * Set the expiry of the page in the client cache.
         *
         * @param int $minutes The number of minutes the content is valid
         */
        static function expires($minutes) {
            $offset = 60 * $minutes * -1;
            header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT');
            header('Cache-Control: must-revalidate');
            header('Expires: '.gmdate("D, d M Y H:i:s",time() + $offset) . ' GMT');
        }

        /**
         * Set the content type of the response, and optionally add a content
         * disposition header to force download of the file.
         *
         * @param string $type The content type
         * @param string $filename The filename to download as (optional)
         */
        static function contentType($type, $filename=null) {
            if (!headers_sent()) {
                if ($filename) {
                    header('Content-disposition: attachment; filename='.$filename);
                } else {
                    header('Content-type: '.$type,true);
                }
            }
        }

        /**
         * Sets the content type from the provided filename. This function is
         * VERY limited but handles the common cases.
         *
         * @param string $file The file to extract the content type from
         */
        static function contentTypeFromFile($file) {

            $fe = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (array_key_exists($fe,lepton::$mimetypes)) {
                return(lepton::$mimetypes[$fe]);
            } else {
                if (function_exists('mime_content_type')) {
                    $mimetype = @mime_content_type($file);
                    return($mimetype);
                } else {
                    if (function_exists('finfo_open')) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
                        $mimetype = finfo_file($finfo, $file);
                        finfo_close($finfo);
                        return($mimetype);
                    } else {
                        return('application/octet-stream');
                    }
                }
            }

        }

        /**
         *
         *
         */
        static function sendJson($data) {
            response::contentType('text/json');
            echo json_encode($data);
        }

        /**
         * Sends a file from the file system to the client. If the file is to
         * be downloaded, response::contentType() should be called first
         *
         * @see response::contentType
         * @param string $file The file on the server to send to the client.
         * @param string $contenttype The contenttype to set
         */
        static function sendFile($file, $contenttype=null) {

            if (!file_exists($file)) {
                throw new BaseException("File not found: ".$file);
            }
            if ($contenttype) {
                response::contentType($contenttype);
            } else {
                $mimetype = response::contentTypeFromFile($file);
                response::contentType($mimetype);
            }
            echo file_get_contents($file);
        }

        static function setStatus($status = 200) {
            if (!headers_sent()) header('HTTP/1.1 '.strval(intval($status)),true);
        }

        static function streamFile($file, $contenttype) {

            $filelen = filesize($file);

            // Stream entire file
            $contentlen = $filelen;
            response::contentType($contenttype);
            response::setHeader('content-length', $filelen);
            $fh = fopen($file,'rb');
            while( !feof($fh) ) {
                $fd = fgets($fh,4096);
                echo $fd;
                flush();
                if ( connection_aborted() ) break;
            }
            fclose($fh);

        }

        /**
         * Sets a response header.
         *
         * @param string $header The header to set
         * @param string $value The new value of the header
         */
        static function setHeader($header, $value) {
            header($header.': '.$value);
        }

        /**
         * Set the response headers from an associative array. Use this method
         * to quickly set several headers at once.
         *
         * @param array $array The array of headers to set
         */
        static function setHeaders($array) {
            foreach($array as $key=>$value) {
                response::setHeader($key,$value);
            }
        }

        /**
         * Sets the script timeout in seconds.
         *
         * @param int $seconds The number of seconds the script is allowed to run
         */
        static function setTimeout($seconds) {
            set_time_limit($seconds);
        }

        /**
         * Redirect the client to another URL.
         *
         * @param string $to The URL to redirect to
         * @param int $code The HTTP status to use for the redirect
         */
        static function redirect($to,$code=302) {

            if (!isset($_SERVER['SERVER_PORT'])) {
            	console::debug('Redirect (%d) requested to %s', $code, $to);
            	return;
            }

            $location = null;
            $sn = $_SERVER['SCRIPT_NAME'];
            $cp = dirname($sn);
            if (substr($to,0,7)=='http://') {
                $location = $to; // Absolute URL
            } else {
                $schema = $_SERVER['SERVER_PORT']=='443'?'https':'http';
                $host = strlen($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:$_SERVER['SERVER_NAME'];
                if (substr($to,0,1)=='/') {
                    $location = "$schema://$host$to";
                } elseif (substr($to,0,1)=='.') { // Relative Path
                    $location = "$schema://$host/";
                    $pu = parse_url($to);
                    $cd = dirname($_SERVER['SCRIPT_FILENAME']).'/';
                    $np = realpath($cd.$pu['path']);
                    $np = str_replace($_SERVER['DOCUMENT_ROOT'],'',$np);
                    $location.= $np;
                    if ((isset($pu['query'])) && (strlen($pu['query'])>0)) $location.= '?'.$pu['query'];
                }
            }

            $hs = headers_sent();
            if ($hs==false) {
                switch($code) {
                    case 301:
                        header("301 Moved Permanently HTTP/1.1"); // Convert to GET
                        break;
                    case 302:
                        header("302 Found HTTP/1.1"); // Conform re-POST
                        break;
                    case 303:
                        header("303 See Other HTTP/1.1"); // dont cache, always use GET
                        break;
                    case 304:
                        header("304 Not Modified HTTP/1.1"); // use cache
                        break;
                    case 305:
                        header("305 Use Proxy HTTP/1.1");
                        break;
                    case 306:
                        header("306 Not Used HTTP/1.1");
                        break;
                    case 307:
                        header("307 Temporary Redirect HTTP/1.1");
                        break;
                    default:
                        trigger_error("Unhandled redirect() HTTP Code: $code",E_USER_ERROR);
                        break;
                }
                header("Location: $location");
                header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            } elseif (($hs==true) || ($code==302) || ($code==303)) {
                echo "<script type=\"text/javascript\"> document.location = '$to' </script>\n";
                echo "<p>Redirecting to <a href='$to'>".htmlspecialchars($location)."</a></p>\n";
            }
            exit(0);

        }

        /**
         *
         *
         */
        static function buffer($state) {

            if ($state) {
                # ob_start(array('response','__buffercb'));
                ob_start();
            } else {
                ob_end_flush();
            }

        }

        /**
         * Buffer callback. Used internally.
         *
         * @internal
         */
        static function __buffercb($data) {
            return strlen($data);
            response::$bufferdata = $data;
            return "";
        }

        /**
         *
         *
         */
        static function getBuffer() {

            $data = ob_get_contents();
            ob_end_clean();
            return $data;

        }

        /**
         *
         *
         */
        static function clear() {

            ob_end_clean();

        }

        /**
         *
         *
         */
        static function flush() {

            ob_flush();
            flush();

        }

        static function end() {
            header('HTTP/1.0 204 No Content');
            header('Content-Length: 0',true);
            header('Content-Type: text/html',true);
            flush();
        }

    }

?>
