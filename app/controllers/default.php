<?php

	ModuleManager::load('lepton.google.charting');

	//
	//  This is an example controller. You can modify this file as you see
	//  fit. For more information, see the documentation.
	//
	class DefaultController extends Controller {

	        function index() {
	            View::load('default/index.php');
	        }

	        function smarty() {
	            View::load('index.tpl');
	        }
		
		function chart() {
			$ds = new DataSet(null);
			$c = new GChart($ds,300,200);
			$c->render();
		}

	}

?>
