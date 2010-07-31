<?php

	interface IRouter {
		function route();
	}

	abstract class Router implements IRouter {
		function getSegment($index) { }
		function getSegmentSlice($start,$end) { }
		function getDomain() { }
		function getFullDomain() { }
		function getFullUri() { }
	}

?>
