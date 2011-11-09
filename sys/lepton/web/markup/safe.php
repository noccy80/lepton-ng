<?php module("Markup Parser: Safe", array(
    'depends' => array(
        'lepton.web.markup'
    )
));

/**
 * Wiki Markup parser/renderer for Lepton/PHP
 *
 * This class parses wiki markup into rich markup format text.
 *
 * @license Gnu GPL v2+
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @since 0.3
 */
class SafehtmlMarkupParser extends MarkupParser {

    /// If set to true, links including protocols will have the rel="nofollow" attribute set
    const OPT_EXTERNAL_NOFOLLOW = 'nofollow';
    /// An array containing the protocols that are allowed
    const OPT_ALLOWED_PROTOCOLS = 'protocols';
    /// If true, the output will be formatted as xhtml
    const OPT_OUTPUT_XHTML = 'xhtml';
    static $defaults = array(
        self::OPT_EXTERNAL_NOFOLLOW => true,
        self::OPT_ALLOWED_PROTOCOLS => array(
            'http',
            'https',
            'ftp'
        ),
        self::OPT_OUTPUT_XHTML => false
    );

    /**
     * @brief Parse the human data and return html
     *
     * @param string $data The data to parse
     * @return string The parsed data
     */
    public function parse($content) {
        $ret = new StringParser($content);
        $ret->replace(array('/\r\n?/' => '\n', '/&/' => '&amp;', '/</' => '&lt;', '/>/' => '&gt;' ));
        $ret->replace('/\n?&lt;blockquote&gt;\n*(.+?)\n*&lt;\/blockquote&gt;/',
                      '<blockquote>$1</blockquote>');
        $ret->replaceEach(array('b','i','em','strong','u'),
                          '/&lt;($ITEM$)&gt;(.+?)&lt;\/($ITEM$)&gt;/','<$1>$2</$1>');
        $ret->replace('/&lt;a.+?href\s*=\s*[\'"](.+?)["\'].*?&gt;(.+?)&lt;\/a&gt;/',
                      '<a href="$1">$2</a>');
        $ret->replace('/\n\n+/', "</p>\n\n<p>");
        $ret->replace('/([^\n]\n)(?=[^\n])/', '\1<br />');
        return "<p>".$ret->get()."</p>";
    }

    function strip($data) { return null; }
    
}

