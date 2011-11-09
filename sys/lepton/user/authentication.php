<?php module("Authentication Provider Base Classes");

/**
 * @brief Exceptions related to user operations
 */
class UserException extends BaseException { 
    const ERR_NO_ACTIVE_USER = 1;
    const ERR_USER_UNASSOCIATED = 2;
    const ERR_USER_INACTIVE = 3;
}

/**
 * @brief Authentication related exceptions
 */
class AuthenticationException extends UserException { }

/**
 * @brief Interface for authentication backend.
 * 
 * Note that classes does not have to directly implement this interface, but
 * rather implement it indirect by extending the AuthentationBackend abstract
 * base class.
 */
interface IAuthenticationBackend {
    function validateCredentials($username,$password);    
}

/**
 * @brief Abstract base class for authentication backend.
 */
abstract class AuthenticationBackend implements IAuthenticationBackend {

}

/**
 * @brief Interface for authentication providers. 
 * 
 * This interface should not be directly implemented, but rather be used by
 * extending the AuthenticationProvider abstract base class.
 */
interface IAuthenticationProvider {
    function isTokenValid(); /// Returns true if the tokens match
    function login();
}

/**
 * @brief Abstract base class for authentication providers.
 * 
 * The authentication provider should handle the login() request as well as
 * provide the isTokenValid() method to indicate the success. Upon calling
 * the login() method, the backend should call on the protected setUser()
 * method to activate the user. A call to clearUser() should be done if the
 * authentication fails for any reason.
 */
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
        if ($u == null) throw new UserException("Unassociated user id / Integrity failure", UserException::ERR_USER_UNASSOCIATED);
        if (!$u->active) throw new UserException("User is not active, check audit log", UserException::ERR_USER_INACTIVE);
        // TODO: Assign to session
        if (ModuleManager::has('lepton.mvc.session')) {
            session::set(User::KEY_USER_AUTH,$id);
        }
        if (class_exists('request')) {
            $db = new DatabaseConnection();
            $db->updateRow("UPDATE users SET lastlogin=NOW(), lastip=%s WHERE id=%d", request::getRemoteIp(), $id);
        }
        if (class_exists('UserEvents')) {
            event::invoke(UserEvents::EVENT_USER_LOGIN, array(
                'id' => $id
            ));
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

using('lepton.user.user');
using('lepton.user.userrecord');
using('lepton.user.backends.*');
using('lepton.user.providers.*');

