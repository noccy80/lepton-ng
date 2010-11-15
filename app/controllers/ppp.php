<?php

using('lepton.user.*');
using('lepton.user.providers.*');

class PppController extends Controller {

	function index() {
		printf('<form action="/ppp/login" method="post">');
		printf('<p>Username: <input type="text" name="username"></p>');
		printf('<p>Password: <input type="password" name="password"></p>');
		printf('<p><input type="submit"></p>');
		printf('</form>');
	}
	
	function login() {
		$user = request::post('username')->toString();
		$pass = request::post('password')->toString();
		if (User::authenticate(new PasswordAuthentication($user,$pass))) {
			session::set('username',$user);
			response::redirect('/ppp/confirm');
		}
	}
	
	function confirm() {
		$code = request::post('pppcode')->toString();
		$user = session::get('username');
		if ($code) {
		if (User::authenticate(new PppAuthentication($user,(string)$code))) {
			printf('<h1>Success.</h1>');
			return;
		} else {
			printf('Failure');
		}
		}
		$code = PppAuthentication::getNextIdentifier($user);
		printf('<form action="/ppp/confirm" method="post">');
		printf('<p>Enter code <b>%s</b>: <input type="text" name="pppcode"></p>', PppAuthentication::cardIndexToString($code));
		printf('<p><input type="submit"></p>');
		printf('</form>');
	}

	function printcard() {
		$user = session::get('username');
		$key = PppAuthentication::getKeyForuser($user);
		for ($n = 0; $n < 3; $n++) {
			printf('<pre>');
			PppAuthentication::printPasswordCard($user,4,$n,'leptonng.noccylabs.info');
			printf('</pre>');
		}
	}

}
