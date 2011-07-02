<?php

config::def('lepton.gallery.mediadir', base::appPath().'/media/');
config::def('lepton.gallery.cachedir', base::appPath().'/cache/');
// config::def('lepton.gallery.renderer', array('WatermarkImageFilter','mylogo.png'));

using('lepton.graphics.canvas');
using('lepton.utils.pagination');

class GalleryItemsTable extends SqlTableSchema {
	function define() {
		// Only to be used during testing!
		$this->dropOnCreate();
		// Table name
		$this->setName('galleryitems');
		// Table columns
		$this->addColumn('id', 'int', self::COL_AUTO | self::KEY_PRIMARY);
		$this->addColumn('name', 'varchar:160');
		$this->addColumn('uuid', 'char:37', self::COL_FIXED);
		$this->addColumn('src','varchar:200');
		// Indexes
		$this->addIndex('uuid', array('uuid'));
		$this->addIndex('src', array('src'));
	}
}
SqlTableSchema::apply(new GalleryItemsTable());

/**
 * @class Gallery
 * @package lepton.media
 * @brief Gallery Management
 *
 */
class Gallery {

	static function get($selection, $paginator=null) {
		return new GalleryCollection($selection,$paginator);
	}
	
	static function getTagStatus() {
	    // Return a lsit of all the tags applied
	}
	
	static function getCollectionStatus() {
	    // Return a list of all the categories applied
	}
	
}

class GalleryCollection {

    private $items = array();

    /**
     * Create a new collection using an uri or a pattern
     *
     * f.ex.  tag:foo, category:bar, title:*, *, user:bob
     *
     */
    public function __construct($selection, Paginator $paginator = null) {
        // Create a collection from a tag, category, title, user etc.
        $db = new DatabaseConnection();
        $sql = $db->quote('SELECT * FROM galleryitems');
        $count = $db->quote('SELECT COUNT(*) AS numitems FROM galleryitems');
        // If we have a paginator, make use of it
        if ($paginator) $sql.=' '.$paginator->getSqlLimit();
        // Then select the rows and the total count
        $rs = $db->getRows($sql);
        $rsc = $db->getSingleRow($count);
    }
    
    public function count() {
        // Return the number of items (on this page)
        return count($this->items);
    }
    
    public function getItem($index) {
        // Get a specific item from the set
    }

}

class GalleryItem {

    private $metadata = array(
    );

    function __construct($id = null) {
        // Create a new image (from the database).
    }
    
    function addTag($tag) {
        // Add the tag to the image, creating it if it doesn't already exist.
    }
    
    function removeTag($tag) {
        // Remove tag from item and if it's the last image having the tag
        // also remove the tag.
    }
    
    function getTags() {
        // Get all the tags assigned to the image
    }
    
    function setCategory($category) {
        // Assign the category to the image
    }
    
    function __get($key) {
        if (arr::hasKey($this->metadata,$key)) {
            return $this->metadata[$key];
        } else {
            throw new BadPropertyException(__CLASS__,$key);
        }
    }
    
    function __set($key,$value) {
        $this->metadata[$key] = $value;        
    }
    
    function __unset($key) {
        unset($this->metadata[$key]);
    }
    
    function getAllProperties() {
        // Return all properties made available via __get().
        return (array)$this->metadata;
    }
    
    function getThumbnail($width,$height) {
        // Create and return the specified thumbnail, and save a cached copy
        // of the image.
        $c = new Image($this->filename);
        $c->resize($width,$height);
        // Save to cache and return cache filename
        return 'foo.png';
    }

    function getImage($size) {
        // Return a high quality version of the image, optionally applying
        // a renderer to it to f.ex. watermark it. Will also cache the image
    }
    
}
