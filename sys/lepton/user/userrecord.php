<?php

__fileinfo("User Classes");

using('lepton.crypto.uuid');

/**
 * @class UserRecord
 * @brief Contains a user record and passes on authentication credentials.
 *
 * Available authentication credential properties:
 *   username - The username
 *   password - The password (write only)
 * Available profile properties:
 *   userid - The unique user id
 *   uuid - The user's UUID
 *   displayname - The displayname of the user
 *   email - The users e-mail address
 *   website - The users website address
 *   registerdate - The date the user record was created (read only)
 *   lastlogindate - The date the user was last logged in (read only)
 *   lastloginip - The IP of the users last log in
 *   flags - The users flags
 * Available ambient properties:
 *   Any property can be used as an ambient property.
 */
class UserRecord {

    private $userid = null;
    private $username = null;
    private $password = null;
    private $email = null;
    private $flags = null;
    private $uuid = null;
    private $active = false;
    private $displayname = null;
    private $website = null;
    private $registered = null;
    private $lastlogin = null;
    private $lastip = null;
    private $properties = array();
    private $ambient = array();
    private $modified = array();
    private $extensions = array();

    /**
     * @brief Constructor, sets things up.
     *
     * @param int $userid An optional user id to load
     */
    function __construct($userid=null) {
        if ($userid) {
            $this->loadUser($userid);
        } else {
            $this->active = (!config::get('lepton.user.disabledbydefault', false));
        }
        $extn = getDescendants('UserExtension');
        foreach($extn as $extnclass) { 
        	$xc = new $extnclass($this);
		$xr = new ReflectionClass($xc);
        	$xm = $xr->getMethods();
        	$this->extensions[] = array(
			'name' => $extnclass,
        		'instance' => $xc,
        		'methods' => $xm
        	);
        }
    }

	function __call($method,$args) {
		foreach($this->extensions as $extension) {
			foreach($extension['methods'] as $cm) {
				if (strtolower($cm->name) == strtolower($method)) {
					return call_user_func_array(array($extension['instance'],$method), $args);
				}
			}
		}
		throw new BadArgumentException("No method ".$method." on UserRecord");
	}

    /**
     * @brief Destructor, saves the modified attributes if any.
     *
     * This method makes use of the $modified array to figure out what needs
     * to be saved to what tables.
     */
    function __destruct() {
        $this->save();
    }

    /**
     * @brief Load a user record from the database.
     *
     * @param int $userid The user ID
     */
    function loadUser($userid) {
        if (is_numeric($userid)) {
            $db = new DatabaseConnection();
            $record = $db->getSingleRow(
                            "SELECT a.*,u.*,a.id AS userid FROM " . LEPTON_DB_PREFIX . "users a LEFT JOIN " . LEPTON_DB_PREFIX . "userdata u ON a.id=u.id WHERE a.id=%d",
                            $userid
            );
            if ($record) {
                $this->assign($record);
            } else {
                throw new UserException("No such user ({$userid})");
            }
        } else {
            throw new BadArgumentException("User ID must be an integer");
        }
    }

    /**
     * @brief Assign the user data from a recordset row
     *
     * @param array $userrecord The recordset row containing the user data.
     */
    function assign($userrecord) {
        $this->userid = $userrecord['userid'];
        $this->username = $userrecord['username'];
        $this->email = $userrecord['email'];
        $this->uuid = $userrecord['uuid'];
        $this->flags = $userrecord['flags'];
        $this->ambient = unserialize($userrecord['ambient']);
        $this->displayname = $userrecord['displayname'];
        $this->active = ($userrecord['active'] == 1)?true:false;
        $this->registered = $userrecord['registered'];
        $this->lastlogin = $userrecord['lastlogin'];
        $this->lastip = $userrecord['lastip'];
    }

    public function hasFlag($flag) {
        return (strpos($this->flags,$flag)!==false);
    }

    public function save() {
        if (!$this->uuid) $this->uuid = uuid::v4();
        if (count($this->modified) > 0) {
            // Get a database reference
            $db = new DatabaseConnection();
            // Determine what needs to be updated.
            $mtable = array(
                'user' => false,
                'userdata' => false,
                'ambient' => false,
                'credentials' => false
            );
            foreach ($this->modified as $mod) {
                switch ($mod) {
                    case 'ambient' : $mtable['ambient'] = true;
                        break;
                    case 'username' : $mtable['user'] = true;
                        break;
                    case 'password' : $mtable['credentials'] = true;
                        break;
                    case 'email' : $mtable['user'] = true;
                        break;
                    case 'uuid' : $mtable['user'] = true;
                        break;
                    case 'active' : $mtable['user'] = true;
                        break;
                    case 'displayname' : $mtable['userdata'] = true;
                        break;
                    case 'firstname' : $mtable['userdata'] = true;
                        break;
                    case 'lastname' : $mtable['userdata'] = true;
                        break;
                    case 'sex' : $mtable['userdata'] = true;
                        break;
                    case 'country' : $mtable['userdata'] = true;
                        break;
                    case 'flags' : $mtable['user'] = true;
                        break;
                    case 'userid' : break;
                    default:
                        throw new BadArgumentException("Unknown field modified: {$mod}");
                }
            }
            
            if (!$this->userid) {
                // Check to see if the username already exists
                if (user::find($this->username)) {
                    throw new UserException("User already exists!");
                }
                // Insert
                $ambient = serialize($this->ambient);
                $this->userid = $db->insertRow(
                        "INSERT INTO " . LEPTON_DB_PREFIX . "users (username,email,uuid,flags,active,registered) VALUES " .
                        "(%s,%s,%s,%s,%d,NOW())",
                        $this->username, $this->email, $this->uuid, $this->flags, ($this->active)?1:0
                );
                $db->updateRow(
                        "INSERT INTO " . LEPTON_DB_PREFIX . "userdata (displayname,firstname,lastname,sex,country,ambient,id) VALUES " .
                        "(%s,%s,%s,%s,%s,%s,%d)",
                        $this->displayname, $this->firstname, $this->lastname, $this->sex,
                        $this->country, $ambient, $this->userid
                );
                // Update credentials
                $backend = User::getAuthenticationBackend();
                $backend->assignCredentials($this);
            } else {
                // Update
                if (($mtable['ambient']) && ($mtable['userdata'])) {
                    // Update complete userdata table
                    $ambient = serialize($this->ambient);
                    $db->updateRow(
                            "Update " . LEPTON_DB_PREFIX . "userdata SET displayname=%s,firstname=%s,lastname=%s,sex=%s,country=%s,ambient=%s WHERE id=%d)",
                            $this->displayname, $this->firstname, $this->lastname, $this->sex,
                            $this->country, $ambient, $this->userid
                    );
                } elseif ($mtable['ambient']) {
                    // Update the ambient column
                    $ambient = serialize($this->ambient);
                    $db->updateRow(
                            "UPDATE " . LEPTON_DB_PREFIX . "userdata SET ambient=%s WHERE id=%d ",
                            $ambient, $this->userid
                    );
                } elseif ($mtable['userdata']) {
                    // Update the userdata columns
                    $db->updateRow(
                            "UPDATE " . LEPTON_DB_PREFIX . "userdata SET displayname=%s,firstname=%s,lastname=%s,sex=%s,country=%s WHERE id=%d",
                            $this->displayname, $this->firstname, $this->lastname, $this->sex,
                            $this->country, $this->userid
                    );
                }
                if ($mtable['user']) {
                    // Update users table
                    $db->updateRow(
                            "UPDATE " . LEPTON_DB_PREFIX . "users SET username=%s,email=%s,uuid=%s,flags=%s,active=%s WHERE id=%d",
                            $this->username, $this->email, $this->uuid, $this->flags, ($this->active)?1:0,
                            $this->userid
                    );
                }
                if ($mtable['credentials']) {
                    // Update credentials
                    $backend = User::getAuthenticationBackend();
                    $backend->assignCredentials($this);
                }
            }
        }
        return true;
    }

    function applyFlags($value) {
        // TODO: This needs updating in the user table.
        $fn = str_replace('+','',$this->flags);
        $op = '+';
        for($n = 0; $n < strlen($value); $n++) {
            switch($value[$n]) {
                case '+': $op = '+'; break;
                case '-': $op = '-'; break;
                default:
                    if (($op == '+') && (strpos($fn,$value[$n]) === false)) {
                        $fn .= $value[$n];
                    } elseif (($op == '-') && (strpos($fn,$value[$n]) !== false)) {
                        $fn = str_replace($value[$n],'',$fn);
                    }
            }
        }
        $this->flags = $fn;
    }

    /**
     * @brief Set a user property.
     *
     * @param string $key The key to set
     * @param string $value The value to set
     */
    public function __set($key, $value) {
        switch ($key) {
            case 'lastlogin':
            case 'lastip':
            case 'uuid':
            case 'registered':
            case 'registerip':
                throw new UserException("Can't set protected property $key");
            case 'userid':
                if ($this->userid == null) {
                    $this->userid = $value;
                } else {
                    throw new UserException("Can't change assigned user id on created users");
                }
                break;
            case 'username':
                $this->username = $value;
                break;
            case 'email':
                $this->email = $value;
                break;
            case 'password':
                $this->password = $value;
                break;
            case 'active':
                $this->active = $value;
                break;
            case 'displayname':
                $this->displayname = $value;
                break;
            case 'flags':
                $this->flags = $value;
                break;
            default:
                $this->ambient[$key] = $value;
                break;
        }
        if (!in_array($key, $this->modified)) {
            $this->modified[] = $key;
        }
    }

    /**
     * @brief Retrieves a user property.
     *
     * @param string $key The key to get
     * @return mixed The property
     */
    public function __get($key) {
        switch ($key) {
            case 'userid':
                return $this->userid;
            case 'username':
                return $this->username;
            case 'email':
                return $this->email;
            case 'flags':
                return $this->flags;
            case 'active':
                return $this->active;
            case 'uuid':
                return $this->uuid;
            case 'registered':
                return $this->registered;
            case 'lastlogin':
                return $this->lastlogin;
            case 'lastip':
                return $this->lastip;
			case 'displayname':
				if (strlen($this->displayname) == 0) return $this->username;
				return $this->displayname;
            case 'password':
                if ($this->password == null) {
                    throw new UserException("Can't access protected property {$key}");
                } else {
                    return $this->password;
                }

            default:
                if (isset($this->ambient[$key])) {
                    return $this->ambient[$key];
                } else {
                    return null;
                }
        }
    }

}
