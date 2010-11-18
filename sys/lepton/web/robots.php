<?php

	class Robots extends Library {

		private $_rules = null;
		private $_sitemap = null;

		function setSitemap($sitemap) {
			$this->__sitemap = $sitemap;
		}

		function addAllow($resource, $agent='*') {
			$this->_rules[$agent]['allow'][] = $resource;
		}

		function addDisallow($resource, $agent='*') {
			$this->_rules[$agent]['disallow'][] = $resource;
		}

		function setCrawlDelay($delay, $agent='*') {
			$this->_rules[$agent]['crawldelay'] = $resource;
		}

		function setVisitTime($starttime, $endtime, $agent='*') {
			// Visit-time: hhmm-hhmm
		}

		function setRequestRate($pages, $per, $agent='*') {
			// Request-rate: pages/seconds [hhmm-hhmm]
		}

		function output() {
			response::contentType('text/plain');
			foreach($this->_rules as $agent=>$ruleset) {
				printf("User-Agent: %s\n", $agent);
				foreach((array)$ruleset['allow'] as $rule) {
					printf("Allow: %s\n", $rule);
				}
				foreach((array)$ruleset['disallow'] as $rule) {
					printf("Disallow: %s\n", $rule);
				}
			}
		}

	}

?>
