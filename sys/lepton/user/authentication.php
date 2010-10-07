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
        function logout();
    }

    abstract class AuthenticationProvider implements IAuthenticationProvider {

        protected $auth_backend = null;

        public function setAuthBackend($backend) {

            $this->auth_backend = $backend;

        }

        protected function setUser($id) {
            // TODO: Assign to session
            if (ModuleManager::has('lepton.mvc.session')) {
                if (session::set('lepton_uid',$id));
            }
        }

        protected function clearUser() {

        }

    }

    abstract class User {

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
        
        static function create(UserRecord $user) {

            // Resolve the authentication backend
            $auth_backend = config::get('lepton.user.authbackend','defaultauthbackend');
            $auth_class = new $auth_backend();
            if ($auth_class->assignCredentials($user)) {
	            $user->save();
	        }
        
        }
        
        static function isAuthenticated() {
        
        	if (ModuleManager::has('lepton.mvc.session')) {
        		if (session::get('lepton_uid',null) != null) {
        			return true;
        		}
        		return false;
        	}
        
        }
        
        static function findUser($username) {
        
        }

    }

    ModuleManager::load('lepton.user.backends.*');
    ModuleManager::load('lepton.user.providers.*');

