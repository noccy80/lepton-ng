<?php

    /**
     * Lepton Wiki class
     *
     * @author
     * @todo Implement a proper wiki with namespaces and revisions
     */

    class Wiki {

        const KEY_DEFAULTNS = 'lepton.wiki.defaultns';

        const REVISION_LATEST = -1;

        function __construct() {
            parent::__construct();
            /*
            $this->loadLibrary('database');
            $this->database->checkSchema('wiki');
            */
            //DBX::getInstance(DBX)->getSchemaManager()->checkSchema('wiki');
            // DatabaseConnection::getSchemaManager()
        }

        /**
         * Return a wiki page
         * @param string $pagename The namespace and page name in the
         *   format of ns:page (ex. wiki:howto)
         * @return WikiPage The page
         */
        function getPage($pagename,$revision = Wiki::REVISION_LATEST) {
            list($ns,$uri) = string::parseUri($pagename,'default');
            $db = new DatabaseConnection();
            try {
                if ($revision == Wiki::REVISION_LATEST) {
                    $rs = $db->getSingleRow("SELECT * FROM wiki WHERE ns='%s' AND uri='%s' AND reverted=0 ORDER BY revision DESC LIMIT 1",$ns,$uri);
                } else {
                    $rs = $db->getSingleRow("SELECT * FROM wiki WHERE ns='%s' AND uri='%s' AND revision='%d'",$ns,$uri,$revision);
                }
                $author = null;
                if ($rs) {
                    $rev = $rs['revision'];
                    $title = $rs['title'];
                    $content = str_replace('\"','"',$rs['content']);
                    $author = User::getUser($rs['author']);
                    $edited = $rs['lastedit'];
                    $locked = false;
                } else {
                    $rev = 1;
                    $title = 'New page';
                    $content = 'This page has not yet been created or revision not found.';
                    $edited = null;
                    $author = null;
                    $locked = false;
                }
            } catch(DBXException $e) {
                    $rev = 1;
                    $title = 'ERROR';
                    $content = 'Error retrieving page.';
                    $locked = true;
            }
            $page = new WikiPage(array(
                'ns'           => $ns,        // The namespace of the page
                'uri'          => $uri,    // The URI of the page
                'revision'     => $rev,    // 1 is default revision
                'lastedit'     => $edited, // Date and time last edit
                'author'       => $author, // Author
                'authorname'   => $author->displayname,
                'title'        => $title,    // TODO: Set title here
                'content'      => $content,// TODO: Set content here
                'reverted'     => false,    // True if the requested revision has been reverted
                'locked'       => $locked,    // True if the page should not be editable
                'hidden'       => false    // True if the page is unlisted
            ));

            return $page;
        }

        function savePage(WikiPage $p) {
            $db = new DatabaseConnection();

            // Read values
            $ns =       $p->getNs();
            $uri =      $p->getUri();
            $title =    $p->getTitle();
            $content =  $p->getContent();
            $author =   User::getActiveUserId();

            // Update
            try {
                // pull the latest revision of the page
                $rs = $db->getSingleRow('SELECT MAX(revision) AS latest FROM wiki WHERE ns=\'%s\' AND uri=\'%s\'',$ns,$uri);
                $currev = ($rs)?$rs['latest']:0; // set to 0 if no record returned
                // bump revision (if no record, 0 becomes 1)
                $currev++;
                // and insert the new data
                $db->updateRow("INSERT INTO wiki SET content='%s',revision='%d',title='%s',ns='%s',uri='%s',lastedit=NOW(),author='%d'",$content,$currev,$title,$ns,$uri,$author);
            } catch(DBXException $e) {
                throw $e;
            }
        }

        /**
         * Update a wiki page
         * @param string $pagename The namespace and page name
         * @param string $title The page title
         * @param string $content The page content
         */
        function updatePage($pagename,$title,$content) {
            $ns = String::getNamespace('default',$pagename);
            $uri = String::getLocation($pagename);
            $db = new DatabaseConnection();
            $author = User::getActiveUserId();
            try {
                // pull the latest revision of the page
                $rs = $db->getSingleRow('SELECT MAX(revision) AS latest FROM wiki WHERE ns=\'%s\' AND uri=\'%s\'',$ns,$uri);
                $currev = ($rs)?$rs['latest']:0; // set to 0 if no record returned
                // bump revision
                $currev++;
                // and insert the new data
                $db->updateRow("INSERT INTO wiki SET content='%s',revision='%d',title='%s',ns='%s',uri='%s',lastedit=NOW(),author='%d'",$content,$currev,$title,$ns,$uri,$author);
            } catch(DBXException $e) {
                die($e);
            }
        }

        /**
         * Revert a wiki page to a previous revision
         * @param string $pagename The namespace and page name
         * @param integer $revision The revision to revert to
         * @param string $reason The reason for revertion [TODO]
         */
        function revertPage($pagename,$revision,$reason) {
            $ns = String::getNamespace('default',$pagename);
            $uri = String::getLocation($pagename);
            $db = DBX::getInstance(DBX);
            try {
                $db->updateRow("UPDATE wiki SET reverted=1 WHERE ns='%s' AND uri='%s' AND revision>'%d'",$ns,$uri,$revision);
            } catch(DBXException $e) {
                die($e);
            }
        }

    }

    class WikiPage extends AbstractModel {
        protected $_fields = array(
            'ns'         => 'string',
            'uri'         => 'string',
            'author'     => 'any',
            'authorname'=> 'any',
            'lastedit'  => 'any',
            'revision'    => 'int protected',
            'title'        => 'string',
            'content'    => 'string',
            'reverted'    => 'bool false',
            'locked'    => 'bool false',
            'hidden'    => 'bool false'
        );
        function getFullUri() {
            $ns = $this->getNs();
            $uri = $this->getUri();
            return ($ns.':'.$uri);
        }
    }

