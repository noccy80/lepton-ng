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
                $token = uniqid ('form',false);
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
     * @brief Sets the value of a specific form field in a wizardform instance.
     * 
     * You should create and add the appropriate field key as a part of your
     * form before setting the field. Also note that this method is a bit
     * heavier as it is intended to be used without an existing reference to
     * the form data. Therefore, every time a value is set the entire data-
     * set is retrieved and then updated.
     * 
     * @param string $formtoken The token, as found in the $meta or via getFormToken();
     * @param string $key The key to set
     * @param mixed $value The value to assign
     */
    static function setFormValue($formtoken,$key,$value) {

        // Make sure that the formplus dataset is present in the session
        if (!session::has('fp')) session::set('fp',array());
        $fpdata = session::get('fp');
        // Check if the specific form is present
        if (!arr::hasKey($fpdata,$formtoken)) {
            $fpdata[$formtoken] = array();
        }
        $fpdata[$formtoken][$key]['value'] = $value;
        session::set('fp',$fpdata);
        
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
        
        if (!$stepobj) return null;
        
        // Make sure that the formplus dataset is present in the session
        if (!session::has('fp')) session::set('fp',array());
        $fpdata = session::get('fp');
        // Check if the specific form is present
        if (!arr::hasKey($fpdata,$this->getFormToken())) {
            $fpdata[$this->getFormToken()] = array();
        }

        // Assign the metadata to pass to the rendering chain.
        $meta = array(
            'step' => $step,
            'token' => $this->getFormToken(),
            'steps' => $this->steps,
            'formdata' => $fpdata
        );
        
        // Resolve some special values before starting
        $action = $this->getOption('action',null);
        if ($action) $action = sprintf(' action="%s"', $action);
        $method = $this->getOption('method',null);
        $method = sprintf(' method="%s"', $method);

        $form = "";
        
		// Inject the navigation control scripts unless explicitly told not to
		// by the use of injectscripts=false.
        if ($this->getOption('injectscripts',true)) {
            $form.= sprintf('<script type="text/javascript">');
            $form.= sprintf('function fpGoPreviousStep() { $(\'wf_control\').value=\'-1\'; $(\''.$this->getFormToken().'\').submit(); }');
            $form.= sprintf('</script>');
        }

		// Create the form HTML
        $form.= sprintf('<form id="%s"%s>', $this->getFormToken(), $action.$method);
        $form.= sprintf('<input type="hidden" name="wf_formtoken" value="%s">', $this->getFormToken());
        $form.= sprintf('<input type="hidden" id="wf_control" name="wf_control" value="1">');
        $form.= $stepobj->render($meta);
        $form.= sprintf('</form>');

        // Return it
        return $form;
        
    }
    
    /**
     * @brief Receive posted form data.
     * 
     * This method will take care of inspecting the posted data to determine
     * what form it belongs to. 
     */
    public function receive() {

        if (!session::has('fp')) session::set('fp',array());
        $fpdata = session::get('fp');
        if (!arr::hasKey($fpdata,$this->getFormToken())) {
            $fpdata[$this->getFormToken()] = array();
        }
        $formdata = $fpdata[$this->getFormToken()];
        
        // Find the current step
        $step = $this->getOption('step',-1);
        if ($step >= 0) {
            $meta = array(
                'step' => $step,
                'token' => $this->getFormToken(),
                'steps' => $this->steps,
                'formdata' => $formdata
            );
            $ts = $this->steps[$step];

            // We call on the validate method to have the form do it's magic.
            $formdata = $ts['step']->validate($meta);
            $fp[$this->getFormToken()] = $formdata;
            session::set('fp', $fp);

            // debug::inspect($fp, false);

            $meta = request::get('wf_control',1)->toInt();
            if ($meta == 1) {
                $step = $step + 1;
            } else {
                $step = $step - 1;
            }
            $this->options['step'] = $step;
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
	 * @brief Shorthand for addStep.
	 * 
	 * @param string $key The key to assign
	 * @param string $name The name to assign
	 * @param array $options The option collection
	 * @return \WizardStep The newly created wizard step.
	 */
    public function createStep($key, $name, Array $options = null) {
       
        $ws = new WizardStep();
        $this->addStep($ws, $key, $name, $options);
        return $ws;
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
        $step->setForm($this);
        $step->initialize($this->getFormToken());
        
    }
    
    /**
     * @brief Return the unique form token for this form
     * 
     * @return string The form token
     */
    public function getFormToken() {
        return ($this->getOption('token'));
    }
    
    /**
     * @brief Check if the wizard has been completed.
     * 
     * @return boolean True if the wizard is complete.
     */
    public function getFormCompleted() {

        if (!session::has('fp')) session::set('fp',array());
        $fpdata = session::get('fp');
        if (!arr::hasKey($fpdata,$this->getFormToken())) {
            $fpdata[$this->getFormToken()] = array();
        }
        $formdata = $fpdata[$this->getFormToken()];
        
        foreach($formdata as $field) {
            if ($field['valid'] != true) return false;
        }

        // Find the current step
        $step = $this->getOption('step',0);
        $stepmax = count($this->steps[$step]) - 1;
        if ($step>$stepmaxj) return true;
        
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
    
	/**
	 * @brief Get the keys from each of the steps.
	 * 
	 * @return array The keys of each of the steps
	 */
    public function getStepKeys() {
        $ret = array();
        foreach($this->steps as $k=>$v) {
            $ret[] = $v['key'];
        }
        return $ret;
    }
    
    public function debug() {
        $debug = array(
            'Current step' => $this->getCurrentStepKey(),
            'Step keys' => $this->getStepKeys(),
            'Current step index' => $this->getCurrentStepIndex(),
            'Form was submitted' => ($this->getFormSubmitted()?'True':'False'),
            'Form was completed' => ($this->getFormCompleted()?'True':'False'),
            'Form token' => $this->getFormToken()
        );
        debug::inspect($debug,false);
    }
    
	/**
	 * @brief Return a field (property accessor)
	 * 
	 * @param string $field  The field
	 * @return mixed The field
	 */
    public function __get($field) {
        return $this->getField($field);
    }
    
	/**
	 * @brief Return a field
	 * 
	 * @param string $field  The field
	 * @return mixed The field
	 */
    public function getField($field) {
        if (!session::has('fp')) return null;
        $fpdata = session::get('fp');
        if (!arr::hasKey($fpdata,$this->getFormToken())) {
            return null;
        }
        $formdata = $fpdata[$this->getFormToken()];
        $val = $formdata[$field]['value'];

        return $val;
    }
    
}

/**
 * @brief Interface for a step in the wizard manager
 *  
 */
interface IWizardStep {
    public function validate(array $meta);
    public function addItem(IWizardControl $item);
    public function render(Array $meta = null);
    public function initialize($token);
}

/**
 * @brief Base class for a step in the wizard manager. 
 */
class WizardStep implements IWizardStep {
    protected $controls = array(); ///< @var Controls in the step
    protected $token = null; ///< @var The form token
    protected $wpf = null; ///< @var The base form

	/**
	 * 
	 * @todo Document
	 * @param WizardForm $form 
	 */
    public function setForm(WizardForm $form) {
        $this->wfp = $form;
    }

	/**
	 * 
	 * @todo Document
	 * $return WizardForm 
	 */
    public function getForm() {
        return $this->wfp;
    }
    
	/**
	 * 
	 * @todo Document
	 * @param string $token
	 */
    public function initialize($token) {
        $this->token = $token;
    }
    
	/**
	 *
	 * @todo Document
	 * @param array $meta
	 * @return type 
	 */
    public function validate(array $meta) {
        
        $formdata = $meta['formdata'];
        
        foreach($this->controls as $ctl) {
            $ci = $ctl['control'];
            if ($ci instanceOf WizardLayoutControl) {
                $formdata = $ci->validate($meta);
            } else {
                $key = $ci->getKey();
                if ($key) {
                    // Flag to detect changes. Defaults to true and is reset.
                    $formdata[$key]['changed'] = true;
                    
                    // Do the validation here
                    if (arr::hasKey($formdata,$key)) {

                        //if (request::has($key)) && ($formdata[$key]['value'] == (string)request::get($key))) {
                        // Not changed, so query previous state of validation

                        $posted = request::get($key)->toString();
                        $current = $formdata[$key]['value'];

                        if ($current != $posteddata) {
                            $ci->setValue($data);
                        
                            // Do validation
                            $formdata[$key]['value'] = $data;
                            $formdata[$key]['changed'] = false;
                            $formdata[$key]['valid'] = true;
                            if ($formdata[$key]['valid'] != true) {
                                if (is_callable(array($ci,'isValid'))) {
                                    $formdata[$key]['valid'] = (bool)$ci->isValid($formdata[$key]['value']);
                                } else {
                                    $formdata[$key]['valid'] = true;
                                }
                            }
                        } else {
                            $formdata[$key]['value'] = $posted;
                        }
                        // We give WizardCheckbox some special treatment as
                        // it comes out blank 
                        if ($ci instanceof WizardCheckbox) {
                            $formdata[$key]['value'] = $posted;
                        }
                    } else {

                        // Insert into array
                        $formdata[$key] = array(
                            'value' => (string)request::get($key),
                            'valid' => $formdata[$key]['valid'],
                            'changed' => true
                        );
                    }
                }
            }
        }    
        
        return($formdata);
    }

    /**
     * @brief Add an item to the step
     * 
     * @param IWizardControl $item The item to add
     */
    public function addItem(IWizardControl $item, Array $options = null) {
        $item->setForm($this->getForm());
        $this->controls[] = array(
            'control' => $item,
            'options' => (array)$options
        );
    }
    
	/**
	 *
	 * @todo Document
	 * @param array $meta
	 * @return type 
	 */
    public function render(Array $meta = null) {
        $ret = '';
        if (($meta) && (arr::hasKey($meta,'step'))) {
            $step = $meta['step'];
            $ret.= sprintf('<input type="hidden" name="wf_step" value="%d">', $step);
        }
        foreach($this->controls as $k=>$ctl) {
            if ($ctl['control']->getVisibility())
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
    protected $isvisible = true;
    protected $options = array();
    protected $defaults = array();
    public $key = null;
    protected $value = null;
    protected $wpf = null; ///< @var The base form
    
	/**
	 *
	 * @todo Document
	 * @param WizardForm $form 
	 */
    public function setForm(WizardForm $form) {
        $this->wfp = $form;
    }
    
	/**
	 * @todo Document
	 * @return type
	 */
    public function getForm() {
        return $this->wfp;
    }
    
	/**
	 * @todo Document
	 * @return type 
	 */
    public function getKey() {
        return $this->key;
    }
    
	/**
	 * @todo Document
	 * @param type $key 
	 */
    public function setKey($key) {
        $this->key = $key;
    }

	/**
	 * @todo Document
	 * @param type $value
	 */
    public function setValue($value) {
        $this->value = $value;
    }
    
	/**
	 * @todo Document
	 * @return type 
	 */
    public function getValue() {
        return $this->value;
    }

	/**
	 * @todo Document
	 * @param array $opts 
	 */
    public function __construct(Array $opts = null) {
        $this->options = (array)$opts;
        if (arr::hasKey($this->options,'key')) $this->setKey($this->options['key']);
    }
    
    /**
     * @brief Get the visibility of the control
     * @return bool The visibility as a boolean
     */
    public function getVisibility() {
        return $this->isvisible;
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
     * @brief Set the visiblity of the control
     * @param bool $visibility The new visibility state
     */
    public function setVisibility($visibility) {
        $this->isvisible = (bool)$visibility;
    }

    /**
     * Validate the form from the specification provided. The definition data
     * is an associative array having the field name as the key and the
     * definition as the value. The following syntax is used:
     *
     * validate {email|ip|int|bool[ean]|float}  - validate form
     *
     * between MIN and MAX  - valid if field between min and max
     *
     * above VALUE  - valid if field above value
     *
     * below VALUE  - valid if field below value
     *
     * netmask {IP/MASK}[;{IP/MASK}..]  - valid if ip in one of the masks
     *
     * match REGEX  - valid if regex match
     *
     * as OTHERFIELD  - valid if value is same as other field
     *
     * minlength LEN  - valid if longer than len
     *
     * maxlength LEN  - valid if shorter than len
     *
	 * @todo Rewrite documentation and clean function.
     * @param string $data The field definition data
     * @return bool True if the form is valid
     */
    static function validateField($data,$rules) {
        $toks = 'validate:1 between:1 and:1 above:1 below:1 netmask:1 '.
            'match:1 as:1 minlength:1 maxlength:1 required:0 default:1';
        $fields = $data;
        $valid = true;
        // Go over each of the expected form fields

        $t = new Tokenizer($toks,$rules);
        $ta = $t->getTokens();
        foreach($t as $tok=>$arg) {
            switch($tok) {
                case 'validate':
                    switch($arg) {
                        case 'email':
                            $valid = (filter_var($data, FILTER_VALIDATE_EMAIL));
                            break;
                        case 'ip':
                            $valid = (filter_var($data, FILTER_VALIDATE_IP));
                            break;
                        case 'int':
                            $valid = (filter_var($data, FILTER_VALIDATE_INT));
                            break;
                        case 'bool':
                        case 'boolean':
                            $valid = (filter_var($data, FILTER_VALIDATE_BOOL));
                            break;
                        case 'float':
                            $valid = (filter_var($data, FILTER_VALIDATE_FLOAT));
                            break;
                        case 'file':
                        // is a file?
                            break;
                        default:
                            throw new BaseException('Invalid validation type: '.$arg);
                    }
                    break;
                case 'between':
                // check if between $arg and ['and']
                    $min = $arg;
                    if (!isset($ta['and'])) {
                        throw new BaseException('Form field definition using "between" without "and"');
                    }
                    $max = $ta['and'];
                    if (($data < $min) || ($data > $max)) $valid = false;
                    break;
                case 'minlength':
                    if (strlen($data) < $arg) $valid = false;
                    break;
                case 'maxlength':
                    if (strlen($data) > $arg) $valid = false;
                    break;
                case 'above':
                    if ($data < $arg) $valid = false;
                    break;
                case 'below':
                    if ($data > $arg) $valid = false;
                    break;
                case 'netmask':
                    $s = explode(';',$arg);
                    // Match subnet x.x.x.x/xxx
                    $valid = false;
                    foreach($s as $net) {
                        list ($net, $mask) = explode ('/', $net);
                        if ((ip2long($data) & ~((1 << (32 - $mask)) - 1) ) == (ip2long($net) & ~((1 << (32 - $mask)) - 1))) {
                            $valid = true;
                            break;
                        }
                        /*
                        $netpart = explode('/',$net);
                        $ip = sprintf("%032b",ip2long($data)); 
                        $subnet = sprintf("%032b",ip2long($netpart[0])); 
                        if (substr_compare($ip,$subnet,0,$netpart[1]) === 0) {
                            $valid = true;
                            break;
                        }
                        */
                    }
                    break;
                case 'match':
                    $ret = preg_match($arg,$data);
                    if (!$ret) $valid = false;
                    break;
                case 'as':
                    if ($data != $this->raw[$arg]) $valid = false;
                    break;
                case 'required':
                    if ($data == null) $valid = false;
                    break;
                case 'default':
                    if ($data == null) $data = $arg;
                    break;
                default:
                    throw new BaseException("Invalid token.");
            }
        }

        return $valid;
    }

    /**
     * Check if a form field or the form as a whole is valid.
     *
     * @param string $field The field to query. If null, returns the state of
     *   the form.
     * @return bool True if set
     */
    public function isValid($field = null) {
        if ($field == null) {
            return $this->formvalid;
        } else {
            if (arr::hasKey($this->fieldvalid,$field)) {
                return $this->fieldvalid[$field];
            } else {
                return null;
            }
        }
    }    
    
}

/**
 * @brief Layout component. 
 */
abstract class WizardLayoutControl extends WizardControl {

    protected $_items = array();

	/**
	 * @todo Document
	 * @param array $meta
	 * @return type 
	 */
    public function validate(array $meta) {

        $formdata = $meta['formdata'];
        foreach($this->_items as $ci) {
            if (is_a($ci, WizardLayoutControl)) {
                $ci->validate($meta);
            } else {
                $key = $ci->getKey();
                $formdata[$key]['changed'] = true;
                // Do the validation here
                if (arr::hasKey($formdata,$key) && 
                    (request::has($key)) &&
                    ($formdata[$key]['value'] == (string)request::get($key))) {
                    // Not changed, so query previous state of validation
                    $formdata[$key]['changed'] = false;
                    if ($formdata[$key]['valid'] != true) {
                        // Do validation
                    }
                } else {
                    // Insert into array
                    $formdata[$key] = array(
                        'value' => (string)request::get($key),
                        'valid' => $formdata[$key]['valid']
                    );
                }
            }
        }
        return($formdata);
        
    }    
    
}

using('lepton.web.wizard.basic');
using('lepton.web.wizard.layout');