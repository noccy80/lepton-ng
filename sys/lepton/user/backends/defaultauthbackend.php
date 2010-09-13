<?php

	/**
	 * @file defaultauthbackend.php
	 * @package lepton.user.backends.defaultauthbackend
	 *
	 * Default authentication backend. Performs authentication against the 
	 * main lepton database using the user salt a
	 *
	 * @author Christopher Vagnetoft <noccy@chillat.net>
	 * @copyright (c) 2010, NoccyLabs.info
	 * @license GNU GPL v3
	 */
	 
	/**
	 * @class DefaultAuthBackend
	 *
	 */
	class DefaultAuthBackend extends AuthenticationBackend {

		private $userid;
		
		/**
		 * @brief Test a password for a username.
		 * Returns true if the password is a valid authentication token for
		 * the specified user.
		 *
		 * @param string $username The username to match against
		 * @param string $password The password to match with
		 * @return bool True on success.
		 */
		function testUserPassword($username,$password,$ext=false) {
			$db = new DatabaseConnection();
			try {
				$userrecord = $db->getSingleRow(
					"SELECT * FROM ".LEPTON_DB_PREFIX."users WHERE username=%s", $username
				);
				if ($userrecord) {

					// What hashing algorithm to use
					$ha = config::get('lepton.user.hashalgorithm','md5');

					// Grab the salt, concatenate the password and the salt,
					// and hash it with the selected hashing algorithm.
					$us = $userrecord['passwordsalt'];
					$ps = $password.$us;
					$hp = hash($ha,$ps);

					// Check the hash against the one on file
					if ($hp == $userrecord['password']) {
						$this->userid = $userrecord['id'];
						return true;
					}
					return false;

				} 
			} catch(Exception $e) {
				throw $e;
			}
			
		}
		
		function getUserId() {
			return $this->userid;
		}
		
		/**
		 * @brief Query and return a user record.
		 * This can be used by the authentication providers in a number of ways
		 * such as resolving user IDs or using information to look in other
		 * tables.
		 *
		 * @param string $username The username to fetch the record from
		 * @return array The user record, or null on failure.
		 */
		function queryUser($username) {
		
			$db = new DatabaseConnection();
			try {
				$userrecord = $db->getSingleRow(
					"SELECT * FROM users WHERE username=%s", $username
				);
				if ($userrecord) {
					return $userrecord;
				} 
				return null;
			} catch(Exception $e) {
				throw $e;
			}
			
		}
		
		function generateSalt() {

			$start = md5(microtime(true)*1000);
			$salt = substr($start,4,16); // Grab 16 bytes from middle
			return $salt;
		
		}
		
	}
	

