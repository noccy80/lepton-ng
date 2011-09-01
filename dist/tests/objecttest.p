#!/usr/bin/php
<?php

require('sys/base.php');

using('lepton.content.content');

class PingbackExtension extends ContentExtension {
	function getHandle() { return "pingback"; }
	function ping() { echo "pong"; }
}
class TestContentObject extends ContentObject {
	private $uri;
	function __construct($uri) {  $this->uri = $uri; parent::__construct(); }
	function getHtml() { return $this->uri; }
	function getUri() { return $this->uri; }
	function getObjectId() { return null; }
}
class TestContentProvider extends ContentProvider {
	function getNamespace() { return "test"; }
	function getContentFromObjectId($uri) {  return new TestContentObject($uri); }
}

contentmanager::registerProvider(new TestContentProvider);
contentmanager::registerExtension(new PingbackExtension);
$f = contentmanager::get('test:foobar');
echo $f->getHtml();
$f->pingback->ping();
