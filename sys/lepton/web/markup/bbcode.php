<?php module("Markup Parser: bbCode", array(
    'depends' => array(
        'lepton.web.markup'
    )
));

/**
 * @brief Handles bbCode markup
 *
 * @package lepton.web.markup
 * @author Christopher Vagnetoft <noccy@chillat.net>
 */
class BBCodeMarkupParser extends MarkupParser {
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
        self::OPT_OUTPUT_XHTML => false,
        markup::OPT_CONDENSE_LINES => true
    );

    /**
     * @brief Parse the bbcoded data and return html
     *
     * @param string $data The data to parse
     * @return string The parsed data
     */
    function parse($data) {
        $res = $data;
        $res = preg_replace('/\[b\](.*)\[\/b\]/i', '<font style="font-weight:bold;">$1</font>', $res);
        $res = preg_replace('/\[u\](.*)\[\/u\]/i', '<font style="text-decoration:underline;">$1</font>', $res);
        $res = preg_replace('/\[i\](.*)\[\/i\]/i', '<font style="font-style:italic;">$1</font>', $res);

        // Split on double newlines
        $res = explode((($this->getOption(markup::OPT_CONDENSE_LINES,true))?"\n\n":"\n"), $res);
        $out = array();
        // Replace the remaining newlines
        foreach($res as $resrow) {
            $out[] = str_replace("\n","", $resrow);
        }
        $res = '<p>'.join('</p><p>',$out).'</p>';
        return $res;
    }
}