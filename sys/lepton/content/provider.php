<?php

interface IContentProvider {
	function getNamespace();
	function getContentFromObjectId($uri);
}

abstract class ContentProvider implements IContentProvider {

}

interface IContentObject {
	function getHtml();
	function getUri();
	function getObjectId();
	function hasChildren();
}