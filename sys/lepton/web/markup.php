<?php __fileinfo("Markup Parser Core",
    array(
        'version' => '1.0'
    )
);

/**
 * @brief Interface for markup parsers.
 * Normally inherited via the abstract base class MarkupParser.
 *
 * @package lepton.web.markup
 * @see MarkupParser
 * @author Christopher Vagnetoft <noccy@chillat.net>
 */
interface IMarkupParser {
    /**
     * @brief Parse the data and return the output
     * @param string $data The data to parse
     */
    function parse($data);
}

/**
 * @brief Abstract base class for markup parsers
 *
 * @package lepton.web.markup
 * @see IMarkupParser
 */
abstract class MarkupParser implements IMarkupParser {
    protected $data = null;
    protected $options = null;
    protected $parsed = null;

    /**
     * @brief Markup parser constructor
     *
     * @see MarkupParser::setData
     * @see MarkupParser::getOptions
     * @see MarkupParser::getOption
     * @param string $string The markup text to process
     * @param array $options The options to apply
     */
    function __construct($string=null,array $options=null) {
        $this->data = $string;
        $this->options = (($options==null)?array():(array)$options);
        $this->parsed = $this->parse($this->data);
    }

    /**
     * @brief String casting method.
     *
     * @see MarkupParser::getParsed
     * @return string The parsed markup
     */
    function __toString() {
        return $this->getParsed();
    }

    /**
     * @brief Assign the data and options to the parser.
     *
     * @param string $string The string to parse
     * @param array $options The options to assign to the parser.
     */
    function setData($string,array $options=null) {
        $this->data = $string;
        if ($options) {
            $this->options = (($options==null)?array():(array)$options);
        }
        $this->parsed = $this->parse($this->data);
    }

    /**
     * @brief Return the data to be parsed.
     *
     * @return string The data assigned.
     */
    function getData() {
        return $this->data;
    }

    /**
     * @brief Returns the applied options.
     *
     * @return array The options
     */
    function getOptions() {
        return $this->options;
    }

    /**
     * @brief Return the parsed data
     *
     * @return string The parsed data
     */
    function getParsed() {
        return $this->parsed;
    }

    /**
     * @brief Return an option set in the constructor or using setData
     *
     * @see MarkupParser::setData
     * @param string $option The option to query
     * @param mixed $default Default value to return if not present
     * @return mixed The option value or the default value
     */
    function getOption($option,$default=null) {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        } else {
            return $default;
        }
    }

}

abstract class Markup {

    /**
     * @brief Factory method to create a parser and feed it relevant data.
     *
     * @param string $parser The parser to create, ex. "bbcode"
     * @param string $string The string to parse
     * @param array $options The options to assign to the parser
     * @return MarkupParser The parser instance
     */
    static function factory($parser,$string='',$options=null) {
        $parserclass = $parser.'MarkupParser';
        if (class_exists($parserclass)) {
            return new $parserclass($string,$options);
        }
        throw new ClassNotFoundException("Could not find markup parser");
    }
}

