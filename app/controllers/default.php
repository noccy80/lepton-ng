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
		
		function upload() {

			if (request::isPost()) {
				$file = request::post('userfile');
				printf('<p>%s</p>', $file);
				$dest = APP_PATH.'cache/image.jpg';
				printf('<p>%s</p>', $dest);
				if ($file->save($dest)) {
					print('<p><img src="/cache/image.jpg"></p>');
				} else {
					print('<p><b>Failed to save the image</b></p>');
				}
			}
			
			print('<form enctype="multipart/form-data" action="/default/upload" method="POST">');
			print('Send this file: <input name="userfile" type="file">');
			print('<input type="submit" value="Send File">');
			print('</form>');
			
		}

	}

?>
