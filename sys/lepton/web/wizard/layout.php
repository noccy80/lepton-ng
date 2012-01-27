<?

using('lepton.web.wizard');
using('lepton.web.url');


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