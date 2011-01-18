#!/usr/bin/php
<?php

require('sys/base.php');

using('lepton.crypto.uuid');

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
        array('a','all','Import everything (-pctg)'),
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
        $db = new DatabaseConnection();

        if (!$this->hasArgument('f')) {
            console::error('You need to specify a file to import with -f');
            return 1;
        }

        // Check and open filename
        $filename = $this->getArgument('f');
        console::writeLn("Importing from %s ...",basename($filename));
        $this->_xml = DomDocument::load($filename);
        $this->_xp = new DomXpath($this->_xml);

        $catdata = array();
        $tagdata = array();

        // Extracting categories
        if ($this->hasArgument('c') || $this->hasArgument('a')) {
            console::write("Reading categories: ");
            $count = 0;
            $chan = $this->_xp->query('/rss/channel/*');
            for($n = 0; $n < $chan->length; $n++) {
                if ($chan->item($n)->nodeName == 'wp:category') {
                    $item = $chan->item($n);
                    // wp:category_nicename, wp:category_parent, wp:cat_name
                    $nicename = $item->getElementsByTagNameNS(self::XMLNS_WP,'category_nicename')->item(0)->nodeValue;
                    $parent = $item->getElementsByTagNameNS(self::XMLNS_WP,'category_parent')->item(0)->nodeValue;
                    $name = $item->getElementsByTagNameNS(self::XMLNS_WP,'cat_name')->item(0)->nodeValue;
                    // console::writeLn("Cat: %s, Parent: %s, NiceName: %s",  $name,$parent,$nicename);
                    $db->updateRow("REPLACE INTO blogcategories (parent,slug,category) VALUES (0,%s,%s)", $nicename, $name);
                    $count++;
                }
            }
            console::writeLn('%d categories imported', $count);
        }

        if ($this->hasArgument('t') || $this->hasArgument('a')) {
            console::write("Reading tags: ");
            $count = 0;
            for($n = 0; $n < $chan->length; $n++) {
                if ($chan->item($n)->nodeName == 'wp:tag') {
                    $item = $chan->item($n);
                    $slug = $item->getElementsByTagNameNS(self::XMLNS_WP,'tag_slug')->item(0)->nodeValue;
                    $tagname = $item->getElementsByTagNameNS(self::XMLNS_WP,'tag_name')->item(0)->nodeValue;
                    // console::writeLn("Slug: %s, TagName: %s", $slug,$tagname);
                    $db->updateRow("REPLACE INTO blogtags (slug,tag) VALUES (%s,%s)", $slug, $tagname);
                    $count++;
                }
            }
            console::writeLn('%d tags imported',$count);
        }

        $catdataraw = $db->getRows("SELECT * FROM blogcategories");
        foreach($catdataraw as $cat) $catdata[$cat['slug']] = $cat['id'];

        $tagdataraw = $db->getRows("SELECT * FROM blogtags");
        foreach($tagdataraw as $tag) $tagdata[$tag['slug']] = $tag['id'];
        // wp:tag, wp:tag_slug, wp:tag_name

        if ($this->hasArgument('p') || $this->hasArgument('a')) {
            console::write("Reading items: ");
            $count = 0;
            for($n = 0; $n < $chan->length; $n++) {
                $item = $chan->item($n);
                if ($item->nodeName == 'item') {
                    $cd = $item->getElementsByTagName("*");
                    $pd = array(
                        'uuid' => uuid::v4()
                    );
                    for($m = 0; $m < $cd->length; $m++) {
                        $mi = $cd->item($m);
                        $mv = $mi->nodeValue;
                        switch($mi->nodeName) {
                            case 'title':
                            case 'link':
                            case 'pubDate':
                            case 'dc:creator':
                            case 'guid':
                            case 'wp:post_name':
                            case 'wp:post_type':
                            case 'content:encoded':
                            case 'excerpt:encoded':
                            case 'wp:post_date_gmt':
                            case 'wp:comment_status':
                            case 'wp:is_sticky':
                            case 'wp:ping_status':
                            case 'wp:status':
                                $pd[$mi->nodeName] = $mv;
                                break;
                            case 'category':
                                $dom = $mi->getAttribute('domain');
                                $nicename = $mi->getAttribute('nicename');
                                $cvalname = $mi->nodeValue;
                                if ($dom && $nicename) {
                                    switch($dom) {
                                        case 'tag':
                                            $pd['tags'][] = $tagdata[$nicename];
                                            break;
                                        case 'category':
                                            $pd['category'] = $catdata[$nicename];
                                            break;
                                    }
                                }
                                break;
                            default:
                                // console::writeLn('[warning: %s not imported] %s',$cd->item($m)->nodeName, $mv);
                        }
                    }
                    if ($pd['wp:post_name'] == null) $pd['wp:post_name'] = string::slug($pd['title']);
                    if ($pd['wp:post_type'] == 'post') {
                        try {
                            $id = $db->insertRow("INSERT INTO blogposts (title,slug,pubdate,".
                                    "creator,guid,uuid,categoryid,content,contenttype,".
                                    "commentstatus,pingbackstatus,poststatus,sticky) ".
                                    "VALUES (%s,%s,%s,".
                                    "%d,%s,%s,%d,%s,%s,".
                                    "%s,%s,%s,0)", $pd['title'], $pd['wp:post_name'], $pd['wp:post_date_gmt'],
                                    1, $pd['uuid'], $pd['uuid'], $pd['category'], $pd['content:encoded'], 'text/html',
                                    $pd['wp:comment_status'], $pd['wp:ping_status'], $pd['wp:status'], 0);
                            if (isset($pd['tags'])) { foreach($pd['tags'] as $tag) {
                                $db->insertRow("INSERT INTO blogposttags (postid,tagid) VALUES (%d,%d)", $id, $tag);
                            }}
                        } catch(Exception $e) {
                            console::writeLn("Failed to import post %s: %s", $pd['title'], $e->getMessage());
                        }
                        $count++;
                    }
                }
            }
            console::writeLn("%d posts imported", $count);
        }
    }
}

lepton::run('WpImportApplication');
