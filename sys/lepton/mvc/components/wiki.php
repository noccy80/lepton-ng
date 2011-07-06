<?php module("Wiki implementation (MVC Component)");

using('lepton.web.markup');
using('lepton.web.markup.*');

/**
 * @class WikiPage
 * @brief Manage wiki pages
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 */
class WikiPage {

    private $modified = false;
    private $markup = null;
    private $data = null;
    private $content = null;
    private $pagens = null;
    private $pagename = null;
    private $pagetitle = null;
    private $pagerevision = null;
    private $revisions = array();
    private $parser = null; ///< Parser object reference

    const KEY_DEFAULTPARSER = 'lepton.mvc.components.wiki.defaultparser';
    const DEF_DEFAULTPARSER = 'wiki';

    /**
     * @brief Constructor, will load or create a Wiki page.
     *
     * @param String $page The page to load/create
     */
    function __construct($page) {
        $this->markup = config::get(self::KEY_DEFAULTPARSER, self::DEF_DEFAULTPARSER);
        $this->parser = markup::factory($this->markup);
        $this->loadWikiPage($page,null);
    }

    /**
     * @brief Destructor
     *
     */
    function __destruct() {
        if ($this->modified) $this->saveWikiPage();
    }

    /**
     * @brief Split a wiki Uri into a namespace and a page name.
     *
     * @param String $uri The URI to parse
     * @param String $defaultns The default namespace to use if any
     * @return Array An array holding the namespace and page name
     */
    function splitUri($uri,$defaultns='wiki') {
        $uc = explode(':',$uri);
        if (count($uc) > 0) {
            return array_slice($uc,0,2);
        } else {
            return array($defaultns,$uc[0]);
        }
    }

    /**
     * @brief Loads a wiki page
     *
     * Will attempt to load a wiki page from the database. If revision is not
     * specified, the latest revision will be loaded.
     *
     * @param String $page
     * @param Int $revision
     * @return Boolean True on success, false otherwise
     */
    function loadWikiPage($page, $revision=null) {
        $db = new DatabaseConnection();
        list($ns,$name) = $this->splitUri($page,'wiki');
        if (!$revision) {
            $pd = $db->getSingleRow(
                "SELECT * FROM wikipages WHERE pagens=%s AND pagename=%s ".
                "ORDER BY revision DESC LIMIT 1;", $ns, $name
            );
        } else {
            $pd = $db->getSingleRow(
                "SELECT * FROM wikipages WHERE pagens=%s AND pagename=%s AND ".
                "revision<%d ORDER BY revision DESC LIMIT 1;",
                $ns, $name, $revision
            );
        }
        if ($pd) {
            $revs = $db->getSingleRow(
                "SELECT revision FROM wikipages WHERE pagens=%s AND pagename=%s",
                $ns, $name
            );
            foreach($revs as $rev) {
                $this->revisions[] = $rev['revision'];
            }
            if ($pd['format'] != NULL) {
                $this->markup = $pd['markuptype'];
                $this->parser = markup::factory($this->markup);
            }
            $this->content = $pd['content'];
            $this->pagerevision = $pd['revision'];
            $this->modified = false;
            $this->pagens = $pd['pagens'];
            $this->pagename = $pd['pagename'];
            $this->pagetitle = $pd['pagetitle'];
            return true;
        } else {
            return false;
        }
    }

    function saveWikiPage() {

    }

    /**
     * @brief Assigns a new markup parser to the page
     *
     * @param MarkupParser $parser The parser to assign
     */
    function setMarkupParser($parser) {
        $this->parser = $parser;
    }

    /**
     * @brief Returns the currently assigned markup parser
     *
     * @return MarkupParser The markup parser in use
     */
    function getMarkupParser() {
        return $this->parser;
    }

    /**
     * @brief Return a property value
     *
     * @param String $property The property to query
     * @return Mixed The property value
     */
    function __get($property) {
        switch($property) {
            case 'html':
                return $this->parser->parse($this->content);
                break;
            case 'content':
                return $this->content;
                break;
            default:
                throw new BadPropertyException("No such property.");
        }
    }

}
