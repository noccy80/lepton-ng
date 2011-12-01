<?php

require('sys/base.php');

// The ACL and Authentication classes ar eneeded to get access to the user
// record (subject) and the actual ACL classes.
using('lepton.user.acl');
using('lepton.user.authentication');

/*
 * This is an example of how to implement the IAclObject interface. It should
 * not be used as it is, but rather implemented in a similar fashion. The
 * important thing to make sure that each instance is assigned a unique and
 * constant uuid.
 */
class Guestbook implements IAclObject {

	// This objects UUID
	private $uuid = null;
	// This objects defined roles
	private $roles = array(
		'post' => true,
		'moderate' => false,
		'view' => true,
		'attach' => false
	);

	function getObjectUuid() {
		// return the UUID of the object here
		return $this->uuid;
	}

	function getObjectRoles() {
		// return the ACL roles of the object here
		return $this->roles;
	}

	function getDescription() {
		// return the descriptive name for the object
		return sprintf("Forum %s", $this->uuid);
	}

	function __construct($guestbookid) {
		// Assuming that the guestbook is opened with its UUID
		$this->uuid = $guestbookid;
	}

	// This is one of the methods used to perform an action on the object.
	function post($message,$attachment=null) {
	
		// Check the ACL entries. Leaving out parameter 3 will cause the ACL
		// class to look up and open the current user.
		if (!acl::getAccess($this,'post')) {
			// User doesn't have permission to post at all
			throw new AccessException("No access to post");
		}
		
		// Check ACL entries for posting attachments.
		if (($attachment) && (!acl::getAccess($this,'attach'))) {
			// User doesn't have permission to attach stuff
			throw new AccessException("No access to attach");
		}
		
		// All is well. Post the data
	}

}


// Create an instance of the test class and retrieve the active user.
$book = new Guestbook('954c1ea0-fd9a-44e6-8cad-f601fe079a36');
$user = user::getActiveUser();

// Get the access matrix for the user
$acl = acl::getAcessMatrix($book,$user);
// Returned data in condensed form:
//                          post  moder.view  attach
// object:Guestbook         true  false true  false
// user:<username>          NULL  NULL  NULL  NULL
// group:administrators     NULL  true  NULL  NULL
// effective                true  true  true  false

// Grant the user access to posting attachments to the guestbook
acl::setAccess($book,'attach',$user,true);
