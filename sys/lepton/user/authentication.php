<?php __fileinfo("Authentication Provider Base Classes");



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
		 * @param $id The user id to assign
		 */
        protected function setUser($id) {
            // Check if the user is active
            $u = user::getUser($id);
			if ($u == null) throw new UserException("Unassociated user id / Integrity failure", user::ERR_USER_UNASSOCIATED);
            if (!$u->active) throw new UserException("User is not active, check audit log", user::ERR_USER_INACTIVE);
            // TODO: Assign to session
            if (ModuleManager::has('lepton.mvc.session')) {
                session::set(User::KEY_USER_AUTH,$id);
            }
            $db = new DatabaseConnection();
            $db->updateRow("UPDATE users SET lastlogin=NOW(), lastip=%s WHERE id=%d", request::getRemoteIp(), $id);
        }


        /**
         * @brief Clear the active user.
         *
         */
        protected function clearUser() {
            User::clearUser();
        }

    }

    ModuleManager::load('lepton.user.user');
    ModuleManager::load('lepton.user.userrecord');
    ModuleManager::load('lepton.user.backends.*');
    ModuleManager::load('lepton.user.providers.*');

