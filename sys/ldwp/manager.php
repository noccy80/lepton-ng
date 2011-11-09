<?php

class LdwpManager {

    function request($event,$data) {
        return false;
    }

}

event::register('lepton.mvc.routing.pre', new EventHandler('LdwpManager','request'));
