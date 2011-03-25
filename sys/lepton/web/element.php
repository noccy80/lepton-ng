<?php

class Element {

    private $_elem = null;
    private $_args = array();
    private $_content = null;
    // TODO: Extend
    const contentelem = '|p|div|a|em|i|u|script|';

    public static function __callstatic($name,$args) {
        $el = new Element($elem,$args[0],$args[1]);
        $el->setContent($content);
        return $el;
    }
    
    function __construct($elem,array $args=null) {
        $this->_elem = $elem;
        $this->_args = (array)$args;
    }
    
    function appendChild(Element $elem) {
        if (!is_array($this->_content)) {
            $this->_content = array();
            // TODO: Should really warn about the content being replaced as
            // long as it was not null.
        }
        $this->_content[] = $elem;
        
    }
    
    function setContent($content) {
        $this->_content = $content;
    }
    
    function __toString() {
        $attrl = array();
        foreach($attr as $key=>$val) {
            $attrl[] = sprintf('%s="%s"', $key, htmlentities($val));
        }
        if (is_array($this->_content)) {
            $contl = array();
            foreach($this->_content as $node) {
                $contl[] = (string)$node;
            }
            $conts = join('',$contl);
        } elseif ($this->_content != null) {
            $conts = $this->_content;
        } else {
            $conts = '';
        }
        if (strpos('|'.$this->_elem.'|',self::contentelem) > 0) {
            $node = sprintf('<%s %s>%s</%s>', $this->_elem, $attrl, $conts);
        } else {
            $node = sprintf('<%s %s>', $this->_elem, $attrl);
        }
    }

}
