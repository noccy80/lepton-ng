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
	 * Constructor
	 *
	 * @param int $userid An optional user id to load
	 */
	function __construct($userid=null) {

		if ($userid) {
			$this->loadUser($userid);
		}

	}

	/**
	 * Load a user record from the database.
	 *
	 * @param int $userid The user ID
	 */
	function loadUser($userid) {
		$db = new DatabaseConnection();
		$record = $db->getSingleRow(
				"SELECT a.*,u.* FROM ".LEPTON_DB_PREFIX."users a LEFT JOIN ".LEPTON_DB_PREFIX."userdata u ON a.id=u.id WHERE a.id=%d",
				$userid
		);
		if ($record) {
			$this->userid = $userid;
		} else {
			throw new UserException("No such user ({$userid})");
		}
	}

	/**
	 * Set a user property.
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
	 * Retrieves a user property.
	 *
	 * @param string $key The key to get
	 * @return mixed
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
