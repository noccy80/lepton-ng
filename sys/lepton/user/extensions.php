<?php

interface IUserExtension {
	function getMethods();
}

abstract class UserExtension implements IUserExtension { 

	protected $user;

	function __construct($user) {
		$this->user = $user;
	}

}
