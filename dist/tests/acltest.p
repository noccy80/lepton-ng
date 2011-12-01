#!/usr/bin/php
<?php

require('sys/base.php');
using('lepton.ldb.database');
using('lepton.user.*');

class QueryObject implements IAclObject {
	private $uuid;
	private $rlist = array();
	function __construct($uuid) {
		$this->uuid = $uuid;
		$db = new DatabaseConnection();
		$rl = $db->getRows("SELECT DISTINCT role FROM aclconf WHERE object=%s", $uuid);
		foreach($rl as $role) {
			$this->rlist[$role['role']] = acl::ACL_NULL;
		}
	}
	function setRoles(Array $roles) {
		$this->rlist = $roles;
	}
	function getObjectUuid() { return $this->uuid; }
	function getObjectRoles() { return $this->rlist; }
}

class AclUtil extends ConsoleApplication {

	var $description = 'ACL Utility/Test';
	var $arguments = array(
		array('s:','subject','Subject UUID'),
		array('o:','object','Object UUID'),
		array('h','help','Show this help')
	);
	var $commands = array(
		array('show','Show the effective permissions'),
		array('show-matrix','Show the permission matrix'),
		array('allow','Update the entry to allow access'),
		array('deny','Update the entry to deny access'),
		array('clear','Clear the access modifier for the entry')
	);

	public function main($argc,$argv) {

		if ($this->getParameterCount() > 0) {
			switch(strtolower($this->getParameter(0))) {
				case 'show-matrix':
					$suuid = $this->getArgument('s');
					$ouuid = $this->getArgument('o');
					if ($suuid && $ouuid) {
						$this->showMatrix($ouuid,$suuid);
					} else {
						console::error("show-matrix requires both object and subject uuid");
					}
					break;
				case 'show':
					$suuid = $this->getArgument('s');
					$ouuid = $this->getArgument('o');
					if ($suuid && $ouuid) {
						$this->showAccess($ouuid,$suuid);
					} else {
						console::error("show requires both object and subject uuid");
					}
					break;
				default:
					$this->usage();
					break;
			}
		} else {
			$this->usage();		
		}
	
	}

	public function showMatrix($ouuid,$suuid,$roles=null) {

		$db = new DatabaseConnection();
		
		$qo = new QueryObject($ouuid);
		// if ($roles) $qo->setRoles($roles);
		$qo->setRoles(array(
			'view' => true,
			'post' => false,
			'delete' => false,
			'attach' => false
		));
		
		$u = $db->getSingleRow("SELECT * FROM users WHERE uuid=%s", $suuid);
		$am = acl::getAccessMatrix($qo, user::find($u['username']));

		console::write("%-60s | %-15s | ", 'Subject', 'Type');
		foreach($am[0]['roles'] as $p=>$v) {
			console::write('%-8s ', substr($p,0,8));
		}
		console::writeLn();

		console::write("%-60s-|-%-15s-|-", str_repeat('-',60), str_repeat('-',15));
		foreach($am[0]['roles'] as $p=>$v) {
			console::write('%-8s-', str_repeat('-',8));
		}
		console::writeLn();

		foreach($am as $ae) {
			console::write("%-60s | %-15s | ", $ae['label'], $ae['type']);
			foreach((array)$ae['roles'] as $p=>$v) {
				if ($v === acl::ACL_ALLOW) { $vstr = 'ALLOW'; }
				elseif ($v === acl::ACL_DENY) { $vstr = 'DENY'; }
				else { $vstr = '-'; }
				console::write('%-8s ', $vstr);
			}
			console::writeLn();
		}
	
	}

	public function showAccess($ouuid,$suuid,$roles=null) {

		$db = new DatabaseConnection();
		
		$qo = new QueryObject($ouuid);
		// if ($roles) $qo->setRoles($roles);
		$qo->setRoles(array(
			'view' => true,
			'post' => false,
			'delete' => false,
			'attach' => false
		));
		
		$u = $db->getSingleRow("SELECT * FROM users WHERE uuid=%s", $suuid);
		$am = acl::getEffectiveAccess($qo, user::find($u['username']));

		foreach($am as $role=>$v) {
			if ($v === acl::ACL_ALLOW) { $vstr = 'ALLOW'; }
			elseif ($v === acl::ACL_DENY) { $vstr = 'DENY'; }
			else { $vstr = '-'; }
			console::writeLn('%-20s: %-8s ', $role, $vstr);
		}
	
	}

}

lepton::run('AclUtil');
