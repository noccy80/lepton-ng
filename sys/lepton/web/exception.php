<?php

	class MvcExceptionHandler extends ExceptionHandler {

		function exception(Exception $e) {
			die("Oh dear, exception $e occured!");
		}
	
	}
	
	Lepton::setExceptionHandler('MvcExceptionHandler');
	
?>
