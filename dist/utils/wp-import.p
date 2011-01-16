#!/usr/bin/php
<?php

require('sys/base.php');

class WpImportApplication extends ConsoleApplication {

    private $_xml = null;
    private $_xp = null;
    public $arguments = array(
        array('f:','file','File name to import from'),
        array('v','verbose','Be verbose'),
        array('p','posts','Import posts'),
        array('c','categories','Import categories'),
        array('t','tags','Import tags'),
        array('g','pages','Import pages'),
        array('a','all','Import everything (-pct)'),
        array('h','help','Show this help')
    );
    public $description = 'WordPress Data Importer';

    const XMLNS_EXCERPT = "http://wordpress.org/export/1.0/excerpt/";
    const XMLNS_CONTENT = "http://purl.org/rss/1.0/modules/content/";
    const XMLNS_WFW = "http://wellformedweb.org/CommentAPI/";
    const XMLNS_DC = "http://purl.org/dc/elements/1.1/";
    const XMLNS_WP = "http://wordpress.org/export/1.0/";

    public function main($argv,$argc) {
        if (!modulemanager::has('lepton.db.database')) {
            die("You need to configure your database connection first!\n");
        }

        if (!$this->hasArgument('f')) {
            console::error('You need to specify a file to import with -f');
            return 1;
        }

        // Check and open filename
        $filename = $this->getArgument('f');
        console::writeLn("Importing from %s ...",basename($filename));
        $this->_xml = DomDocument::load($filename);
        $this->_xp = new DomXpath($this->_xml);

        // Extracting categories
        console::write("Reading categories ");
        $chan = $this->_xp->query('/rss/channel/*');
        for($n = 0; $n < $chan->length; $n++) {
            if ($chan->item($n)->nodeName == 'wp:category') {
                $item = $chan->item($n);
                // wp:category_nicename, wp:category_parent, wp:cat_name
                $nicename = $item->getElementsByTagNameNS(self::XMLNS_WP,'category_nicename')->item(0)->nodeValue;
                $parent = $item->getElementsByTagNameNS(self::XMLNS_WP,'category_parent')->item(0)->nodeValue;
                $name = $item->getElementsByTagNameNS(self::XMLNS_WP,'cat_name')->item(0)->nodeValue;
                // console::writeLn("Cat: %s, Parent: %s, NiceName: %s",  $name,$parent,$nicename);
                console::write('.');
            }
        }
        console::writeLn('');

	console::write("Reading tags ");
        for($n = 0; $n < $chan->length; $n++) {
            if ($chan->item($n)->nodeName == 'wp:tag') {
                $item = $chan->item($n);
                $slug = $item->getElementsByTagNameNS(self::XMLNS_WP,'tag_slug')->item(0)->nodeValue;
                $tagname = $item->getElementsByTagNameNS(self::XMLNS_WP,'tag_name')->item(0)->nodeValue;
                // console::writeLn("Slug: %s, TagName: %s", $slug,$tagname);
                console::write('.');
            }
        }
        console::writeLn('');

        // wp:tag, wp:tag_slug, wp:tag_name

        console::write("Reading items ");
        for($n = 0; $n < $chan->length; $n++) {
            $item = $chan->item($n);
            if ($item->nodeName == 'item') {
                $cd = $item->getElementsByTagName("*");
                $pd = array();
                for($m = 0; $m < $cd->length; $m++) {
                    $mi = $cd->item($m);
                    switch($mi->nodeName) {
                        case 'title':
                        case 'link':
                        case 'pubDate':
                        case 'dc:creator':
                        case 'guid':
                            $pd[$mi->nodeName] = $mi->nodeValue;
                            console::writeLn('%s: %s', $mi->nodeName, $mi->nodeValue);
                            break;
                        case 'content:encoded':
                        case 'excerpt:encoded':
                            console::writeLn('%s: <%s>', $mi->nodeName, $mi->childNodes->item(0)->textContent); 
                            break;
                        default:
                            console::writeLn('*** Node: %s',$cd->item($m)->nodeName);
                    }
                }
/*


    title VARCHAR(255) NOT NULL,
    slug VARCHAR(64) UNIQUE NOT NULL,
    pubdate DATETIME NOT NULL,
    creator INT NOT NULL,
    guid VARCHAR(255) NOT NULL,
    uuid VARCHAR(40) NOT NULL,
    categoryid INT,
    excerpt TEXT,
    content TEXT,
    contenttype VARCHAR(64) NOT NULL DEFAULT 'text/html',
    commentstatus ENUM('closed','open') NOT NULL DEFAULT 'open',
    pingbackstatus ENUM('closed','open') NOT NULL DEFAULT 'open',
    poststatus ENUM('draft','published'),
    postmeta TEXT,
    sticky TINYINT(1) NOT NULL DEFAULT 0,
    hits INT NOT NULL DEFAULT 0
*/
die();
                console::writeLn('-----');
            }
        }

/*

*/

    }
}

lepton::run('WpImportApplication');
