#!/usr/bin/php
<?php

require('sys/base.php');
using('lepton.ldb.database');
using('lepton.user.*');

class TestObject implements IAclObject {
	function getObjectUuid() { return uuid::v4(); }
	function getObjectRoles() { return array('create'=>false,'view'=>true); }
}

var_dump(user::find('noccy')->getGroups());
var_dump(acl::getAccessMatrix(new TestObject(),user::find('noccy')));
