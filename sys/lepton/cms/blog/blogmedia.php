<?php __fileinfo("Lepton CMS Blog Media Manager");

class BlogMedia {

    const sql_query_base = 'SELECT mi.* FROM mediaitems mi ';

    /**
     * Initialize the collection for the specific blog (or the default blog if
     * blogid is null).
     *
     * @param $blogid The blog to access.
     */
    public function __construct($blogid = null) {
    
    
    }

    /**
     * Retrieve all associated media for the requested blog. Can use "/" to
     * retrieve media directly from the root. If a path is specified, folders
     * will be returned as well. If path is null, only actual media items will
     * be returned.
     *
     * @param $path The path to query, or null for all media
     */
    public function getAllMedia($path = null) {
    
        // Get a database handle
        $db = new DatbaseConnection();
        // Translate the path and query accordingly
        if ($path) {
            $query = self::sql_query_base.' WHERE ... ORDER BY medianame ASC';
        
        } else {
            // Get all items
            $query = self::sql_query_base.' ORDER BY medianame ASC';
        }
    
    }

}

class MediaCollection { 

    public function getItemCount() {
        // Return number of items in the collection
    }
    
    public function getItem($index) {
    
    }

}


