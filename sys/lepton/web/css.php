<?php

/**
 * @brief Encapsulates a single CSS Rule with one or more attributes
 *
 * @license GNU GPL v3
 * @author Christopher Vagnetoft <noccy@chillat.net>
 *
 */
class CssRule {
    private $selector;
    private $attributes;
    /**
     * @brief Creates a new rule
     *
     * @param String $selector The CSS selector
     * @param Array $attributes The attributes to assign
     */
    function __construct($selector,$attributes) {
        $this->selector = $selector;
        $attrarr = array();
        foreach((array)$attributes as $attr=>$val) {
            $newattr = string::strip($attr, string::CHS_ALPHA);
            $attrarr[$attr] = $val;
        }
        $this->attributes = $attrarr;
    }
    /**
     * @brief Return the CSS code for the rule
     *
     * @return String The CSS code for the rule
     */
    function __toString() {
        $rules = array();
        foreach($this->attributes as $key=>$rule) {
            $rules[] = $key.':'.$rule.';';
        }
        $rulestr = '{'.join(',',$rules).'}';
        $ret = $this->selector.$rulestr;
        return $ret;
    }

    /**
     * @brief Assign a value to a CSS attribute
     *
     * @param String $key The attribute name
     * @param String $value The value to assign
     */
    function __set($key,$value) {
        $this->attributes[$key] = $value;
    }

    /**
     * @brief Retrieve a specific attribute value
     *
     * @param String $key The attributes key
     * @return String The attributes value
     */
    function __get($key) {
        return $this->attributes[$key];
    }

    /**
     * @brief Get the name of the selector
     *
     * @return String The name of the selector
     */
    function getSelector() {
        return $this->selector;
    }

    /**
     * @brief Update the name of the selector
     *
     * @param String $selector The new name of the selector
     */
    function setSelector($selector) {
        $this->selector = $selector;
    }
}

/**
 * @brief Encapsulates a complete CSS Stylesheet
 */
class CssStylesheet {
    private $rules = array();

    /**
     * @brief Output the entire stylesheet
     */
    function output() {
        response::contentType('text/css');
        echo $this->__toString();    
    }

    /**
     * @brief Add a rule to the stylesheet
     *
     * @param CssRule $rule The rule to add
     */
    function addRule(CssRule $rule) {
        $this->rules[$rule->getSelector()] = $rule;
    }

    /**
     * @brief Return the stylesheet
     *
     * @return String The stylesheet
     */
    function __toString() {
        $rules = array();
        foreach($this->rules as $rule) {
            $rules[] = (string)$rule;
        }
        return (string)join(' ',$rules);
    }
}

