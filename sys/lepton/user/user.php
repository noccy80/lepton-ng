<?php module("Authentication Provider Base Classes");

    /**
     * @class User
     * @example authentication.php
     *
     * Handle authentication and user management.
     */
    abstract class User {

		const KEY_USER_AUTH = 'lepton.user.identity';
		const ERR_USER_UNASSOCIATED = 2;
        const ERR_USER_INACTIVE = 1;

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
         * @brief Check if the user has a specific flag set
         * 
         * Shorthand version of user->hasFlag()
         * 
         * @param String $flag The flag to look for
         * @return Bool True if the user has the flag set
         */
        static function hasFlag($flag) {
            $u = user::getActiveUser();
            return $u->hasFlag($flag);
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
				"SELECT a.*,u.*,a.id AS userid FROM ".LEPTON_DB_PREFIX."users a LEFT JOIN ".LEPTON_DB_PREFIX."userdata u ON a.id=u.id WHERE a.username=%s",
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
                return null;
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
				"SELECT a.*,u.*,a.id AS userid FROM ".LEPTON_DB_PREFIX."users a LEFT JOIN ".LEPTON_DB_PREFIX."userdata u ON a.id=u.id WHERE a.id=%d",
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
				"SELECT * FROM ".LEPTON_DB_PREFIX."users WHERE username=%s",
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

