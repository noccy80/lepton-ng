<?php

__fileinfo("Group Management");

using('lepton.crypto.uuid');
using('lepton.user.acl');

/**
 * @class UserGroup
 * @brief Contains a usergroup record.
 *
 * Available ambient properties:
 *   Any property can be used as an ambient property.
 */
class UserGroup implements IAclSubject {

	private $uuid = null;
	private $groups = array();

	public function getSubjectUuid() {
		return $this->uuid;
	}
	
	/**
	 * @brief Returns the groups that this group is a member of
	 *
	 * @return Array The groups that the group is a member of
	 */
	public function getSubjectGroups() {
		return array();
	}
	
	static function find($groupname) {
	
	}
	
	public function __get($property) {
	
	}
	
	public function __set($property,$value) {
	
	}
	
	public function __unset($property) {
	
	}
	
	public function __construct() {
		$this->uuid = uuid::v4();
	}
	
	public function __destruct() {
	
	}
	
	public function __toString() {
		return typeOf($this);
	}

}

class UserGroupExtension extends UserExtension { 
	function getMethods() {
		return array('getGroups','addGroup','removeGroup');
	}
	function getGroups() { return array(new UserGroup()); }
	function addGroup(UserGroup $group) { }
	function removeGroup(UserGroup $group) { }
}
