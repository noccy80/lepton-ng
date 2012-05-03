<?php

using('lepton.web.url');

class LdwpManager {

    function request($event,$data) {
        $qurl = $data['uri'];
    	if (url($qurl)->like('/^\/ldwp-webxml\//')) {
            view::load('base:/dist/ldwp/dynamic/manager.php');
			return true;    	
    	}
        return false;
    }

}

event::register('lepton.mvc.routing.pre', new EventHandler('LdwpManager','request'));
