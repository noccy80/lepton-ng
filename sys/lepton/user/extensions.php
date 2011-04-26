<?php

interface IUserExtension {
}

abstract class UserExtension implements IUserExtension { 

	protected $user;

	public function __construct($user) {
		$this->user = $user;
	}

}
