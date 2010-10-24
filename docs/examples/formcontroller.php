<?php

ModuleManager::load('lepton.mvc.forms');

class FormController extends Controller {

	var $gbform = array(
		'name'    => 'required',
		'email'   => 'validate email required',
		'website' => 'validate website optional',
		'message' => 'required'
	);

	function savepost() {
		$post = new WebForm($this->gbform);
		if (!$post->isValid()) {
			// Form is invalid, post it back to the user to allow correction
		} else {
			$db = new DatabaseConnection();
			$db->insertRow("INSERT INTO guestbook (name,email,website,message) VALUES (%s,%s,%s,%s)", 
				$post->name, $post->email, $post->website, $post->message
			);
		}
	}

}

