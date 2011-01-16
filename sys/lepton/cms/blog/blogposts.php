<?php __fileinfo("Lepton CMS Blog Posts library");

config::def('lepton.cms.blog.defaultblog','default');

class BlogPosts {

    /**
     * Initialize the collection for the specific blog (or the default blog if
     * blogid is null).
     *
     * @param $blogid The blog to access.
     */
    function __construct($blogid = null) {

    }

    function getAllPosts() {
        // Acquire a handle to the database.
        $db = new DatabaseConnection();
        // Query the posts and assign the result to a postcollection.
        $rs = $db->getRows("SELECT * FROM blogposts ORDER BY postdate DESC;")
        $coll = new PostCollection($rs);
        // Finally return it
        return $coll;
    }


}

class PostCollection {

    function __construct($rs) {
        $this->posts = array();
        // Enumerate to extract the posts
        foreach($rs as $row) {
            $p = new BlogPost();
            $p->assign($row);
            
        }
    }

}

class BlogPost extends BasicContainer {

    protected $properties = array(
        'blogid' => null,
        'title' => null,
        'slug' => null,
        'excerpt' => null,
        'content' => null,
        'tags' => array(),
        'categories' => array(),
        'author' => null,
        'uuid' => null,
    );

}
