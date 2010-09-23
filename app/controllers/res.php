<?php

    class ResController extends Controller {

        var $contenttypes = array(
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'css' => 'text/css',
            'js' => 'text/javascript'
        );

        function __request($method,$args) {
            // URL has been mangled here so we better un-mangle it!
            // TODO: This should be inserted in a chain BEFORE the router!
            $url = join('/',array_merge(array('app','res',$method),$args));
            $ct = null;
            foreach($this->contenttypes as $ext=>$type) {
                if (preg_match('/\.'.$ext.'$/i',$url)) {
                    $ct = $type;
                }
            }

            header('HTTP/1.1 200 Content Follows', true);
            header('Content-Type: '.$ct, true);
            print(file_get_contents($url));
            
            return RETURN_SUCCESS;
        }

    }
