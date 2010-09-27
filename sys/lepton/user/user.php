<?php __fileinfo("User Classes");

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
	
	private $ambient = array();

	/**
	 * Constructor
	 *
	 */
	function __construct($userid=null) {

		if ($userid) {
			$this->loadUser($userid);
		}

	}

	/**
	 * Load a user record from the database.
	 *
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
	}
	
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
