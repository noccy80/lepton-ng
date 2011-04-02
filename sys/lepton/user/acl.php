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
    const ACL_DENY = false; ///< Deny access to the object
    const ACL_ALLOW = true; ///< Allow access to the object
    const ACL_NULL = null; ///< No Acl entry, or clear existing entry.

    /**
     *
     * @param mixed $objectid One or more object IDs as string or array.
     * @return AclList The access control list for the object.
     */
    function getAclForObject($objectid) {
		$db = new DatabaseConnection();
		$acl = $db->getRows("SELECT * FROM acl WHERE objectuuid=%s", $objectid);
    }

    /**
     *
     * @param mixed $userid One or more user or group IDs as string or array.
     * @return AclList The access control list for the user/group.
     */
    function getAclForUser($userid) {
		$db = new DatabaseConnection();
		$acl = $db->getRows("SELECT * FROM acl WHERE useruuid=%s", $userid);
    }

    /**
     *
     * @param mixed $userid One or more user or group IDs as string or array.
     * @param mixed $objectid One or more object IDs as string or array.
     * @return boolean True if access is allowed to the object.
     */
    function hasAccess($userid, $objectid, $default=false) {
		$db = new DatabaseConnection();
		$acl = $db->getSingleRow("SELECT * FROM acl WHERE objectuuid=%s AND useruuid=%s", $objectid, $userid);
		if ($acl) {
			// Check values
		} else {
			return $default;
		}
    }

    /**
     * @brief Manage Acl entries in the database.
     *
     * @param mixed $userid One or more user or group IDs as string or array.
     * @param mixed $objectid One or more object IDs as string or array.
     * @param integer $access One of the acl::ACL_* flags.
     */
    function setAccess($userid, $objectid, $access) {

    }

}
