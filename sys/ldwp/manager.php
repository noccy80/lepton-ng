<?php

class LdwpManager {

	function request($event,$data) {
		return false;
	}

}

event::register(MvcEvent::EVENT_BEFORE_ROUTING, new EventHandler('LdwpManager','request'));
