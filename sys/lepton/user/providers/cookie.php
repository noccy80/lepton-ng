<?php module("Cookie Authentication Provider", array(
    'version' => '1.0'
));

using('lepton.user.authentication');

/**
 * @class CookieAuthentication
 * @package lepton.user.providers.cookie
 * @brief Authentication with cookies, implementing "remember me" functionality.
 * 
 * 
 * It does so by storing a cookie with the user ID and and a random nonce
 * value as a cookie in the client's browser and this cookie is then matched
 * against the pointed to user record.
 *
 * Please bear in mind that cookies may be covered by legal restrictions in
 * some countries or territories. Therefore always inform the user about the
 * cookies being stored and the purpose thereof.
 */
final class CookieAuthentication extends AuthenticationProvider {

    const DEF_EXPIRY = -1; // 1 week
    const KEY_COOKIE_NAME = 'lepton.cookieauth.cookiename';
    const KEY_COOKIE_EXPIRY = 'lepton.cookieauth.defaultduration';
    const KEY_COOKIE_DOMAIN = 'lepton.cookieauth.domain';

    public function __construct($setcookie = false) {
        if ($setcookie == true) {
            $this->setCookie();
        }
    }

    public function authenticate() {
        // TODO: Look up the cookie in the database
        $name = config::get(CookieAuthentication::KEY_COOKIE_NAME,'leptonauth');
        $ca = Cookies::getInstance(Cookies);
        if ($ca->has($name)) {
            $kd = $ca->get($name);
            $c = $this->decryptCookie($kd);
            $db = DBX::getInstance(DBX);
            $r = $db->getSingleRow("SELECT id FROM users WHERE id='%d' AND username='%s'", $c['uid'], $c['uname']);
            if ($r) {
                User::setActiveUser($r['id']);
                return true;
            }
        }
    }
    
    public function isTokenValid() {
        return false;
    }
    
    public function login() {
        return false;
    }

    public function setCookie($expiry = CookieAuthentication::DEF_EXPIRY) {
        // Set the cookie for autoauth for current user
        $ca = Cookies::getInstance(Cookies);
        if (User::isAuthenticated()) {
            $u = User::getActiveUser();
            $name = config::get(CookieAuthentication::KEY_COOKIE_NAME,'leptonauth');
            $c = $this->encryptCookie(array(
                'uid' => $u->userid,
                'uname' => $u->username
            ));
            if ($expiry == -1) {
                $expiry = config::get(CookieAuthentication::KEY_COOKIE_EXPIRY,'7d');
            }
            $ca->set($name,
                $c,
                array(
                    'expires' => $expiry,
                    'domain' => config::get(CookieAuthentication::KEY_COOKIE_DOMAIN, null),
                    'path' => '/'
                )
            );
        }
    }

    public function deleteCookie() {
        $ca = Cookies::getInstance(Cookies);
        $name = config::get(CookieAuthentication::KEY_COOKIE_NAME,'leptonauth');
        $ca->clr($name, array(
            'domain' => config::get(CookieAuthentication::KEY_COOKIE_DOMAIN, null),
            'path' => '/'
        ));
    }

    private function encryptCookie($data) {
        $key = config::get('lepton.cookieauth.key');
        $c = new CryptoCipher('3DES', $key);
        $plain = serialize($data);
        return $c->encrypt($plain, true);
    }

    private function decryptCookie($data) {
        $key = config::get('lepton.cookieauth.key');
        $c = new CryptoCipher('3DES', $key);
        $plain = $c->decrypt($data, true);
        return unserialize($plain);
    }

}
