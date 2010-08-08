<?php

	interface IAuthenticationProvider {
		function isTokenValid(); /// Returns true if the tokens match
		function login();
		function logout();
	}

	abstract class AuthenticationProvider implements IAuthenticationProvider {
		protected function setUser($id) {

		}
		protected function clearUser() {

		}
	}



?>
