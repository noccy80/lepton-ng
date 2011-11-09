<?php module("Markup Parser: Wiki", array(
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
class WikiMarkupParser extends MarkupParser {
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
     * @brief Parse the wiki data and return html
     *
     * @param string $data The data to parse
     * @return string The parsed data
     */
    public function parse($content) {
        $str = $content;
        
        $str = string::rereplace($str,"/^(.*?)[\r{2}]?$/m",'<p>$1</p>');
        $str = string::rereplace($str,"/'''(.*?)'''/",'<b>$1</b>');
        $str = string::rereplace($str,"/''(.*?)''/",'<i>$1</i>');
        $str = string::rereplace($str,"/<p>==== (.*?) ====<\/p>/",$this->getTag('h',4));
        $str = string::rereplace($str,"/<p>=== (.*?) ===<\/p>/",$this->getTag('h',3));
        $str = string::rereplace($str,"/<p>== (.*?) ==<\/p>/",$this->getTag('h',2));
        $str = string::rereplace($str,"/<p>= (.*?) =<\/p>/",$this->getTag('h',1));
        $str = string::rereplace($str,"/\[mailto:(.*?)\s(.*?)\]/",'<a class="mailto-link" href="mailto:$1">$2</a>');
        $str = string::rereplace($str,"/\[([http:\/\/|https:\/\/]?)(.*?)\s(.*?)\]/",'<a class="external-link" href="$1$2">$3</a>');
        $str = string::rereplace($str,"/\[\/(.*?)\s(.*?)\]/",'<a href="/$1">$2</a>');
        /*
        $str = string::rereplace($str,"/\[\[embed:(.*?)\]\]/i", new Callback(MarkupUtil,'embed'));
        $str = string::rereplace($str,"/\[\[video:(.*?)\]\]/i", new Callback(MarkupUtil,'video'));
        */
        $str = string::rereplace($str,"/\[\[image:(.*?)\]\]/i",'<img src="$1"></img>');
        $str = string::rereplace($str,"/<nowiki>(.*)<\/nowiki>/",'$1');
        
        return $str;
    }

    function strip($data) { return null; }
    
}

