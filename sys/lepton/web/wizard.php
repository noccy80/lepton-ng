<?php

interface IWizardForm {
    
}

/**
 * @brief Wizard/Guide implementation, main class.
 * 
 * Create an instance of this class to create forms that are rendered for you,
 * as well as validated and collected. Once the guide indicates that it is
 * done you will be handed all the posted data in one go. You can of course
 * peek at the data and information while the guide is running by using the
 * getStep() method to get the step and then getData() to retrieve the data.
 *  
 */
class WizardForm implements IWizardForm {

    protected $steps = array();    ///< @var Steps in the form
    protected $options = array();  ///< @var Options for the form
    protected $currentstep = 0;    ///< @var The current step
    
    /**
     * @brief Constructor.
     * 
     * If you include your own constructor, you MUST call on parent::construct()
     * with the expected data or things will go boom.
     * 
     * @param string $token The token of the form (from getFormToken())
     * @param string $url The target URL to post to, or same if blank.
     * @param array $options The options as an array
     */
    public function __construct($token = null, $url = null, Array $options = null) {

        if (!$token) {
            if (request::has('wf_formtoken')) {
                $token = request::get('wf_formtoken')->toString();                
            } else {
                $token = uniqid ('form',true);
            }
        }
            
        // These are the defaults we will use
        $defaults = array(
            'method' => 'post',
            'target' => null,
            'token' => $token,
            'url' => $url,
        );
        if (request::has('wf_step')) {
            $options['step'] = request::get('wf_step')->toString();
        }
        
        // Apply the defaults to the options and save
        $this->options = arr::defaults($options, $defaults);
    }
    
    /**
     * @brief Returns the HTML for the current step of the form.
     * 
     * @return string The HTML code to render. 
     */
    public function render() {
        
        // Find the current step
        $step = $this->getOption('step',0);
        $stepinfo = $this->steps[$step];
        $stepobj = $stepinfo['step']; 
        
        $meta = array(
            'step' => $step
        );
        
        $action = $this->getOption('action',null);
        if ($action) $action = sprintf(' action="%s"', $action);
        $method = $this->getOption('method',null);
        $method = sprintf(' method="%s"', $method);

        $form = sprintf('<form%s>', $action.$method);
        $form.= sprintf('<input type="hidden" name="wf_formtoken" value="%s">', $this->getFormToken());
        $form.= $stepobj->render($meta);
        $form.= sprintf('</form>');
        
        return $form;
        
    }
    
    /**
     * @brief Receive posted form data.
     * 
     * This method will take care of inspecting the posted data to determine
     * what form it belongs to. 
     */
    public function receive() {
        
        // Find the current step
        $step = $this->getOption('step',-1);
        if ($step >= 0) {
            $ts = $this->steps[$step];

            // We call on the validate method to have the form do it's magic.
            // $ts['step']->validate();
        }
    }

    /**
     * @brief Get an option value
     * 
     * @param string $key The key to query
     * @param mixed $default Default value if not set (defaults to null)
     * @return mixed The option (or $default)
     */
    protected function getOption($key,$default=null) {
        if (arr::hasKey($this->options,$key)) {
            return $this->options[$key];
        } else {
            return $default;
        }
    }
    
    /**
     * @brief Add a step to the wizard.
     * 
     * @param IWizardStep $step The step as a IWizardStep instance
     * @param string $key The key of the step (f.ex. 'basic')
     * @param string $name The name of the step (f.ex. 'Basic Information')
     * @param array $options Options for the step
     */
    public function addStep(IWizardStep $step, $key, $name, Array $options = null) {
        
        // These are the defaults we will use
        $defaults = array(
            'title' => $name, // The title of the step
            'novalidate' => false, // If true the form will not be validated
        );
        
        // Apply the defaults to the options
        $options = arr::defaults($options, $defaults);
        // And add the step with the new options attached
        $this->steps[] = array(
            'step' => $step,
            'key' => $key,
            'name' => $name,
            'options' => $options
        );
        
        // Now go over the data for the step to see if it has already been
        // validated and saved. We do this with the initialize method.
        $step->initialize($this->getFormToken());
        
    }
    
    /**
     * @brief Return the unique form token for this form
     * 
 
     */
    public function getFormToken() {
        return ("magictoken");
    }
    
    /**
     * @brief Check if the wizard has been completed.
     * 
     * @return boolean True if the wizard is complete.
     */
    public function getFormCompleted() {
        return false;
    }
    
    /**
     * @brief Check if the form was submitted
     * @return boolean True if the form was submitted, False otherwise.
     */
    public function getFormSubmitted() {
        return ($this->getOption('step',null) != null);
    }
    
    /**
     * @brief Return the index of the current step.
     * 
     * @return int The step index
     */
    public function getCurrentStepIndex() {
        $index = $this->getOption('step',0);
        return (int)$index;
    }
    
    /**
     * @brief Return the specified key for the current step.
     * 
     * @return string The step key
     */
    public function getCurrentStepKey() {
        $index = $this->getCurrentStepIndex();
        $form = $this->steps[$index];
        return $form['key'];
        
    }
    
    public function getStepKeys() {
        $ret = array();
        foreach($this->steps as $k=>$v) {
            $ret[] = $v['key'];
        }
        return $ret;
    }
}

/**
 * @brief Interface for a step in the wizard manager
 *  
 */
interface IWizardStep {
    public function validate();
    public function addItem(IWizardControl $item);
    public function render(Array $meta = null);
}

/**
 * @brief Base class for a step in the wizard manager. 
 */
class WizardStep implements IWizardStep {
    protected $controls = array(); ///< @var Controls in the step
    public function initialize() { }
    public function validate() { }

    /**
     * @brief Add an item to the step
     * 
     * @param IWizardControl $item The item to add
     */
    public function addItem(IWizardControl $item, Array $options = null) {
        $this->controls[] = array(
            'control' => $item,
            'options' => (array)$options
        );
    }
    
    public function render(Array $meta = null) {
        $ret = '';
        if (($meta) && (arr::hasKey($meta,'step'))) {
            $step = $meta['step'];
            $ret.= sprintf('<input type="hidden" name="wf_step" value="%d">', $step);
        }
        foreach($this->controls as $k=>$ctl) {
            $ret.= $ctl['control']->render($meta);
        }
        return $ret;
    }
   
}

/**
 * @brief Interface for a Control in the Wizard manager 
 * 
 */
interface IWizardControl {
    public function render(Array $meta = null);
}

/**
 * @brief Base class for A Control in the Wizard Manager
 * 
 */
abstract class WizardControl implements IWizardControl {
    protected $isvisible = false;
    protected $options = array();
    protected $defaults = array();
    
    public function __construct(Array $options = null) {
        $this->options = arr::defaults($options, $this->defaults);
    }
    
    /**
     * @brief Get the visibility of the control
     * @return bool The visibility as a boolean
     */
    public function getVisibility() {
        return $this->isvisible;
    }
    
    /**
     * @brief Set the visiblity of the control
     * @param bool $visibility The new visibility state
     */
    public function setVisibility($visibility) {
        $this->isvisible = (bool)$visibility;
    }
    
    public function getKey() {
        return ($this->options['key']);
    }
    
}

/**
 * @brief Layout component. 
 */
abstract class WizardLayoutControl extends WizardControl {
    
}

using('lepton.web.wizard.basic');