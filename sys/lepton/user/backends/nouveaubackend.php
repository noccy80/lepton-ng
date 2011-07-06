<?php

__fileinfo("Nouveau Authentication Backend (DB)");

using('lepton.crypto.uuid');
using('lepton.user.authentication');
using('lepton.crypto.hash');

/**
 * @file nouveauauthbackend.php
 * @class NouveauAuthBackend
 * @package lepton.user.backends.nouveauauthbackend
 *
 * The new authentication backend, with flexible salting and hashing supporting
 * embedded salts in password string. Performs authentication against the main
 * lepton database.
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @copyright (c) 2010, NoccyLabs.info
 * @license GNU GPL v3
 */
class NouveauAuthBackend extends AuthenticationBackend {

    const KEY_HASH_ALGORITHMS = 'lepton.user.hashing.algorithms';
    const KEY_SALTLEN = 'lepton.user.hashing.saltlen';
    const DEF_SALTLEN = 16;
    
    private $userid;

	/**
	 * @brief Separates a password hash into its components
	 *
	 *
	 * @param String $hstr Hash string
	 * @return Array The components as algo,rounds, salt and hash
	 */
	private function getHashComponents($hstr) {
	
		// Explode string as: $<algo>:<rounds>$<salt>$<hash>
		if ($hstr[0] == '$') {
			$rv = explode('$', $hstr);
			if (count($rv) == 5) {
				list($algo,$rounds) = explode(':', $rv[1]);
				return array($algo,$rounds,$rv[2],$rv[3]);
			}
			throw new SecurityException("Salted hash in incompatible format!");
		} else {
			throw new SecurityException("Legacy hash detected");
		}
	
	}

    /**
     * @brief Return the best hashing algorithm that is supported
     * 
     * 
     * @return type 
     */
	private function hashGetBestAlgorithm() {
	
		// Go over the algorithms available and pick the best one from the
		// list.
		$algos = config(self::KEY_HASH_ALGORITHMS);
		foreach($algos as $algo) {
			try {
				$h = new Hash($algo);
				return $algo;
			} catch (Exception $e) { 
				// Unsupported hash
			}
		}
		throw new SecurityException("No supported hashes found!");
	
	}
	
	public function hashPassword($password) {

		$algo = $this->hashGetBestAlgorithm();
		$salt = $this->generateSalt();
		$ho = new Hash($algo);
		
		$hash = $salt.$password;
		$rounds = config::get('lepton.user.hashing.rounds', 4);
		
		for ($n = 0; $n < $rounds; $n++) {
			$hash = $ho->hash($hash);
		}
		
		// Assemble hash block
		$hb = '$'.$algo.':'.$rounds.'$'.$salt.'$'.$hash.'$';
		
		// Return it
		return $hb;

	}

    /**
     * @brief Test a password for a username.
     * Returns true if the password is a valid authentication token for
     * the specified user.
     *
     * @param string $username The username to match against
     * @param string $password The password to match with
     * @return bool True on success.
     */
    public function validateCredentials($username, $password, $ext=false) {
        $db = new DatabaseConnection();
        try {
            $userrecord = $db->getSingleRow(
                "SELECT * FROM " . LEPTON_DB_PREFIX . "users WHERE username=%s", $username
            );
            if ($userrecord) {
				try {
	            	list($ha,$rounds,$us,$hash) = $this->getHashComponents($userrecord['password']);
				    $ps = $us . $password;
	            	logger::debug("Hash algorithm: %s (salt=%s)", $ha, $us);
	            } catch (SecurityException $e) {
		            // Fall back on MD5 or defined algorithm
		            $ha = config::get('lepton.user.hashalgorithm', 'md5');
				    // Grab the salt, concatenate the password and the salt,
				    // and hash it with the selected hashing algorithm.
				    $us = $userrecord['salt'];
				    $hash = $userrecord['password'];
				    $ps = $password . $us;
				    $rounds = 1;
	            	logger::debug("Hash algorithm: %s (salt=%s) [VIA FALLBACK!]", $ha, $us);
	            }
				$oha = new Hash($ha);
				
				// Iterate specified number of rounds
				for ($n = 0; $n < $rounds; $n++) {
					$ps = $oha->hash($ps);
				}
			    $hp = $ps;

		        // Check the hash against the one on file
		        if ($hp == $hash) {
		            $this->userid = $userrecord['id'];
		            return true;
		        }
		        return false;
		    }
	    } catch (Exception $e) {
		    throw $e; // TODO: Handle exception
		}
	}

    /**
     * Update the sign-in credentials for the specific user.
     *
     * @param UserRecord $user The user to update the credentials for
     * @return Boolean True on success
     */
    public function assignCredentials(UserRecord $user) {

        $db = new DatabaseConnection();

		$hp = $this->hashPassword($user->password);
		logger::debug("Updating password has for %s with '%s'", $user->username, $hp);

        if ($user->userid == null) {
            $uuid = UUID::v4();
            try {
                $id = $db->insertRow(
                    "REPLACE INTO " . LEPTON_DB_PREFIX . "users (username,password,email,flags,registered,uuid) VALUES (%s,%s,%s,%s,%s,NOW(),%s)",
                    $user->username, $hp, $user->email, $user->flags, $uuid
                );
                $user->userid = $id;
            } catch (Exception $e) {
                throw $e; // TODO: Handle exception
            }
        } else {
            try {
                $db->updateRow(
                    "UPDATE " . LEPTON_DB_PREFIX . "users SET username=%s,password=%s,email=%s,flags=%s WHERE id=%d",
                    $user->username, $hp, $user->email, $user->flags, $user->userid
                );
            } catch (Exception $e) {
                throw $e; // TODO: Handle exception
            }
        }

        return true;
    }

    /**
     *
     * @return int The user id or null.
     */
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
    public function queryUser($username) {

        $db = new DatabaseConnection();
        try {
            $userrecord = $db->getSingleRow(
                "SELECT * FROM users WHERE username=%s", $username
            );
            if ($userrecord) {
                return $userrecord;
            }
            return null;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @brief Generate salt for the password hashing.
     * 
     * This method currently uses a sha1 hash of the current time for generating
     * the random string to use for salting.
     * 
     * @return String The generated salt
     */
    function generateSalt() {

		$len = config::get(self::KEY_SALTLEN, self::DEF_SALTLEN);
        while(!isset($raw) || strlen($raw)<$len)
            $raw = sha1(uniqid(microtime(true) * 1000));

        $salt = substr($raw, strlen($raw)-$len, $len); // Grab requested number of bytes
        logger::debug("Generated salt '%s' -> used as '%s'", $raw, $salt);
        return $salt;
    }

}

