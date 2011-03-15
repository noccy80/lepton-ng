<?php

__fileinfo("Default Authentication Backend (DB)");

using('lepton.crypto.uuid');
using('lepton.user.authentication');

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
    public function validateCredentials($username, $password, $ext=false) {
        $db = new DatabaseConnection();
        try {
            $userrecord = $db->getSingleRow(
                "SELECT * FROM " . LEPTON_DB_PREFIX . "users WHERE username=%s", $username
            );
            if ($userrecord) {

                // What hashing algorithm to use
                $ha = config::get('lepton.user.hashalgorithm', 'md5');

                // Grab the salt, concatenate the password and the salt,
                // and hash it with the selected hashing algorithm.
                $us = $userrecord['salt'];
                $ps = $password . $us;
                $hp = hash($ha, $ps);

                // Check the hash against the one on file
                if ($hp == $userrecord['password']) {
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

        // Generate a new salt and hash the password
        $salt = $this->generateSalt();
        // What hashing algorithm to use
        $ha = config::get('lepton.user.hashalgorithm', 'md5');
        $ps = $user->password . $salt;
        $hp = hash($ha, $ps);

        if ($user->userid == null) {
            $uuid = UUID::v4();
            try {
                $id = $db->insertRow(
                    "REPLACE INTO " . LEPTON_DB_PREFIX . "users (username,salt,password,email,flags,registered,uuid) VALUES (%s,%s,%s,%s,%s,NOW(),%s)",
                    $user->username, $salt, $hp, $user->email, $user->flags, $uuid
                );
                $user->userid = $id;
            } catch (Exception $e) {
                throw $e; // TODO: Handle exception
            }
        } else {
            try {
                $db->updateRow(
                    "UPDATE " . LEPTON_DB_PREFIX . "users SET username=%s,salt=%s,password=%s,email=%s,flags=%s WHERE id=%d",
                    $user->username, $salt, $hp, $user->email, $user->flags, $user->userid
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

    function generateSalt() {

        $start = md5(microtime(true) * 1000);
        $salt = substr($start, 4, 16); // Grab 16 bytes from middle
        return $salt;
    }

}

