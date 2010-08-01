<?php

	interface IRouter {
		function route();
	}

	abstract class Router implements IRouter {
		function __construct() {

		}
		function getSegment($index) { }
		function getSegmentSlice($start,$end=-1) { }
		function getDomain() { }
		function getFullDomain() { }
		function getFullUri() { }
	}

?>
