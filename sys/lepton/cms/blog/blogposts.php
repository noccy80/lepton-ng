<?php __fileinfo("Lepton CMS Blog Posts library");

config::def('lepton.cms.blog.defaultblog','default');

class Blog {

    const POST_PUBLISHED = 'publish';
    const POST_DRAFT = 'draft';
    const POST_AUTODRAFT = 'auto-draft';
    const POST_INHERIT = 'inherit';
    const POST_DELETED = 'deleted';

    private $_blog = null;

    /**
     * Initialize the collection for the specific blog (or the default blog if
     * blogid is null).
     *
     * @param $blogid The blog to access.
     */
    function __construct($blogid = null) {
        $this->_blog = $blogid;
    }

    function getAllPosts() {
        // Acquire a handle to the database.
        $db = new DatabaseConnection();
        // Query the posts and assign the result to a postcollection.
        $rs = $db->getRows("SELECT * FROM blogposts ORDER BY pubdate DESC");
        $coll = array();
        foreach($rs as $post) {
            $coll[] = new BlogPost($post);
        }
        // Finally return it
        return $coll;
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
