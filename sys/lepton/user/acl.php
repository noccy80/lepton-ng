<?php

/**
 * @interface IAclObject
 * @brief Interface for an ACL object
 */
interface IAclObject {
	function getObjectUuid();
	function getObjectRoles();
}

/**
 * @interface IAclSubject
 * @brief Interface for an ACL subject
 */
interface IAclSubject {
	function getSubjectUuid();
	function getSubjectGroups();
}

/**
 * @class AclconfSchema
 * @brief The schema that is used to define the ACL permissions
 */
class AclconfSchema extends SqlTableSchema {
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

	const TYP_OBJECT = 'object';
	const TYP_SUBJECT = 'subject';
	const TYP_GROUP = 'group';
	const TYP_EFFECTIVE = 'effective';

	/**
	 * @brief Initialize the ACL subsystem.
	 *
	 * This will make sure the schemas are created and correct.
	 */
	static function initialize() {
		$db = new DatabaseConnection();
		$sm = $db->getSchemaManager();
		if (!$sm->schemaExists('aclconf')) $sm->apply(new AclconfSchema());
	}

	/**
	 * @brief Retrieve the access matrix for the subject.
	 *
	 * The matrix will contain all the roles as well as effective modifiers
	 * applied including groups as well as the default roles.
	 *
	 * @param IAclObject $object The object to for which the access is queried
	 * @param IAclSubject $subject The subject whos access is being queried
	 * @return Array The access mnatrix
	 */
	static function getAccessMatrix(IAclObject $object, IAclSubject $subject) {
	
		// Get the object uuids
		$ouuid = $object->getObjectUuid();
		$roles = $object->getObjectRoles();
		
		// Get the uuid and groups from the subject
		$sgroups = $subject->getSubjectGroups();
		$suuid = $subject->getSubjectUuid();
		
		// Create database connection
		$db = new DatabaseConnection();
		$matrix = array();
		
		// Determine and label the default roles for the object
		$matrix[] = array(
			'label' => sprintf('%s <%s>', typeOf($object), $ouuid), 
			'type' => self::TYP_OBJECT,
			'roles' => $roles
		);
		// Compile a role list and save the effective roles
		$rlist = array();
		foreach($roles as $role=>$def) { $rlist[] = $role; }
		$effective = $roles;
		
		// Determine and label the roles for the subjects groups
		foreach($sgroups as $group) {
			$guuid = $group->getSubjectUuid();
			$groles = acl::getExplicitAccessRecord($guuid,$ouuid,$rlist);
			$matrix[] = array(
				'label' => sprintf('%s <%s>', (string)$group, $guuid), 
				'type' => self::TYP_GROUP,
				'roles' => $groles
			);
			foreach($effective as $role=>$val) {
				if ($groles[$role] === self::ACL_DENY) {
					$effective[$role] = self::ACL_DENY;
				} elseif ($groles[$role] === self::ACL_ALLOW) {
					$effective[$role] = self::ACL_ALLOW;
				}
			}
		}
		// Determine and label the roles for the subject
		$sroles = acl::getExplicitAccessRecord($suuid,$ouuid,$rlist);
		$matrix[] = array(
			'label' => sprintf('%s <%s>', (string)$subject, $suuid), 
			'type' => self::TYP_SUBJECT,
			'roles' => $sroles
		);
		foreach($effective as $role=>$val) {
			if ($sroles[$role] === self::ACL_DENY) {
				$effective[$role] = self::ACL_DENY;
			} elseif ($sroles[$role] === self::ACL_ALLOW) {
				$effective[$role] = self::ACL_ALLOW;
			}
		}
		// Finally assemble the effective permissions
		$matrix[] = array(
			'label' => sprintf('%s <%s>', (string)$subject, $suuid), 
			'type' => self::TYP_EFFECTIVE,
			'roles' => $effective
		);
		
		// Return result
		return $matrix;

	}
	
	static function getExplicitAccessRecord($suuid,$ouuid,$rlist) {

		$db = new DatabaseConnection();
		$rolesraw = $db->getRows("SELECT role,access FROM aclconf WHERE object=%s AND subject=%s", $ouuid, $suuid);
		$rolesraw = arr::flip($rolesraw,'role');
		// Translate access into tristate booleans
		foreach($rlist as $role)  {
			if (arr::hasKey($rolesraw,$role)) {
				$rf = $rolesraw[$role]['access'];
				if ($rf == 'Y') {
					$access = self::ACL_ALLOW;
				} elseif ($rf == 'N') {
					$access = self::ACL_DENY;
				} else {
					$access = self::ACL_NULL;
				}
			} else {
				$access = self::ACL_NULL;
			}
			$roles[$role] = $access;
		}
		return $roles;

	}
	
	/**
	 * @brief Retrieve the effective access on the object
	 *
	 * If the subject is not specified, the current user will be used.
	 *
	 * @param IAclObject $object The object to for which the access is queried
	 * @param IAclSubject $subject The subject whos access is being queried
	 * @return Array The effective access for each of the roles
	 */
	static function getEffectiveAccess(IAclObject $object, IAclSubject $subject = null) {

		// If the subject is not specified, set it to the current user.
		if (!$subject) $subject = user::getActiveUser();
		
		$am = acl::getAccessMatrix($object,$subject);
		$ar = end($am);
		return $ar['roles'];
	
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
	
		// If the subject is not specified, set it to the current user.
		if (!$subject) $subject = user::getActiveUser();
		
		// Retrieve the subjects UUID and query it
		$suuid = $subject->getSubjectUuid();
	
	}

    /**
     * @brief Update Acl entries in the database
     *
     * to allow or deny access to the specific object for the specific role and
     * subject. If the subject is passed as null, it will be replaced with the
     * active user.
     *
     * @param IAclObject $object The object
     * @param string $role One or more object IDs as string or array.
     * @param IAclSubject $subject One or more user or group IDs as string or array.
     * @param boolean $access One of the acl::ACL_* flags.
     */
    function setAccess(IAclObject $object, $role, IAclSubject $subject, $access) {

		// If the subject is not specified, set it to the current user.
		if (!$subject) $subject = user::getActiveUser();
		
		// Retrieve the uuid of the subject and the object
		$suuid = $subject->getSubjectUuid();
		$ouuid = $object->getObjectUuid();
		
		// Convert the access into a string
		if ($access === self::ACL_NULL) {
			$accessstr = '-';
		} elseif ($access === self::ACL_ALLOW) {
			$accesstr = 'Y';
		} else {
			$accesstr = 'N';
		}
		
		// Update the record
		$db = new DatabaseConnection();
		$db->updateRow("REPLACE INTO aclconf (object,role,subject,access) VALUES (%s,%s,%s,%s)",
			$ouuid, $role, $suuid, $accessstr);
    }

}

// Initialize the ACL subsystem
acl::initialize();
