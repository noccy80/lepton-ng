<?php
	//
	//  This is an example controller. You can modify this file as you see
	//  fit. For more information, see the documentation.
	//
	class IndexController extends Controller {

		function index() {
			View::load('index/index.php');
		}

	}

?>