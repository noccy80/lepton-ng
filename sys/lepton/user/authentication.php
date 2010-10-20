<?php

    class UserException extends BaseException { }
        class AuthenticationException extends UserException { }

    interface IAuthenticationBackend {
        function validateCredentials($username,$password);    
    }
    
    abstract class AuthenticationBackend implements IAuthenticationBackend {
    
    }

    interface IAuthenticationProvider {
        function isTokenValid(); /// Returns true if the tokens match
        function login();
    }

    abstract class AuthenticationProvider implements IAuthenticationProvider {

        protected $auth_backend = null;

		/**
		 * Sets the authentication backend to use.
		 *
		 * @param IAuthenticationBackend $backend The backend to authenticate with
		 */
        public function setAuthBackend(IAuthenticationBackend $backend) {

            $this->auth_backend = $backend;

        }

		/**
		 * @brief Assign a user to the current session.
		 *
		 * @param int $id The user id to assign
		 */
        protected function setUser($id) {
            // TODO: Assign to session
            if (ModuleManager::has('lepton.mvc.session')) {
                if (session::set(User::KEY_USER_AUTH,$id));
            }
        }

		/**
		 * @brief Clear the active user.
		 *
		 */
        protected function clearUser() {
			User::clearUser();
        }

    }

    /**
     * @class User
     * @example authentication.php
     *
     * Handle authentication and user management.
     */
    abstract class User {

		const KEY_USER_AUTH = 'lepton.user.identity';

        /**
         * Attempt to authenticate the user through a provider.
         *
         * @param AuthenticationProvider $authrequest The authetication request
         * @return Bool True on success
         */
        static function authenticate($authrequest) {
        
            // Resolve the authentication backend
            $auth_backend = config::get('lepton.user.authbackend','defaultauthbackend');
            $auth_class = new $auth_backend();
            // Assign the authentication backend to the request
            $authrequest->setAuthBackend($auth_class);

            if ($authrequest->isTokenValid()) {
				$authrequest->login();
                return true;
            }
            
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
         */
        static function create(UserRecord $user) {

            // Resolve the authentication backend
            $auth_backend = config::get('lepton.user.authbackend','defaultauthbackend');
            $auth_class = new $auth_backend();
            if ($auth_class->assignCredentials($user)) {
                $user->save();
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
				"SELECT a.*,u.* FROM ".LEPTON_DB_PREFIX."users a LEFT JOIN ".LEPTON_DB_PREFIX."userdata u ON a.id=u.id WHERE a.username=%s",
				$username
			);
			return $record;

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
			return ($record != null);

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

    ModuleManager::load('lepton.user.backends.*');
    ModuleManager::load('lepton.user.providers.*');

