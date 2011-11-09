<?php module("Markup Parser: Human", array(
    'depends' => array(
        'lepton.web.markup'
    )
));

/**
 * Human Markup parser/renderer for Lepton/PHP
 *
 * This class parses wiki markup into rich markup format text.
 *
 * @license Gnu GPL v2+
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @since 0.3
 */
class HumanMarkupParser extends MarkupParser {
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
        $str = $content;
    
        $str = string::rereplace($str,'/</', '&lt;');
        $str = string::rereplace($str,'/>/', '&gt;');
        $str = string::rereplace($str,'/\\\"/', '&quot;');
        // First chunk everything into paragraphs...
        $str = string::rereplace($str,'/(.+)/', '<p>\1</p>');
        // Then take care of multiple newlines
        $str = string::rereplace($str,'/(\r|\n){2}/', '<br>');

        // The usual, bold, italics, etc...
        $str = string::rereplace($str,'/\/(.*?)\//', '<em>\1</em>');
        $str = string::rereplace($str,'/\*(.*?)\*/', '<strong>\1</strong>');
        $str = string::rereplace($str,'/_(.*?)_/', '<u>\1</u>');

        $tag_re = '/\#([a-zA-Z0-9]+)/s';
        $group_re = '/\!([a-zA-Z0-9]+)/s';

        /*
        preg_match_all($tag_re, $content, $tags);
        preg_match_all($group_re, $content, $groups);

        $this->setMeta(MarkupParser::META_TAGS, $tags[1]);
        $this->setMeta(MarkupParser::META_GROUPS, $groups[1]);
        */

        $str = string::rereplace($str,$tag_re,'<a href="/tags/\1">#\1</a>');
        $str = string::rereplace($str,$group_re,'<a href="/groups/\1">!\1</a>');
        $str = string::rereplace($str,'/\@([a-zA-Z0-9]+)/s','<a href="/user/\1">@\1</a>');

        // A bit of a hack...but it works... let me know if you have a regex that fixes this :p
        $str = string::rereplace($str,'/<strong><\/strong>/', '**');
        $str = string::rereplace($str,'/<em><\/em>/', '//');
        $str = string::rereplace($str,'/<u><\/u>/', '__');

        $str = string::rereplace($str,'/(https?:\/\/\S+)/', '<a href="\1" rel="nofollow">\1</a>');
        return $str;
        
    }
    
    function strip($data) { return null; }
    
}

