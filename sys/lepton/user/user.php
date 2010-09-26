<?php __fileinfo("User Classes");

class User {

	private $userid;

	function __construct($userid=null) {

		if ($userid) {
			$this->loadUser($userid);
		}

	}

	function loadUser($userid) {
		
	}

}
