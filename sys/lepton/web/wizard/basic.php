<?

using('lepton.web.wizard');

class WizardTextbox extends WizardControl {
    
    private $text = null;
    
    public function __construct($text,$id,array $options=null) {
        
        // Save the main settings
        $this->text = $text;
        
        // Populate some settings
        if (!$options) $options = array();
        $options['key'] = $id;
        
        // Call the parent constructor with the whole lot
        parent::__construct($options);
    }
    
    public function render(array $meta = null) {
        return sprintf('<input type="text">');
    }
    
}

class WizardButton extends WizardControl {
    
    const BUTTON_NORMAL = 0;
    const BUTTON_NEXT = 1;
    const BUTTON_BACK = 2;
    
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
        }
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