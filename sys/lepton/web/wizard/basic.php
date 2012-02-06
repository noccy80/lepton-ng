<?

using('lepton.web.wizard');
using('lepton.web.url');


/**
 * @brief Encapsulates a input type=hidden form field.
 * 
 * This type is not visible and thus does not support any styling.
 *  
 * @author Christopher Vagnetoft
 */
class WizardHidden extends WizardControl {
    
    private $value = null;
    
    /**
     * @brief Constructor.
     * 
     * @param string $key The name of the input field
     * @param string $value The value
     */
    public function __construct($key, $value) {
        
        // Save the main settings
        $this->key = $key;
        $this->value = $value;
        
        // Call the parent constructor with the whole lot
        parent::__construct();
    }
    
    /**
     * Renders the wizard element
     * 
     * @param array $meta Meta data to use when rendering
     * @return string The rendered control
     */
    public function render(array $meta = null) {
        $attrs = '';
        return sprintf('<input type="hidden" name="%s" value="%s">', $this->key, $this->value);
    }    
}


/**
 * @brief Encapsulates a one-line text box (input type=text)
 * 
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
        $this->setKey($id);
        
        // Call the parent constructor with the whole lot
        $this->options = $options;
    }
    
    public function isValid($value) {
        
        $validation = $this->getOption('validation',null);
        if (!$validation) return true;
        
        $ok = WizardControl::validateField($value, $validation);
        printf('<p>In validator as <b>%s</b> with value"<em>%s</em>" = %d</p>', $validation, $value, $ok);
        return $ok;
    }
    
    /**
     * Renders the wizard element
     * 
     * @param array $meta Meta data to use when rendering
     * @return string The rendered control
     */
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
        $attrs = '';
        $cssclass = $this->getOption('class',null);
        $cssclass = 'fp'.($cssclass?' '.$cssclass:'');
        $cssstyle = $this->getOption('style',null);
        $attrs.=sprintf(' class="%s"', $cssclass);
        if ($cssstyle) $attrs.=sprintf(' style="%s"', $cssstyle);
        $key = $this->getKey();
        $formtoken = $meta['token'];
        $curform = $meta['formdata'];
        $curform = $curform[$formtoken];
        if (arr::hasKey($meta['formdata'],$formtoken)) {
            $value = $meta['formdata'][$formtoken][$this->getKey()]['value'];
        } else {
            $value = null;
        }
        return sprintf('<div%s><label for="%s" class="fp">%s</label><input id="%s" name="%s" class="fp" type="%s" value="%s"%s></div>', $dattrs, $key, $this->label, $key, $key, $type, $value, $attrs);
    }
    
}

/**
 * @brief Encapsulates a multi line text area (textarea)
 *  
 * @author Christopher Vagnetoft
 */
class WizardTextArea extends WizardControl {
    
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
    
    /**
     * Renders the wizard element
     * 
     * @param array $meta Meta data to use when rendering
     * @return string The rendered control
     */
    public function render(array $meta = null) {
        $attrs = '';
        if ($this->getOption('autoselect',true) == true) {
            $attrs.= ' onmouseup="this.focus(); this.select();"';
        }
        $dattrs = ' class="fp -formrow"';
        $attrs = '';
        $cssclass = $this->getOption('class',null);
        $cssclass = 'fp'.($cssclass?' '.$cssclass:'');
        $cssstyle = $this->getOption('style',null);
        $attrs.=sprintf(' class="%s"', $cssclass);
        if ($cssstyle) $attrs.=sprintf(' style="%s"', $cssstyle);
        return sprintf('<div%s><label class="fp">%s</label><textarea class="fp"%s></textarea></div>', $dattrs, $this->label, $attrs);
    }
    
}


/**
 * @brief Encapsulates a button (input type=button/submit/cancel)
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
    
    /**
     * Renders the wizard element
     * 
     * @param array $meta Meta data to use when rendering
     * @return string The rendered control
     */
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
                return sprintf('<input class="fp" type="button" onclick="fpGoPreviousStep();" value="%s"%s>', $this->text, $attrs);
            case self::BUTTON_NORMAL:
                return sprintf('<input class="fp" type="button" value="%s"%s>', $this->text, $attrs);
        }
    }
    
}

/**
 * @brief Encapsulates a toolbar consisting of buttons
 *  
 */
class WizardButtonBar extends WizardControl {

    private $_buttons = array();
    private $_name = null;

    function __construct($name=null) {

        $this->_name = $name;
    }

    function addButton($name, $label, $type='button', $style='', $onclick=null) {

        if ($type=='button') { $btype = WizardButton::BUTTON_NORMAL; }
        if ($type=='back') { $btype = WizardButton::BUTTON_BACK; }
        if ($type=='next') { $btype = WizardButton::BUTTON_NEXT; }
        if ($type=='submit') { $btype = WizardButton::BUTTON_NEXT; }
        $this->_buttons[] = new WizardButton($label, $btype, array(
                'onclick' => $onclick,
                'style' => $style,
                'name' => $name,
                'onclick' => $onclick
        ));
        
    }

    /**
     * Renders the wizard element
     * 
     * @param array $meta Meta data to use when rendering
     * @return string The rendered control
     */
    function render(Array $meta = null) {

        $ret = '<div style="overflow:hidden;">';
        foreach ($this->_buttons as $button) {
            $ret.= sprintf('<div style="float:left; display:block">');
            $ret.= $button->render($meta);
            $ret.= sprintf('</div>');
        }
        $ret.=sprintf('</div>');
        return $ret;
        
        echo sprintf('<p class="wizard-buttonbar">');
        foreach ($this->_buttons as $button) {
            if ($button['onclick']) {
                $onclick = sprintf('onclick="%s"', $button['onclick']);
            } else {
                $onclick = '';
            }
            echo sprintf('<input type="%s" name="%s" id="wb-%s" value="%s" style="%s" %s>', $button['type'], $button['name'], $button['name'], $button['label'], $button['style'], $onclick);
        }
        echo sprintf('</p>');
    }

}

/**
 * @brief Encapsulates a textual label.
 * 
 * This control has got nothing to do with the html label element.
 *  
 * @author Christopher Vagnetoft
 */
class WizardLabel extends WizardControl {

    private $text = null;
    
    public function __construct($text, Array $options = null) {
        $this->text = $text;
        $this->options = $options;
    }
    
    /**
     * Renders the wizard element
     * 
     * @param array $meta Meta data to use when rendering
     * @return string The rendered control
     */
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
 * @brief Encapsulates an iframe. 
 * 
 * The form token will be added to the request URL of the iframe as the query
 * string argument "token".
 * 
 * The control can be styled with the "class" and "style" options.
 *  
 * @author Christopher Vagnetoft
 */
class WizardIframe extends WizardControl {

    private $src = null;
    
    public function __construct($src, Array $options = null) {
        $this->src = $src;
        $this->options = $options;
    }
    
    /**
     * Renders the wizard element
     * 
     * @param array $meta Meta data to use when rendering
     * @return string The rendered control
     */
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
    
    public function __construct($label, $key, Array $items = null, Array $options = null) {
        
        $this->label = $label;
        $this->setKey($key);
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
    
    /**
     * Renders the wizard element
     * 
     * @param array $meta Meta data to use when rendering
     * @return string The rendered control
     */
    public function render(array $meta = null) {
        
        // Attribute sets
        $lattrs = ''; $attrs = ''; $sattrs = '';
        $cssclass = null; $dcssclass = null;
        
        // Check the options for the various styles and classes
        if (arr::hasKey($this->options,'style')) $attrs.=sprintf(' style="%s"', $this->options['style']);
        $dcssclass = $this->getOption('class',null);
        $attrs.=sprintf(' class="%s"', 'fp -formrow'.($dcssclass?' '.$dcssclass:''));

        $lcssclass = $this->getOption('labelclass',null);
        $lattrs.=sprintf(' class="%s"', 'fp'.($lcssclass?' '.$lcssclass:''));

        if (arr::hasKey($this->options,'labelstyle')) $lattrs.=sprintf(' style="%s"', $this->options['labelstyle']);
        if (arr::hasKey($this->options,'comboclass')) $cssclass = $this->getOption('class');
        $sattrs.=sprintf(' class="%s"', 'fp -dropdown'.($cssclass?' '.$cssclass:''));
        if (arr::hasKey($this->options,'combostyle')) $sattrs.=sprintf(' style="%s"', $this->options['style']);

        // Render the control
        $key = $this->getKey();
        $out = sprintf('<div%s><label%s>%s</label><select name="%s" id="%s"%s>', $attrs, $lattrs, $this->label, $key, $key, $sattrs);
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

class WizardVisualSteps extends WizardControl {
    
    private $src = null;
    
    public function __construct(Array $options = null) {
        parent::__construct($options);
    }
    
    /**
     * Renders the wizard element
     * 
     * @param array $meta Meta data to use when rendering
     * @return string The rendered control
     */
    public function render(array $meta = null) {

        $stepsarr = array();
        if (arr::hasKey($meta,'steps')) {
            foreach($meta['steps'] as $step) {
                $stepname = $step['name'];
                $stepsarr[] = $stepname;
            }
        }
        
        $idx = 0;
        $step = $meta['step'];
        foreach($stepsarr as $key=>$val) {
            $sc1 = '1';
            $sc2 = '1';
            if ($idx == $step - 1) { $sc2 = '2'; }
            if ($idx == $step) { $sc1 = '2'; }
            // Last step, should have nothing right
            if ($idx == count($this->_array) - 1) { $sc2 = '0'; $sc1 = '0'; }
            $stepcode = '-wizardstep-'.$sc1.$sc2;
            if ($key == $step) {
                $buttons.= sprintf('<div class="current %s">%s</div>', $stepcode, $val);
            } else {
                $buttons.= sprintf('<div class="%s">%s</div>', $stepcode, $val);
            }
            $idx++;
        }

        return sprintf('<div class="wizard-steps" style="background-color:#F0F0F0; padding:3px;">%s</div>', $buttons);        
        
    }
    
}

class WizardCaptcha extends WizardControl {
    
    private $captchaid = null;
    
    function __construct($label,$key,array $options = null) {
        using('lepton.web.captcha');
        $this->setKey($key);
        $this->label = $label;
        parent::__construct($options);
        $this->captchaid = Captcha::generate();
    }
    
    function render(array $meta = null) {
        
        
        $attrs = '';
        if ($this->getOption('autoselect',true) == true) {
            $attrs.= ' onmouseup="this.focus(); this.select();"';
        }
        $dattrs = ' class="fp -formrow"';
        $attrs = '';
        $cssclass = $this->getOption('class',null);
        $cssclass = 'fp'.($cssclass?' '.$cssclass:'');
        $cssstyle = $this->getOption('style','width:80px;');
        $attrs.=sprintf(' class="%s"', $cssclass);
        if ($cssstyle) $attrs.=sprintf(' style="%s"', $cssstyle);
        $key = $this->getKey();
        $formtoken = $meta['token'];
        $curform = $meta['formdata'];
        $curform = $curform[$formtoken];
        if (arr::hasKey($meta['formdata'],$formtoken)) {
            $value = $meta['formdata'][$formtoken][$this->getKey()]['value'];
        } else {
            $value = null;
        }
        $captchaurl = $this->getOption('captchaurl','/meta/captcha?cid={cid}');
        $captchaurl = str_replace('{cid}',$this->captchaid, $captchaurl);

        $str = sprintf('<div%s>', $dattrs);
        $str.= sprintf('<label for="%s" class="fp">%s</label>', $key, $this->label);
        $str.= sprintf('<img src="%s"><br>', $captchaurl);
        $str.= sprintf('<input id="%s_cid" name="%s_cid" class="fp" type="hidden" value="%s">', $key, $key, $this->captchaid);
        $str.= sprintf('<input id="%s" name="%s" class="fp" type="text" %s>', $key, $key, $attrs);
        $str.= sprintf('</div>');
        return $str;
        
    }
    
}