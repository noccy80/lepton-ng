#!/usr/bin/php
<?php 	require('sys/base.php');
	using('lepton.user.acl');
	using('lepton.user.*');

class MyObject implements IAclObject {

	private $uuid = null;
	function getObjectUuid() { return($this->uuid); }
	function getObjectRoles() { return( array('default' => false) ); }
	function __construct($uuid) { $this->uuid = $uuid; }

}

$obj = new MyObject("449abb01-8f44-4b59-8ae2-60ece6acf685");
$sub = user::getUser(1);

acl::setAccess($obj,'default',$sub,acl::ACL_ALLOW);
var_dump(acl::getAccess($obj,'default',$sub));
var_dump(acl::getAccessMatrix($obj,$sub));
