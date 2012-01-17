<?php

using('net.lepton.url');

class LdwpManager {

    function request($event,$data) {
    	if (url($event)->like('/^ldwp-webxml\/')) {
			echo "Ohai";
			return true;    	
    	}
        return false;
    }

}

event::register('lepton.mvc.routing.pre', new EventHandler('LdwpManager','request'));
