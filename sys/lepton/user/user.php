<?php __fileinfo("User Classes");

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
    private $displayname = null;
    private $website = null;
    private $registerdate = null;
    private $lastlogindate = null;
    private $lastloginip = null;

    private $properties = array();
    private $ambient = array();
    private $modified = array();

    /**
     * @brief Constructor, sets things up.
     *
     * @param int $userid An optional user id to load
     */
    function __construct($userid=null) {

        if ($userid) {
            $this->loadUser($userid);
        }

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
                    "SELECT a.*,u.*,a.id AS userid FROM ".LEPTON_DB_PREFIX."users a LEFT JOIN ".LEPTON_DB_PREFIX."userdata u ON a.id=u.id WHERE a.id=%d",
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
        $this->ambient = unserialize($userrecord['ambient']);
        $this->displayname = $userrecord['displayname'];
    }

    public function save() {

        if (count($this->modified) > 0) {
            // Get a database reference
            $db = new DatabaseConnection();
            // Determine what needs to be updated.
            $mtable = array(
                'user' => false,
                'userdata' => false,
                'ambient' => false,
                'credentials' => true
            );
            foreach($this->modified as $mod) {
                switch($mod) {
                    case 'ambient'     : $mtable['ambient'] = true; break;
                    case 'username'    : $mtable['user'] = true; break;
                    case 'password'    : $mtable['credentials'] = true; break;
                    case 'email'       : $mtable['user'] = true; break;
                    case 'uuid'        : $mtable['user'] = true; break;
                    case 'active'      : $mtable['user'] = true; break;
                    case 'displayname' : $mtable['userdata'] = true; break;
                    case 'firstname'   : $mtable['userdata'] = true; break;
                    case 'lastname'    : $mtable['userdata'] = true; break;
                    case 'sex'         : $mtable['userdata'] = true; break;
                    case 'country'     : $mtable['userdata'] = true; break;
                    case 'userid'      : break;
                    default:
                        throw new BadArgumentException("Unknown field modified: {$mod}");
                }
            }
            if (($mtable['ambient']) && ($mtable['userdata'])) {
                // Update complete userdata table
                $ambient = serialize($this->ambient);
                $db->updateRow(
                    "REPLACE INTO ".LEPTON_DB_PREFIX."users (displayname,firstname,lastname,sex,country,ambient,id) VALUES ".
                    "(%s,%s,%s,%s,%s,%s,%d)",
                    $this->displayname, $this->firstname, $this->lastname, $this->sex,
                    $this->country, $ambient, $this->userid
                );
            } elseif ($mtable['ambient']) {
                // Update the ambient column
                $ambient = serialize($this->ambient);
                $db->updateRow(
                    "REPLACE INTO ".LEPTON_DB_PREFIX."users (ambient,id) VALUES ".
                    "(%s,%s,%s,%s,%s,%s,%d)",
                    $ambient, $this->userid
                );
            } elseif ($mtable['userdata']) {
                // Update the userdata columns
                $db->updateRow(
                    "REPLACE INTO ".LEPTON_DB_PREFIX."users (displayname,firstname,lastname,sex,country,id) VALUES ".
                    "(%s,%s,%s,%s,%s,%s,%d)",
                    $this->displayname, $this->firstname, $this->lastname, $this->sex,
                    $this->country, $this->userid
                );
            }
            if ($mtable['credentials']) {
                // Update credentials
                $backend = User::getAuthenticationBackend();
                $backend->assignCredentials($this);
            }
            if ($mtable['user']) {
                // Update users table
                $db->updateRow(
                    "REPLACE INTO ".LEPTON_DB_PREFIX."users (username,email,uuid,active,id) VALUES ".
                    "(%s,%s,%s,%d,%d)",
                    $this->username, $this->email, $this->uuid, $this->active,
                    $this->userid
                );
            }

        }

    }

    /**
     * @brief Set a user property.
     *
     * @param string $key The key to set
     * @param string $value The value to set
     */
    public function __set($key,$value) {
        switch($key) {
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
            case 'flags':
            // TODO: This needs updating in the user table.
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
        switch($key) {
            case 'userid':
                return $this->userid;
            case 'username':
                return $this->username;
            case 'email':
                return $this->email;
            case 'flags':
                return $this->flags;

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
