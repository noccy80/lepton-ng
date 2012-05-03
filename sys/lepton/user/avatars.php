<?php

using('lepton.user.extensions');
config::def('lepton.avatar.providers', array(
    'LocalAvatarProvider',
    'GravatarAvatarProvider'
));
config::push('lepton.user.extensions', 'AvatarProvider');

////////// AvatarProviders ////////////////////////////////////////////////

/**
 * AvatarProvider interface and base class. Derive your avatar mnaagement functions
 * from the AvatarProvider class. The first class in the chain to return a non-null
 * string will be used as the avatar source.
 */

class AvatarProvider extends UserExtension { 
    function getMethods() {
        return array('getAvatar','setAvatar');
    }
    function getAvatar($size=null) {
        $prov = config('lepton.avatar.providers');
        foreach($prov as $provider) {
            $prov = new $provider();
            $avatar = $prov->getAvatar($this->user,$size);
            if ($avatar) break;
        }
        return $avatar;
    }
    function setAvatar($avatar) {
        return true;
    }
}

abstract class AvatarProviderBase {
    abstract function getAvatar(UserRecord $user, $size=null);
    abstract function setAvatar(UserRecord $user, $avatar);
}

class LocalAvatarProvider extends AvatarProviderBase{
    function getAvatar(UserRecord $user, $size=null) {
        return false;
    }
    function setAvatar(UserRecord $user, $avatar) {
        return true;
    }
}

class GravatarAvatarProvider extends AvatarProviderBase{
    function getAvatar(UserRecord $user,$size=null) {
        $default = config::get('lepton.avatars.gravatars.default','identicon');
        if (!$size) $size = config::get('lepton.avatars.defaultsize', 64);
        $email = $user->email;
        return(
            (request::isSecure()?'https://':'http://').
            "www.gravatar.com/avatar.php?" .
            "gravatar_id=".md5( strtolower($email) ) .
            "&default=".urlencode($default) .
            "&size=".$size
        );
    }
    function setAvatar(UserRecord $user, $avatar) {
        return false;
    }
}

class Gravatar {
    function get($email,$size=null) {
        $default = config::get('lepton.avatars.gravatars.default','identicon');
        if (!$size) $size = config::get('lepton.avatars.defaultsize', 64);
        return(
            (request::isSecure()?'https://':'http://').
            "www.gravatar.com/avatar.php?" .
            "gravatar_id=".md5( strtolower($email) ) .
            "&default=".urlencode($default) .
            "&size=".$size
        );
    }
}

