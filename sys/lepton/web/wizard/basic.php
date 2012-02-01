<?

using('lepton.web.wizard');
using('lepton.web.url');


/**
 * @brief
 *  
 * @author Christopher Vagnetoft
 */
class WizardHidden extends WizardControl {
    
    private $key = null;
    private $value = null;
    
    public function __construct($key, $value) {
        
        // Save the main settings
        $this->key = $key;
        $this->value = $value;
        
        // Call the parent constructor with the whole lot
        parent::__construct();
    }
    
    public function render(array $meta = null) {
        $attrs = '';
        return sprintf('<input type="hidden" name="%s" value="%s">', $this->key, $this->value);
    }    
}


/**
 * @brief
 *  
 * @author Christopher Vagnetoft
 */
class WizardTextbox extends WizardControl {
    
    private $label = null;
    
    public function __construct($label,$id,array $options=null) {
        
        // Save the main settings
        $this->label = $label;
        
        // Populate some settings
        if (!$options) $options = array();
        $options['key'] = $id;
        
        // Call the parent constructor with the whole lot
        parent::__construct($options);
    }
    
    public function render(array $meta = null) {
        if ($this->getOption('password',false) == true) {
            $type = 'password';
        } else {
            $type = 'text';
        }
        $attrs = '';
        if ($this->getOption('autoselect',true) == true) {
            $attrs.= ' onmouseup="this.focus(); this.select();"';
        }
        $dattrs = ' class="fp -formrow"';
        return sprintf('<div%s><label class="fp">%s</label><input class="fp" type="%s"%s></div>', $dattrs, $this->label, $type, $attrs);
    }
    
}


/**
 * @brief
 *  
 * @author Christopher Vagnetoft
 */
class WizardButton extends WizardControl {
    
    const BUTTON_NORMAL = 0;
    const BUTTON_NEXT = 1;
    const BUTTON_BACK = 2;
    const BUTTON_CANCEL = 3;
    
    private $type = self::BUTTON_NORMAL;
    private $text = null;

    public function __construct($text, $type = self::BUTTON_NORMAL, Array $options = null) {
        $this->type = (int)$type;
        $this->text = $text;
        parent::__construct($options);
    }
    
    public function render(array $meta = null) {
        if ($this->getOption('enabled',true) != true) {
            $disabled = ' disabled="disabled"';
        } else {
            $disabled = '';
        }
        $attrs = ''.$disabled;
        switch($this->type) {
            case self::BUTTON_NEXT:
                return sprintf('<input class="fp" type="submit" value="%s"%s>', $this->text, $attrs);
            case self::BUTTON_BACK:
                return sprintf('<input class="fp" type="button" onclick="history.go(-1);" value="%s"%s>', $this->text, $attrs);
            case self::BUTTON_NORMAL:
                return sprintf('<input class="fp" type="button" value="%s"%s>', $this->text, $attrs);
        }
    }
    
}


/**
 * @brief
 *  
 * @author Christopher Vagnetoft
 */
class WizardLabel extends WizardControl {

    private $text = null;
    
    public function __construct($text, Array $options = null) {
        $this->text = $text;
        $this->options = $options;
    }
    
    public function render(array $meta = null) {
        $attrs = '';
        $cssclass = 'fp -label';
        if (arr::hasKey($this->options,'style')) $attrs.=sprintf(' style="%s"', $this->options['style']);
        if (arr::hasKey($this->options,'class')) {
            $attrs.=sprintf(' class="%s %s"', $cssclass, $this->options['class']);
        } else {
            $attrs.=sprintf(' class="%s"', $cssclass);
        }
        return sprintf('<div%s>%s</div>', $attrs, $this->text);
    }
    
}


/**
 * @brief
 *  
 * @author Christopher Vagnetoft
 */
class WizardIframe extends WizardControl {

    private $src = null;
    
    public function __construct($src, Array $options = null) {
        $this->src = $src;
        $this->options = $options;
    }
    
    public function render(array $meta = null) {
        $attrs = '';
        $url = url($this->src);
        $url->setParameter('token', $meta['token']);
        if (arr::hasKey($this->options,'class')) $attrs.=sprintf(' class="%s"', $this->options['class']);
        if (arr::hasKey($this->options,'style')) $attrs.=sprintf(' style="%s"', $this->options['style']);
        return sprintf('<iframe src="%s" %s></iframe>', (string)$url, $attrs);
    }
    
}


/**
 * @brief Drop-down combo box for Wizard.
 * 
 * This class encapsulates a select form element complete with one ore more
 * option values, passed through the $items constructor argument or populated
 * with the addComboItem() method.
 * 
 * The constructor can handle arrays of arrays, i.e. record sets from a database
 * query. When passing data like this, the first column will become the value
 * and the second column will be the text. The rest will be discarded.
 * 
 * Style options are: style, labelstyle, combostyle
 * Class options are: class, labelclass, comboclass
 * 
 * @author Christopher Vagnetoft
 */
class WizardCombo extends WizardControl {
    
    private $label = null;
    private $items = array();
    
    public function __construct($label, Array $items = null, Array $options = null) {
        
        $this->label = $label;
        // Here we will do a little check to see if the array we are passed is
        // indexed with a key ( $k => $v ), or if it's simply an array of arrays
        // i.e. a recordset from a database query. If this is the case, we will
        // go over the list and massage it to be indexed with a key.

        if (count($items)>0) {
            if (typeOf($items[0]) == 'array') {
                $newarr = array();
                // Repopulate the list with keys properly assigned
                foreach($items as $item) {
                    $newarr[$item[0]] = $item[1];
                }
                // And update the item list with our newly populated one
                $items = $newarr;
            }
            // Now we update the main list
            $this->items = $items;
        }
        $this->options = arr::defaults($options, array(
            'class' => 'wf-row'
        ));
        
    }
    
    public function addComboItem($key,$value) {
        
        $this->items[$key] = $value;
        
    }
    
    public function render(array $meta = null) {
        
        // Attribute sets
        $lattrs = ''; $attrs = ''; $sattrs = '';
        
        // Check the options for the various styles and classes
        if (arr::hasKey($this->options,'class')) $attrs.=sprintf(' class="%s"', $this->options['class']);
        if (arr::hasKey($this->options,'style')) $attrs.=sprintf(' style="%s"', $this->options['style']);
        if (arr::hasKey($this->options,'labelclass')) $lattrs.=sprintf(' class="%s"', $this->options['class']);
        if (arr::hasKey($this->options,'labelstyle')) $lattrs.=sprintf(' style="%s"', $this->options['style']);
        if (arr::hasKey($this->options,'comboclass')) $sattrs.=sprintf(' class="%s"', $this->options['class']);
        if (arr::hasKey($this->options,'combostyle')) $sattrs.=sprintf(' style="%s"', $this->options['style']);

        // Render the control
        $out = sprintf('<div%s><label%s>%s</label><select%s>', $attrs, $lattrs, $this->label, $sattrs);
        $current = $this->getOption('current',null);
        foreach($this->items as $value=>$text) {
            $attrs = '';
            if ($current == $value) $attrs.=' selected="selected"';
            $out.= sprintf('<option%s value="%s">%s</option>', $attrs, $value, $text);
        }
        $out.= sprintf('</select></div>');
        
        return $out;
        
    }
}
