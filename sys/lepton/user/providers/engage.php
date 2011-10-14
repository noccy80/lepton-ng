<?php

using('lepton.user.authentication');
using('lepton.net.httprequest');

class EngageException extends UserException { }
/**
 * EngageEvents::EVENT_SUCCESSFUL_CALLBACK
 * Called on a successful callback
 *   profile DOMDocument The DOM Document holding the parsed profile
 *   profiletext string The profile in XML format
 *
 * EngageEvents::EVENT_IDENTITY_CREATED
 * Called when an identity is created
 * 
 */
class EngageEvents {
	const EVENT_SUCCESSFUL_CALLBACK = 'lepton.user.engage.successfulcallback';
    const EVENT_IDENTITY_CREATED = 'lepton.user.engage.identitycreated';
    const EVENT_IDENTITY_ADDED = 'lepton.user.engage.identityadded';
}

/**
 * @brief JanRain Engage Authentication Provider
 *
 * Handles authentication against the JanRain Engage service (formely known as
 * RPX)
 */
class EngageAuthentication extends AuthenticationProvider {

	const KEY_APIKEY = 'lepton.user.engage.apikey';
	const KEY_DEFAULT_FLAGS = 'lepton.user.engage.defaultflags';
    const KEY_TOKEN_URL = 'lepton.user.engage.tokenurl';
    const KEY_APP_NAME = 'lepton.user.engage.appname';
	const KEY_ALLOW_CREATION = 'lepton.user.engage.allowcreation';
	const DEFAULT_FLAGS = 'e';
	const DEFAULT_ALLOW_CREATION = true;
    const SESSIONKEY_USER_CREATED = 'lepton.user.engage.usercreated';

	private $userid = null;

	/**
	 * @brief Constructor for Password Authentication
	 *
	 * @param string $username The username for which to validate the token
	 * @param string $password The user's password.
	 */
	public function __construct() {
		$token = request::get('token')->toString();
		$apikey = config::get('lepton.user.engage.apikey');

		$ret = new HttpRequest('https://rpxnow.com/api/v2/auth_info', array(
					'method' => 'post',
					'parameters' => array(
						'apiKey' => $apikey,
						'token' => $token,
						'format' => 'xml'
					)
				));

		$dom = DOMDocument::loadXml($ret->responseText());
		$domx = new DOMXPath($dom);

		// Get the status
		$status = $domx->query('/rsp')->item(0)->getAttribute('stat');
		if ($status == 'ok') {
			event::invoke(EngageEvents::EVENT_SUCCESSFUL_CALLBACK, array(
				'profile' => $dom,
				'profiletext' => $ret->responseText()
			));
			$identifier = $domx->query('/rsp/profile/identifier')->item(0)->nodeValue;
			$displayname = $domx->query('/rsp/profile/displayName')->item(0)->nodeValue;
			$provider = $domx->query('/rsp/profile/providerName')->item(0)->nodeValue;
			$firstname = $domx->query('/rsp/profile/name/givenName')->item(0)->nodeValue;
			$lastname = $domx->query('/rsp/profile/name/familyName')->item(0)->nodeValue;
			$preferredusername = $domx->query('/rsp/profile/preferredUsername')->item(0)->nodeValue;
			$email = $domx->query('/rsp/profile/email')->item(0)->nodeValue;

			// Sign in
			$db = new DatabaseConnection();
			$idrs = $db->getSingleRow("SELECT * FROM userengage WHERE identifier=%s", $identifier);

			if ($idrs) {
				$cu = $idrs['userid'];
				$db->updateRow("UPDATE userengage SET lastseen=NOW(), lastip=%s WHERE id=%d", request::getRemoteIp(), $idrs['id']);
			} else {
				if (!user::isAuthenticated()) {
					if (!config::get(EngageAuthentication::KEY_ALLOW_CREATION, false)) {
						throw new SecurityException("User creation is disabled for EngageAuthentication");
					}
					// Check username, add random numbers if not available
					$username = $preferredusername;
					$retrycount = 0;
					while (!(user::checkUsername($username))) {
						$username = substr($preferredusername, 0, 6) . rand(1000, 9999);
						$retrycount = $retrycount + 1;
						if ($retrycount > 10) {
							throw new UserException("Bad username");
						}
					}
					// Generate a new password
					$password = substr(md5(uniqid()), 0, 6);
					// And create the userrecord
					$u = new UserRecord();
					$u->username = $username;
					$u->password = $password;
					$u->flags = config::get(EngageAuthentication::KEY_DEFAULT_FLAGS,EngageAuthentication::DEFAULT_FLAGS);
					$u->displayname = $displayname;
					$u->firstname = $firstname;
					$u->lastname = $lastname;
					$u->email = $email;
					$cu = user::create($u);
                    session::set(self::SESSIONKEY_USER_CREATED,true);
				} else {
					$cu = user::getActiveUser();
				}
				// Add identifier to user
				$db->updateRow("INSERT INTO userengage (userid,identifier,provider,lastseen,lastip) VALUES (%d,%s,%s,NOW(),%s)", $cu, $identifier, $provider, request::getRemoteIp());
			}
			$this->userid = $cu;
		} else {
			$this->userid = null;
		}

    }

	/**
	 * @brief Check if the token used for authentication is valid
	 *
	 * @return boolean True on success, false otherwise.
	 */
	public function isTokenValid() {
		return ($this->userid !== null);
	}

	/**
     * @brief Authenticate the specified user.
     *
     * @return boolean True on success
     */
    function login() {
        if ($this->userid) {
            $this->setUser($this->userid);
            // console::writeLn("Authenticated as user %d", $this->userid);
            return true;
        }
        throw new AuthenticationException("No user available to login()");
    }

	static function unlinkIdentity($iid,$userid=null) {

		$db = new DatabaseConnection();
		
		// Default to the active user
		if (!$userid) $userid = user::getActiveUser()->userid;

		// And make sure we have an identity to unlink
		if ($iid != 0) {
			$identities = $db->getRows("SELECT * FROM userengage WHERE userid=%d", user::getActiveUser()->userid);
			$identity = $db->getSingleRow("SELECT * FROM userengage WHERE userid=%d AND id=%d", user::getActiveUser()->userid, $iid);
			if (count($identities) > 1) {
				if ($identity) {
					$db->updateRow("DELETE FROM userengage WHERE userid=%d AND id=%d", user::getActiveUser()->userid, $iid);
					response::redirect('/control/panel');
				}
			} else {
				view::set('identity', $identity);
				view::load('control/unlink_error.php');
				return;
			}
		} else {
			response::redirect('/control/panel');
		}
	}
    
    static function header() {
        $tokenurl = config::get(self::KEY_TOKEN_URL);
        $appname = config::get(self::KEY_APP_NAME);
        printf("<script type=\"text/javascript\">");
        printf("(function() {");
        printf("    if (typeof window.janrain !== 'object') window.janrain = {};");
        printf("    window.janrain.settings = {};");
        printf("    janrain.settings.tokenUrl = '%s';", $tokenurl);
        printf("    function isReady() { janrain.ready = true; };");
        printf("    if (document.addEventListener) {");
        printf("        document.addEventListener(\"DOMContentLoaded\", isReady, false);");
        printf("    } else {");
        printf("        window.attachEvent('onload', isReady);");
        printf("    }");
        printf("    var e = document.createElement('script');");
        printf("    e.type = 'text/javascript';");
        printf("    e.id = 'janrainAuthWidget';");
        printf("    if (document.location.protocol === 'https:') {");
        printf("        e.src = 'https://rpxnow.com/js/lib/%s/engage.js';",$appname);
        printf("    } else {");
        printf("        e.src = 'http://widget-cdn.rpxnow.com/js/lib/%s/engage.js';",$appname);
        printf("    }");
        printf("    var s = document.getElementsByTagName('script')[0];");
        printf("    s.parentNode.insertBefore(e, s);");
        printf("})();");
        printf("</script>");
        
    }

}