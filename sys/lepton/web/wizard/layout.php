<?

using('lepton.web.wizard');
using('lepton.web.url');


/**
 * @brief Horizontal display of items for toolbars etc.
 */
class WizardHPanel extends WizardLayoutControl {

    private $_err = null;

    function __construct(Array $options=null) {
        parent::__construct($options);
    }
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

        $attrs = '';
        $cssclass = $this->getOption('class',null);
        $cssclass = 'fp -hpanel'.($cssclass?' '.$cssclass:'');
        $cssstyle = $this->getOption('style',null);
        $attrs.=sprintf(' class="%s"', $cssclass);
        if ($cssstyle) $attrs.=sprintf(' style="%s"', $cssstyle);
        $ret = sprintf('<div %s>',$attrs);
        foreach ($this->_items as $item) {
            $ret.= sprintf('<div style="float:left; display:block">');
            $ret.= $item->render($meta);
            $ret.= sprintf('</div>');
        }
        $ret.=sprintf('</div>');
        return $ret;
        
    }

}

/**
 * @brief Vertical display of items for toolbars etc.
 */
class WizardVPanel extends WizardLayoutControl {

    private $_err = null;
    
    function __construct(Array $options=null) {
        parent::__construct($options);
    }
    
    /**
     * @brief Add an item to the VPanel
     * 
     * @param IWizardControl $item
     * @param array $options 
     */
    function addItem(IWizardControl $item, array $options = null) {
        $this->_items[] = $item;
    }

    function render(Array $meta = null) {
        $attrs = '';
        $cssclass = $this->getOption('class',null);
        $cssclass = 'fp -vpanel'.($cssclass?' '.$cssclass:'');
        $cssstyle = $this->getOption('style',null);
        $attrs.=sprintf(' class="%s"', $cssclass);
        if ($cssstyle) $attrs.=sprintf(' style="%s"', $cssstyle);
        $ret = sprintf('<div %s>',$attrs);
        foreach ($this->_items as $item) {
            $ret.= $item->render($meta);
        }
        $ret.=sprintf('</div>');
        return $ret;
    }

}