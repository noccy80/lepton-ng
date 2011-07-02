<?php

interface IAclObject {
	function getObjectUuid();
	function getObjectRoles();
}

interface IAclSubject {
	function getSubjectUuid();
	function getSubjectGroups();
}

class AclDbSchema extends SqlTableSchema {
	function define() {
		$this->setName('aclconf');
		$this->addColumn('id','int',self::COL_AUTO | self::KEY_PRIMARY);
		$this->addColumn('object','char:37',self::COL_FIXED);
		$this->addColumn('role','char:42');
		$this->addColumn('subject', 'char:37',self::COL_FIXED);
		$this->addColumn('access', 'enum:Y,N,-');
		$this->addIndex('access',array('object','role','subject'),self::KEY_UNIQUE);
	}
}

/**
 * @class Acl
 * @brief Access Control List (ACL) implementation for Lepton
 *
 * This class operates exclusively on UUIDs. For it to function properly both
 * the user (the subject) and the object must be identified using their unique
 * UUIDs. The class doesn't care about any UUID being present in a list other
 * than its own access control tables, which makes this class also usable with
 * external users, daemons, or anything else that can be tagged with a UUID.
 *
 * Roles are used to define more fine grained access control over the object,
 * by adding another set of "sub-rules". Each role is a (preferably) short
 * string such as "create", "read", "moderate", "admin", or "view". Any number
 * of roles can be defined for any object.
 *
 * Access is defined as one of three values. NULL indicates that there is no
 * access control in place. TRUE indicates that the subject is explicitly
 * granted access to the object, and FALSE indicates that the subject is
 * explicitly denied access to the object.
 *
 * @package lepton.user.acl
 * @example acldemo.php
 * @author Christopher Vagnetoft <noccy.com>
 */
class Acl {

    const ACL_DENY = false; ///< Deny access to the object
    const ACL_ALLOW = true; ///< Allow access to the object
    const ACL_NULL = null; ///< No Acl entry, or clear existing entry.

	static function initialize() {
		$d = new DatabaseConnection();
		$sm = $d->getSchemaManager();
		if (!$sm->schemaExists('aclconf')) $sm->apply(new AclDbSchema());
	}

	static function getAccessMatrix(IAclObject $object, IAclSubject $subject) {
		// Get the object uuids
		$ouuid = $object->getObjectUuid();
		$roles = $object->getObjectRoles();
		// Get the uuid and groups from the subject
		$sgroups = $subject->getSubjectGroups();
		$suuid = $subject->getSubjectUuid();
		
		$db = new DatabaseConnection();
		$matrix = array();
		// Determine and label the default roles
		$matrix[] = array(sprintf('%s <%s>', typeOf($object), $ouuid), $roles);
		foreach($sgroups as $group) {
			$guuid = $group->getSubjectUuid();
			$matrix[] = array(sprintf('%s <%s>', (string)$group, $guuid), $roles);
		}
		$matrix[] = array(sprintf('%s <%s>', (string)$subject, $suuid), $roles);
		
		// Return result
		return $matrix;

	}

    /**
     * @brief Retrieve Acl entries from the database
     *
     * to tell whether the specified subject has got access to the object or
     * not.
     *
     * @param IAclObject $object The object
     * @param string $role One or more object IDs as string or array.
     * @param IAclSubject $subject One or more user or group IDs as string or array.
     * @param boolean $access One of the acl::ACL_* flags.
     */
	static function getAccess(IAclObject $object, $role, IAclSubject $subject = null) {
		if (!$subject) $subject = user::getActiveUser();
	
	}

    /**
     * @brief Update Acl entries in the database
     *
     * to allow or deny access to the specific object for the specific role and
     * subject.
     *
     * @param IAclObject $object The object
     * @param string $role One or more object IDs as string or array.
     * @param IAclSubject $subject One or more user or group IDs as string or array.
     * @param boolean $access One of the acl::ACL_* flags.
     */
    function setAccess(IAclObject $object, $role, IAclSubject $subject = null, $access) {
		if (!$subject) $subject = user::getActiveUser();
    }

}

acl::initialize();
