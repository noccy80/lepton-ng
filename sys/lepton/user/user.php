<?php module("Authentication Provider Base Classes");

abstract class UserEvents {
    const EVENT_USER_REGISTER = 'lepton.user.authentication.register';
    const EVENT_USER_LOGIN = 'lepton.user.authentication.login';
    const EVENT_USER_LOGOUT = 'lepton.user.authentication.logout';
}

/**
 * @class User
 * @example authentication.php
 *
 * Handle authentication and user management.
 */
abstract class User {

    const KEY_USER_AUTH = 'lepton.user.identity';
    const KEY_USER_SUID = 'lepton.user.suid';

    /**
     * @brief Check if the user has a specific flag set
     * 
     * Shorthand version of user->hasFlag()
     * 
     * @param String $flag The flag to look for
     * @return Bool True if the user has the flag set
     */
    static function hasFlag($flag) {
        if (user::isAuthenticated()) {
            return (user::getActiveUser()->hasFlag($flag));
        }
        return null;
    }

    /**
     * Attempt to authenticate the user through a provider.
     *
     * @param AuthenticationProvider $authrequest The authetication request
     * @return Bool True on success
     */
    static function authenticate($authrequest) {

        // Resolve the authentication backend
        $auth_class = User::getAuthenticationBackend();
        // Assign the authentication backend to the request
        $authrequest->setAuthBackend($auth_class);

        if ($authrequest->isTokenValid()) {
            $authrequest->login();
            return true;
        }
        return false;

    }

    /**
     * @brief Suid to another user.
     * 
     * The aim of this function is to allow the user to become another user,
     * for testing or administrative purposes. It is similar to the sudo and
     * su commands in the *nix world.
     * 
     * A user can only suid ONCE, and is only allowed to switch back to the
     * previous (original) user by passing NULL as the user record.
     * 
     * @todo Thorougly test
     * @param UserRecord $user The user to suid to or null to revert
     * @return boolean True on success
     */
    static function suid(UserRecord $user = null) {

        $suid = (array)session::get(User::KEY_USER_SUID,null);
        if (arr::hasKey($suid,'issuid') && ($user == null)) {
            // Can unsuid
            $uid = $suid['uid'];
            session::set(User::KEY_USER_AUTH, $uid);
            session::clr(User::KEY_USER_SUID);
            // user::set
            return true;
        } elseif ($user) {
            // Can suid
            session::set(User::KEY_USER_SUID, array(
                'issuid' => true,
                'uid' => user::getActiveUser()->userid,
                'suid' => $user->userid
            ));
            session::set(User::KEY_USER_AUTH, $user->userid);
            return true;
        } else {
            throw new SecurityException("Invalid suid attempt");
        }
        
    }

    /**
     * @brief Remove a user based on username or UserRecord.
     * 
     * This action can not be reverted, so make sure that you really want
     * to remove the username before actually doing this.
     * 
     * @param String|UserRecord $username The user to remove
     * @return Boolean True if the operation was successful 
     */
    static function remove($username) {
        $db = new DatabaseConnection();
        if (is_a($username,'UserRecord')) {
            $user = $db->getSingleRow("SELECT * FROM users WHERE id=%d", $username->userid);
        } else {
            $user = $db->getSingleRow("SELECT * FROM users WHERE username=%s", $username);
        }
        if ($user) {
            $uid = $user['id'];
            $db->updateRow("DELETE FROM users WHERE id=%d", $uid);
            $db->updateRow("DELETE FROM userdata WHERE id=%d", $uid);
            $db->updateRow("DELETE FROM userengage WHERE id=%d", $uid);
            $db->updateRow("DELETE FROM userppp WHERE id=%d", $uid);
            return true;
        }
        return false;
    }

    /**
     * Return the authentication backend
     *
     * @return AuthenticationBackend The backend instance
     */
    static function getAuthenticationBackend() {

        // Resolve the authentication backend
        $auth_backend = config::get('lepton.user.authbackend','default');
        if (strpos(strtolower($auth_backend),'authbackend') === false)
            $auth_backend.= 'AuthBackend';
        logger::debug('Creating auth backend instance %s', $auth_backend);
        $auth_class = new $auth_backend();
        return $auth_class;

    }

    /**
     * Log out the currently active user
     *
     * @TODO Implement logout.
     */
    static function logout() {

        session::set(User::KEY_USER_AUTH, null);

    }

    /**
     * Create a user record and set up the authentication credentials.
     *
     * @param UserRecord $user The user record to create.
     * @return integer The user ID on success
     */
    static function create(UserRecord $user) {

        if ($user->username) {
            $user->save();
            return $user->userid;
        } else {
            throw new UserException("New user need to have a username set!");
        }

    }

    /**
     * Check if a user is authenticated.
     *
     * @return Bool True if the user is authenticated.
     */
    static function isAuthenticated() {

        if (ModuleManager::has('lepton.mvc.session')) {
            if (session::get(User::KEY_USER_AUTH,null) != null) {
                return true;
            }
            return false;
        }

    }

    /**
     * Find a user by username.
     *
     * @param string $username The username to search for
     * @return UserRecord The matching user record or null if none
     */
    static function find($username) {

        $db = new DatabaseConnection();
        $record = $db->getSingleRow(
            "SELECT a.*,u.*,a.id AS userid FROM users a LEFT JOIN userdata u ON a.id=u.id WHERE a.username=%s",
            $username
        );
        if ($record) {
            $u = new UserRecord();
            $u->assign($record);
            return $u;
        }
        return null;

    }

    /**
     * @brief Return the user record of the active user
     * 
     * Returns null if no user is authenticated
     * 
     * @todo Gracefully handle situations where the session is not available
     * @return UserRecord The UserRecord of the active user
     */
    static function getActiveUser() {
        if (ModuleManager::has('lepton.mvc.session')) {
            $uid = (session::get(User::KEY_USER_AUTH,null));
            if ($uid) {
                return user::getUser($uid);
            }
            throw new UserException("No active user", UserException::ERR_NO_ACTIVE_USER);
        }
    }

    /**
     * Find a user by userid.
     *
     * @param int $userid The userid to look up
     * @return UserRecord The matching user record or null if none
     */
    static function getUser($userid) {

        $db = new DatabaseConnection();
        $record = $db->getSingleRow(
            "SELECT a.*,u.*,a.id AS userid FROM users a LEFT JOIN userdata u ON a.id=u.id WHERE a.id=%d",
            $userid
        );
        if ($record) {
            $u = new UserRecord();
            $u->assign($record);
            return $u;
        }
        return null;

    }

    /**
     * @brief Checks to see if a username is available.
     * Keep in mind that if the username is available, true is returned.
     * This is contrary to the find() methods.
     *
     * @see User::find
     * @param string $username The username to check
     * @return bool True if the username is valid, false otherwise
     */
    static function checkUsername($username) {

        $db = new DatabaseConnection();
        $record = $db->getSingleRow(
            "SELECT * FROM users WHERE username=%s",
            $username
        );
        return (($record) == null);

    }

    /**
     * Find a user in the database. Deprecated in favor of User::find()
     *
     * @deprecated since 1.0.0
     * @param string $username
     * @return UserRecord The user record if any. Null otherwise.
     */
    static function findUser($username) {
        __deprecated('user::findUser', 'user::find');
        return user::find($username);
    }

}

