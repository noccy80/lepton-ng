<?php

using('lepton.content.provider');
using('lepton.content.extension');

class Content {

	private static $providers = array();
	private static $extensions = array();

	private function __construct() {
		// Non-creatable class
	}

	static public function registerProvider(ContentProvider $provider) {
		$ns = $provider->getNamespace();
		if (isset(self::$providers[$ns])) {
			logger::warn('Overwriting previous handler for %s', $ns);
		}
		self::$providers[$ns] = $provider;
	}

	static public function registerExtension(ContentExtension $extension) {
		self::$extensions[] = $extension;
	}

	static public function initExtensions($object) {
		foreach(self::$extensions as $extension) {
			$object->{$extension->getHandle()} = new $extension($object);
		}
	}

	static public function getExtensions() {
		return self::$extensions;
	}

	static public function get($uri) {
		list($ns,$objid) = explode(':',$uri);
		if (isset(self::$providers[$ns])) {
			return self::$providers[$ns]->getContentFromObjectId($objid);
		} else {
			throw new ContentException(sprintf("No handler found for %s", $uri));
		}
	}

}

class ContentException extends Exception { }
