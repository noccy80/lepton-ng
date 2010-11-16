<?php __fileinfo("Wiki implementation (MVC Component)");

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
	private $pagens = null;
	private $pagename = null;
	private $pagerevision = null;
	private $revisions = null;

	const KEY_DEFAULTPARSER = 'lepton.mvc.components.wiki.defaultparser';
	const DEF_DEFAULTPARSER = 'wiki';

	/**
	 * @brief Constructor, will load or create a Wiki page.
	 *
	 * @param String $page The page to load/create
	 */
	function __construct($page) {
		$pclass = config::get(self::KEY_DEFAULTPARSER, self::DEF_DEFAULTPARSER);
		$this->parser = markup::factory($pclass);
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
	 * 
	 *
	 *
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
			if ($pd['format'] != NULL) {
			
			}
			$this->data = $pd;
			return true;
		} else {
			return false;
		}
	}
	
	function saveWikiPage() {
	
	}
	
	function setMarkupParser($parser) {
		$this->parser = $parser;
	}
	
	function getMarkupParser() {
	
	}
	
	function __get($property) {
		switch($property) {
			case 'page':
				$this->parser->setData($this->pagedata);
				break;
			default:
				throw new BadPropertyException("No such property.");
		}
	}

}
