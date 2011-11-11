<?php

module("Group Management");

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

    private $groupname = null;
    private $groupid = null;
    private $uuid = null;
    private $groups = array();

    public function getSubjectUuid() {
        return $this->uuid;
    }
    
    public function getSubjectDescription() {
        return sprintf("%s [group:%d]", $this->groupname, $this->groupid);
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
        switch($property) {
        case 'groupname':

        case 'uuid':

        case 'description':

        default:
            throw new BadPropertyException("No ");
        }
    }

    public function __set($property,$value) {

    }

    public function __unset($property) {

    }

    public function __construct($groupid=null) {
        if ($groupid) {

        } else {

        }
        $this->uuid = uuid::v4();
    }

    public function __destruct() {

    }

    public function __toString() {
        return typeOf($this);
    }

    static function getGroup($id) {

    }

    static function findGroup($groupname) {

    }

    static function getGroupByUuid($uuid) {

    }

    static function create($groupname,$description) {
        $g = new UserGroup();
        $g->groupname = $groupname;
        $g->uuid = uuid::v4();
        $g->description = $description;
        return $g;
    }

    static function remove(UserGroup $usergroup) {

    }

    public function addUser(UserObject $user) {
        if (!isset($this)) throw new BaseException("Invoking object method as static method");
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
