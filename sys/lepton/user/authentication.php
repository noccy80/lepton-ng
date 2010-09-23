<?php

    class AuthenticationException extends BaseException { }


    interface IAuthenticationBackend {
        function testUserPassword($username,$password);    
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
            }
            
        }

    }

    ModuleManager::load('lepton.user.backends.*');
    ModuleManager::load('lepton.user.providers.*');

