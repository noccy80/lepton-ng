<?php

interface IAclObject {
    function getObjectUuid();
}
abstract class AclObject {

}

/**
 *
 *
 *
 *
 */
class Acl {

    const ACCESS_DENY = -1; ///< Deny access to the object
    const ACCESS_ALLOW = 1; ///< Allow access to the object
    const ACCESS_NULL = 0; ///< No Acl entry, or clear existing entry.

    /**
     *
     * @param mixed $objectid One or more object IDs as string or array.
     * @return AclList The access control list for the object.
     */
    function getAclForObject($objectid) {

    }

    /**
     *
     * @param mixed $userid One or more user or group IDs as string or array.
     * @return AclList The access control list for the user/group.
     */
    function getAclForUser($userid) {

    }

    /**
     *
     * @param mixed $userid One or more user or group IDs as string or array.
     * @param mixed $objectid One or more object IDs as string or array.
     * @return boolean True if access is allowed to the object.
     */
    function hasAccess($userid,$objectid) {

    }

    /**
     * @brief Manage Acl entries in the database.
     *
     * @param mixed $userid One or more user or group IDs as string or array.
     * @param mixed $objectid One or more object IDs as string or array.
     * @param integer $access One of the acl::ACCESS_* flags.
     */
    function setAccess($userid,$objectid,$access) {

    }

}
