<?php __fileinfo("Form management", array(
    'depends' => array(
        'lepton.utils.tokenizer'
    )
));

/**
 * @class WebForm
 * @example formcontroller.php
 * @brief Validates forms posted from a browser or similar.
 *
 * Depends on the tokenizer class to parse the definitions. Allows validation
 * of strings, numbers, ip addresses, urls, netmasks, value ranges etc.
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 */
class WebForm {

    private $raw = array();
    private $parsed = array();
    private $fieldvalid = array();
    private $fields = null;
    private $formvalid = true;

    public function __construct($data = null) {
        if ($data) $this->validateForm($data);
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
     * @param string $data The field definition data
     * @return bool True if the form is valid
     */
    public function validateForm($data) {
        $toks = 'validate:1 between:1 and:1 above:1 below:1 netmask:1 '.
            'match:1 as:1 minlength:1 maxlength:1 required:0 default:1';
        $this->fields = $data;
        $this->valid = true;
        // Go over each of the expected form fields
        foreach((array)$data as $field => $attr) {
            if (isset($_REQUEST[$field])) {
                $this->raw[$field] = $_REQUEST[$field];
            } else {
                $this->raw[$field] = null;
            }
            $valid = true;
            $data = $this->raw[$field];

            $t = new Tokenizer($toks,$attr);
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
            // if (!$valid) { Console::warn('Form field %s failed validation', $field); }
            $this->fieldvalid[$field] = $valid;
            $this->parsed[$field] = $data;
            if (!$valid) $this->formvalid = false;
        }
        return $this->formvalid;
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
            if (isset($this->fieldvalid[$field])) {
                return $this->fieldvalid[$field];
            } else {
                return null;
            }
        }
    }

    /**
     * Retrieve a form field
     *
     * @param string $field The field to fetch
     * @return mixed The field vaue
     */
    public function __get($field) {
        if (isset($this->parsed[$field])) {
            return ($this->parsed[$field]);
        } else {
            return null;
        }
    }

    /**
     * Check if a form field is set
     *
     * @param string $field The field to query
     * @return bool True if set
     */
    public function __isset($field) {
        return (isset($this->collected[$field]));
    }

}

