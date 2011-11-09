<?php

    class CurlInstance {

        const METHOD_GET = 0;
        const METHOD_POST = 1;
        const METHOD_PUT = 2;
        const METHOD_HEAD = 3;
        const METHOD_PROPGET = 4;
        const METHOD_PROPSET = 5;

        private $ch;
        private $url;
        private $params;
        private $headers;
        private $_contentLength = 0;
        private $_responseData = '';
        private $_responseHeaders = array();
        private $_progress_cb = null;
        private $_target = null;
        private $_htarget = null;

        function __construct($url=null) {
            if (!function_exists('curl_init'))
                // Removed by miniman (ticket #55)
                // Lepton::raiseError('Curl Library missing', 'You need to install the php5-curl package or similar');
                throw new ConfigurationException('You need to install the php5-curl package or similar', ConfigurationException::ERR_MISSING_DEPENDENCY);
            $this->url = $url;
            $this->ch = curl_init();
            if (!$this->ch) {
                unset($this->ch);
                throw new CurlException('Failed to retrieve Curl handle',CurlException::ERR_GENERIC);
            }
        }

        function __destruct() {
            if ($this->_htarget) fclose($this->_htarget);
            $this->close();
        }

        function close() {
            if (isset($this->ch)) {
                curl_close($this->ch);
                unset($this->ch);
            }
        }

        function setTarget($target=null) {
            $this->_target = $target;
            if ($this->_target) {
                $this->_htarget = fopen($this->_target,'wb');
            }
        }

        function setOption($option,$value) {
            curl_setopt($this->ch,$option,$value);
        }

        function setParams($params) {
            $this->params = $params;
        }

        function setHeader($header,$value) {
            $this->headers[$header] = $value;
            $th = array();
            foreach($this->headers as $key=>$value) {
                $th[] = sprintf('%s: %s', $key, $value);
            }
            $this->setOption(CURLOPT_HTTPHEADER, $th);
        }

        function setReferer($referer) {
            $this->setOption(CURLOPT_REFERER, $referer);
        }

        function setUserAgent($ua) {
            $this->setOption(CURLOPT_USERAGENT,$ua);
        }

        private function _headercb($ch, $header) {
            if (preg_match('/^(.*)\: (.*)\r$/',$header,$vals)) {
                $this->_responseHeaders[$vals[1]] = $vals[2];
                if (strtolower($vals[1]) == 'content-length') {
                    $this->_contentLength = $vals[2];
                }
            }
            # $this->_responseHeaders[] = $header;
            return strlen($header);
        }

        public function setProgressCallback(Callback $cb) {
            $this->_progress_cb = $cb;
        }

        private function _writecb($ch, $content) {
            $this->_bytesRead += strlen($content);
            if ($this->_htarget) {
                fwrite($this->_htarget, $content, strlen($content));
            } else {
                $this->_responseData.=$content;
            }
            if ($this->_progress_cb) $this->_progress_cb->call($this->_contentLength, $this->_bytesRead);
            return strlen($content);
        }

        function exec($method=CurlInstance::METHOD_GET) {

            $this->setOption(CURLOPT_HEADER, false);
            switch($method) {
            case CurlInstance::METHOD_HEAD:
                $this->setOption(CURLOPT_URL,$this->url);
                $this->setOption(CURLOPT_CUSTOMREQUEST,'HEAD');
                $this->setOption(CURLOPT_FILETIME, true);
                $this->setOption(CURLOPT_NOBODY, true);
                // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);            
                
                break;
            case CurlInstance::METHOD_GET:
                if (is_array($this->params)) {
                    $argl = array();
                    // Parse the existing query string
                    if(strpos($this->url,'?')!==false) {
                        $qs = substr($this->url,strpos($this->url,'?')+1);
                        $qse = explode('&',$qs);
                        foreach($qse as $qsi) {
                            $si = strpos($qsi,'=');
                            if ($si!==null) {
                                $key = urldecode(substr($qsi,0,$si));
                                $value = urldecode(substr($qsi,$si+1));
                                $argl[$key]=$value;
                            } else {
                                $argl[] = $value;
                            }
                        }
                        $url = substr($this->url,0,strpos($this->url,'?'));
                    } else {
                        $url = $this->url;
                    }
                    // Merge in the params
                    foreach($this->params as $key => $param) {
                        $argl[$key] = $param;
                    }
                    // And create the resulting args
                    $args = array();
                    foreach($argl as $key=>$param) {
                        $args[] = urlencode($key).'='.urlencode($param);
                    }
                    $url = $url.'?'.join('&',$args);
                    $this->setOption(CURLOPT_URL,$url);
                } else {
                    $this->setOption(CURLOPT_URL,$this->url);
                }
                $this->setOption(CURLOPT_HTTPGET, true);
                $this->setOption(CURLOPT_FOLLOWLOCATION, true);
                break;
            case CurlInstance::METHOD_POST:
                $this->setOption(CURLOPT_URL,$this->url);
                if (is_array($this->params)) {
                    $out = array();
                    foreach($this->params as $key=>$val) {
                        $out[] = $key.'='.urlencode($val);
                    }
                    $params = join('&',$out);
                    $this->setHeader('content-length', strlen($params));
                    $this->setOption(CURLOPT_POSTFIELDS, $params);
                } else {
                    $this->setOption(CURLOPT_POSTFIELDS, $this->params);
                }
                $this->setOption(CURLOPT_POSTFIELDS, $this->params);
                $this->setOption(CURLOPT_POST, true);
                $this->setOption(CURLOPT_FOLLOWLOCATION, true);
                break;
            }
            $this->setOption(CURLOPT_HEADERFUNCTION, array(&$this,'_headercb'));
            $this->setOption(CURLOPT_WRITEFUNCTION, array(&$this,'_writecb'));

            curl_exec($this->ch);
            $r['content'] = $this->_responseData;
            $r['headers'] = $this->_responseHeaders;
            $r['code'] = curl_getinfo($this->ch,CURLINFO_HTTP_CODE);
            $r['ce'] = curl_errno($this->ch);
            $this->close();
            return $r;

        }

    }

    class CurlException extends Exception {

        const ERR_GENERIC = 1; // Generic error

    }

