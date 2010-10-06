<?php

class AuthenticationController extends Controller {

	// Method to handle registration requests.
	function register($username,$password) {

		// Create a new user record and assign properties
		$u = new UserRecord();
		$u->username = $username;
		$u->password = $password;
		// Finally create the user
		user::create($u);

	}

	function autologin() {

		// Log in using cookie authentication
		$auth = new CookieAuthentication()
		if (user::authenticate($auth)) {
			// Logged in, redirect to inside
			response::redirect('/inside/');
		}

	}

	function login() {

		// Get the form
		$f = new Webform(array(
			'username' => 'required',
			'password' => 'required'
		));

		// Log in using password
		$auth = new PasswordAuthentication($f->username,$f->password)
		if (user::authenticate($auth)) {
			// Logged in, redirect to inside
			response::redirect('/inside/');
		} else {
			// Not logged in. Send to login page
		}

	}

}
