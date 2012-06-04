<?php
/**
 * @file requesthandler.php
 * @brief Lepton Server-to-Server Request Handler
 * 
 * @author Christopher Vagnetoft
 */

// We need URL support to match the queries
using('lepton.web.url');

using('s2s.request');
using('s2s.response');

class S2SRequestHandler {

    static function request($event,$data) {
        $qurl = $data['uri'];
    	if (url($qurl)->like('/^\/sts-rpc[\/]?/')) {
            using('s2s.request');
            $cmd = request::get('cmd');
            $req = new S2SRequest(S2SRequest::RT_HTTP, $cmd, request::getAll());
            $res = new S2SResponse(S2SRequest::RT_HTTP, $cmd);
            $cmdp = explode('.',$cmd);
            $mod = $cmdp[0];
            $modc = $mod.'S2SEndpoint';
            if (class_exists($modc)) {
                $ci = new $modc();
                $rd = $ci->invoke($req,$res);
                var_dump($rd);
            } else {
                printf("Bad endpoint");
            }
			return true;    	
    	}
        return false;
    }

}

event::register('lepton.mvc.routing.pre', new EventHandler('S2SRequestHandler','request'));
