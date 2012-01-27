<?

using('lepton.web.wizard');
using('lepton.web.url');

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
        $attrs = '';
        return sprintf('<div%s><label>%s</label><input type="text"></div>', $attrs, $this->label);
    }
    
}

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
    }
    
    public function render(array $meta = null) {
        switch($this->type) {
            case self::BUTTON_NEXT:
                return sprintf('<input type="submit" value="%s">', $this->text);
            case self::BUTTON_BACK:
                return sprintf('<input type="button" onclick="history.go(-1);" value="%s">', $this->text);
        }
    }
    
}

class WizardLabel extends WizardControl {

    private $text = null;
    
    public function __construct($text, Array $options = null) {
        $this->text = $text;
        $this->options = $options;
    }
    
    public function render(array $meta = null) {
        $attrs = '';
        if (arr::hasKey($this->options,'style')) $attrs.=sprintf(' style="%s"', $this->options['style']);
        if (arr::hasKey($this->options,'class')) $attrs.=sprintf(' class="%s"', $this->options['class']);
        return sprintf('<div%s>%s</div>', $attrs, $this->text);
    }
    
}

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

class WizardCombo extends WizardControl {
    
    private $label = null;
    private $items = array();
    
    public function __construct($label, Array $items = null, Array $options = null) {
        
        $this->label = $label;
        $this->items = $items;
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
        foreach($this->items as $value=>$text) {
            $out.= sprintf('<option value="%s">%s</option>', $value, $text);
        }
        $out.= sprintf('</select></div>');
        
        return $out;
        
    }
}

/**
 * @brief Horizontal display of items for toolbars etc.
 */
class WizardHPanel extends WizardLayoutControl {

    private $_items = array();
    private $_err = null;
/*
    function __construct(WizardError $err = null) {
        $this->_err = $err;
    }
*/
    /**
     * @brief Add an item to the HPanel
     * 
     * @param IWizardControl $item
     * @param array $options 
     */
    function addItem(IWizardControl $item, array $options = null) {
        $this->_items[] = $item;
    }

    function render(Array $options = null) {
        echo sprintf('<div style="overflow:hidden;">');
        if ($this->_err) {
            $err = $this->_err;
            $this->_err = null;
            $err->render($this);
        } else {
            foreach ($this->_items as $item) {
                echo sprintf('<div style="float:left; display:block">');
                    $item->render();
                echo sprintf('</div>');
            }
            echo sprintf('</div>');
        }
    }

}