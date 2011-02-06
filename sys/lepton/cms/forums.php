<?php

    /**
     * Lepton Forums library
     *
     * This library has support for namespaces as well as nested forums; to
     * reference a specific forum, the following syntax should be used:
     *
     *    [ns]:[/][loc[/loc[..]]]
     *
     * For example, these are all valid:
     *
     *    /foo/bar/baz        /foo/bar/baz in the default namespace
     *    :/foo/bar/baz       /foo/bar/baz in the default namespace
     *    foo:bar/baz         /bar/baz in the foo namespace
     *    bar:/baz/xyzzy      /baz/xyxxy in the bar namespace
     *
     * @author Christopher Vagnetoft <noccy@chillat.net>
     * @since 0.2
     * @todo Implement a proper forum class
     */

    class Forums extends Library {

        function __construct() {
            parent::__construct();
            $sm = DBX::getInstance(DBX)->getSchemaManager();
            $sm->checkSchema('forums');
            $sm->checkSchema('forumposts');
            $sm->checkSchema('forumthreads');
        }

        /**
         * Return a list of forums that has the specified parent URI. In order
         * to get the actual forum posts, use the getForum() method.
         *
         * @param string $uri The URI (with optional namespace) to match
         * @return array Matching forums
         * @see Forums::getForum
         */
        function getForumsByParentUri($uri = '') {

            // TODO: Namespace support
            $db = DBX::getInstance(DBX);
            $f = $db->getRows("SELECT * FROM forums WHERE parent = '%s' ORDER BY uri ASC", $uri);

        }

        /**
         * Returns the forum that has the specified forum URI. The returned class will
         * be a ForumInstance, allowing for basic manipulation of the forums. To get
         * the child forums of the request forum, use Forums::getForumsByParentUri
         *
         * @param string $slug The URI (with optional namespace) of the forum to open
         * @return ForumInstance The forum instance
         * @see Forums::getForumsByParentUri
         */
        function getForum($slug=null) {

            $f = new Forum($slug);
            return $f;

        }

    }


////////// Forum /////////////////////////////////////////////////////////////

    /**
     *
     *
     *
     */
    class Forum {

        private $_meta;
        private $forum;
        private $childforums;
        private $threads;

        /**
         *
         *
         *
         */
        function __construct($slug) {
            $db = DBX::getInstance(DBX);
            $this->forum = $db->getSingleRow("SELECT * FROM forums WHERE slug='%s'", $slug);
            $this->childforums = $db->getRows("SELECT * FROM forums WHERE parent='%s'", $this->forum['id']);
            $this->threads = $db->getRows("SELECT * FROM forumthreads WHERE forumid='%s'", $this->forum['id']);

            $this->_meta['id'] = $this->forum['id'];
            $this->_meta['name'] = $this->forum['forumname'];
            $this->_meta['description'] = $this->forum['description'];
            $this->_meta['slug'] = $this->forum['slug'];

        }


        /**
         *
         *
         *
         */
        function __get($key) {
            switch($key) {
                case 'id':
                case 'name':
                case 'description':
                case 'slug':
                    return $this->_meta[$key];
                case 'numposts':
                case 'numthreads':
                case 'lastactivity':
                    return $this->forum[$key];
                case 'lastactive':
                    if (!$this->_meta['lastactive']) {
                        $r = DBX::getInstance(DBX)->getSingleRow("SELECT * FROM forumthreads WHERE id='%d' ORDER BY threadupdated DESC", $this->forum['id']);
                        if ($r) $this->_meta['lastactive'] = new ForumThread($r);
                    }
                    return $this->_meta['lastactive'];
                default:
                    throw new BaseException('Invalid request for Forum::__get - '.$key);
            }
        }

        /**
         *
         *
         *
         */
        function __set($key,$value) {

        }

        /**
         *
         *
         *
         */
        function getChildForums() {
            $fl = array();
            foreach($this->childforums as $forums) {
                $fl[] = new Forum($forums['slug']);
            }
            return $fl;
        }

        /**
         *
         *
         *
         */
        function getThreads($page=null,$items=null) {
            if (!$items) $items = config::get('lepton.cms.forums.threadsperpage', 20);
            $tl = array();
            foreach($this->threads as $thread) {
                $tl[] = new ForumThread($thread);
            }
            return $tl;
        }

        /**
         *
         *
         */
        function getThread($thread) {
            $db = DBX::getInstance(DBX);
            $td = $db->getSingleRow("SELECT * FROM forumthreads WHERE forumid='%s' AND slug='%s'", $this->forum['id'], $thread);
            $to = new ForumThread($td);
            return $to;
        }

        /**
         *
         *
         */
        function addThread(ForumThread $thread) {
            if (!$thread->id) {
                # forumid | slug | title | threadcreated  | usercreated
                # threadupdated | userupdated | posts
                printf(
                    "INSERT INTO forumthreads ".
                    "(forumid,slug,title,threadcreated,usercreated,threadupdated,userupdated,posts) ".
                    "VALUES ".
                    "(%d,'%s','%s',NOW(),'%d',NOW(),'%d',0)",
                    $this->forum['id'], $thread->slug, $thread->title,
                    $thread->user->userid, $thread->user->userid
                );
                $aid=0;
                $rs = DBX::getInstance(DBX)->getSingleRow("SELECT * FROM forumthreads WHERE id='%d'", $aid);
                die();
                return new ForumThread($rs);
            }
        }

        /**
         *
         *
         *
         */
        function canPost() {
            return true;
        }

    }





////////// ForumThread ///////////////////////////////////////////////////////


    /**
     *
     *
     * @author Christopher Vagnetoft <noccy@chillat.net>
     */
    class ForumThread {

        private $_meta;

        /**
         * Create a new thread. If the id is provided the thread will be
         * loaded, otherwise an empty thread will be returned.
         *
         * Use the ForumThread::find($slug,$forum) to find the ID of the
         * thread if not known.
         *
         * @param array $thread The thread database record
         */
        public function __construct($thread=null) {
            if ($thread) {
                $this->_meta = $thread;
            } else {
                $this->_meta = array(
                    'id' => null
                );
            }
        }

        /**
         * Returns the posts that make up the thread. If the item count is
         * omitted, the config key lepton.cms.forums.itemsperpage will be
         * used, and if the key is missing it defaults to 20.
         *
         * @param int $page The page to display (0 based)
         * @param int $items The number of items to display
         */
        function getPosts($page=null,$items=null) {
            if (!$items) $items = config::get('lepton.cms.forums.postsperpage', 20);
            $rs = DBX::getInstance(DBX)->getRows("SELECT * FROM forumposts WHERE threadid='%d' ORDER BY postdate ASC", $this->_meta['id']);
            $rp = array();
            $i = 1;
            foreach($rs as $p) {
                $p['index'] = $i++;
                $rp[] = new ForumPost($p);
            }
            return $rp;
        }


        /**
         *
         *
         *
         */
        function getLastPost() {

            $rs = DBX::getInstance(DBX)->getSingleRow("SELECT * FROM forumposts WHERE threadid='%d' ORDER BY postdate DESC LIMIT 1", $this->_meta['id']);
            $p = new ForumPost($rs);
            return $p;

        }

        function addPost(ForumPost $post) {

            

        }

        /**
         *
         *
         *
         */
        function __get($key) {
            switch($key) {
                case 'id':
                case 'title':
                case 'isnew':
                case 'slug':
                    return $this->_meta[$key];
                case 'replies':
                    if (!$this->_meta['replies']) {
                        $r = DBX::getInstance(DBX)->getSingleRow("SELECT COUNT(*) FROM forumposts WHERE threadid='%d'", $this->_meta['id']);
                        $this->_meta['replies'] = $r[0] - 1;
                    }
                    return $this->_meta[$key];
                case 'created':
                    return $this->_meta['threadcreated'];
                case 'user':
                    if (!$this->_meta['createduser']) $this->_meta['createduser'] = User::getUser($this->_meta['usercreated']);
                    return $this->_meta['createduser'];
                default:
                    throw new BaseException('Invalid request for ForumThread::__get - '.$key);
            }
        }

        /**
         *
         *
         *
         */
        function __set($key,$value) {
            switch($key) {
                case 'id':
                case 'isnew':
                    $this->_meta[$key] = $value;
                    break;
                case 'title':
                    $this->_meta['title'] = $value;
                    break;
                case 'slug':
                    $this->_meta['slug'] = $value;
                    break;
                case 'created':
                    $this->_meta['threadcreated'] = $value;
                    break;
                case 'replies':
                    throw new BaseException('You cant modify ForumThread->replies');
                case 'user':
                    $this->_meta['usercreated'] = $value->userid;
                    break;
                default:
                    throw new BaseException('Invalid request for ForumThread::__get - '.$key);
            }
        }

    }




////////// ForumPost //////////////////////////////////////////////////////////

    /**
     * Forumpost class, contains a single post in a thread in a forum.
     *
     *
     *
     */
    class ForumPost {

        private $_meta;

        function __construct($post=null) {
            if ($post) {
                $this->_meta = $post;
            } else {
                $this->_meta['id'] = null;
                $this->_meta['userid'] = User::getActiveUserId();
                $this->_meta['postip'] = request::getRemoteHost();
            }
        }

        /**
         *
         *
         *
         */
        function __get($key) {
            switch($key) {
                case 'index':
                case 'id':
                case 'forumid':
                case 'threadid':
                case 'userid':
                case 'title':
                case 'message':
                case 'postip':
                case 'postdate':
                    return $this->_meta[$key];
                case 'user':
                    if (!$this->_meta['user']) $this->_meta['user'] = User::getUser($this->_meta['userid']);
                    return $this->_meta['user'];
                default:
                    throw new BaseException('Invalid request for ForumThread::__get - '.$key);
            }
        }

        function __set($key,$value) {
            switch($key) {
                case 'index':
                case 'id':
                case 'forumid':
                case 'threadid':
                case 'userid':
                case 'title':
                case 'message':
                case 'postip':
                case 'postdate':
                    $this->_meta[$key] = $value;
                    break;
                case 'user':
                    $this->_meta['user'] = $value->id;
                    break;
                default:
                    throw new BaseException('Invalid request for ForumThread::__set - '.$key);
            }
        }

    }


//////////////////////////////////////////////////////////////////////////////

    Library::register('forums',array(
        'baseclass'    => 'Forums',
        'alias'     => 'forums',
        'provides'    => 'lepton:forums'
    ));


?>
