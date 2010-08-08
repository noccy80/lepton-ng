<?php


	class Uuid {
		const UUID_V1 = 1;
		const UUID_V2 = 2;
		const UUID_V3 = 3;
		const UUID_V4 = 4;
		const UUID_V5 = 5;
		const LENGTH = 36; ///< length of a UUID

		static $urand = null;
		static $uobject = null;
		private $version = null;
		protected $uuid;

		public function __construct($version = Uuid::UUID_V4) {
			$this->uuid = Uuid::generate($version);
			$this->version = $version;
		}

		/**
		 * @internal Initializes the urand or uuid resource
		 *
		 *
		 */
		static function initialize() {
			if (function_exists('uuid_create')) {
				if (! is_resource ( Uuid::$uobject )) {
					uuid_create ( Uuid::$uobject );
				}
			} else {
				Uuid::$urand = @fopen ( '/dev/urandom', 'rb' );
			}
		}

		function update() {
			$this->uuid = Uuid::generate($this->version);
			return $this->uuid;
		}

		/**
		 * Return a type 1 (MAC address and time based) uuid
		 *
		 * @return String
		 */
		public static function v1() {
			Uuid::initialize();
			if ( isset(Uuid::$uobject) ) {
				uuid_make ( Uuid::$uobject, UUID_MAKE_V1 );
				uuid_export ( Uuid::$uobject, UUID_FMT_STR, &$uuidstring );
				return trim ( $uuidstring );
			} else {
				return null;
			}
		}

		/**
		 *
		 *
		 *
		 */
		public static function v3($namespace, $name) {
			if(!self::is_valid($namespace)) return false;
			// Get hexadecimal components of namespace
			$nhex = str_replace(array('-','{','}'), '', $namespace);

			Uuid::initialize();

			// Binary Value
			$nstr = '';

			// Convert Namespace UUID to bits
			for($i = 0; $i < strlen($nhex); $i+=2) {
				$nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
			}

			// Calculate hash value
			$hash = md5($nstr . $name);

			return sprintf('%08s-%04s-%04x-%04x-%12s',
				// 32 bits for "time_low"
				substr($hash, 0, 8),
				// 16 bits for "time_mid"
				substr($hash, 8, 4),
				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 3
				(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,
				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
				// 48 bits for "node"
				substr($hash, 20, 12)
			);
		}

		/**
		 * Return a type 4 (random) uuid
		 *
		 * @return String
		 */
		function v4() {

			Uuid::initialize();

			if (isset(Uuid::$uobject)) {
				uuid_make ( Uuid::$uobject, UUID_MAKE_V4 );
				uuid_export ( Uuid::$uobject, UUID_FMT_STR, &$uuidstring );
				return trim ( $uuidstring );
			}

			$pr_bits = false;
			if (is_resource ( Uuid::$urand )) {
				$pr_bits .= @fread ( Uuid::$urand, 16 );
			}

			if (! $pr_bits) {
				$fp = @fopen ( '/dev/urandom', 'rb' );
				if ($fp !== false) {
					$pr_bits .= @fread ( $fp, 16 );
					@fclose ( $fp );
				} else {
					// If /dev/urandom isn't available (eg: in non-unix systems), use mt_rand().
					$pr_bits = "";
					for($cnt = 0; $cnt < 16; $cnt ++) {
						$pr_bits .= chr ( mt_rand ( 0, 255 ) );
					}
				}
			}
			$time_low = bin2hex ( substr ( $pr_bits, 0, 4 ) );
			$time_mid = bin2hex ( substr ( $pr_bits, 4, 2 ) );
			$time_hi_and_version = bin2hex ( substr ( $pr_bits, 6, 2 ) );
			$clock_seq_hi_and_reserved = bin2hex ( substr ( $pr_bits, 8, 2 ) );
			$node = bin2hex ( substr ( $pr_bits, 10, 6 ) );

			/**
			 * Set the four most significant bits (bits 12 through 15) of the
			 * time_hi_and_version field to the 4-bit version number from
			 * Section 4.1.3.
			 * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
			 */
			$time_hi_and_version = hexdec ( $time_hi_and_version );
			$time_hi_and_version = $time_hi_and_version >> 4;
			$time_hi_and_version = $time_hi_and_version | 0x4000;

			/**
			 * Set the two most significant bits (bits 6 and 7) of the
			 * clock_seq_hi_and_reserved to zero and one, respectively.
			 */
			$clock_seq_hi_and_reserved = hexdec ( $clock_seq_hi_and_reserved );
			$clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
			$clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;

			$uuid = sprintf ( '%08s-%04s-%04x-%04x-%012s', $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node );

			return $uuid;

		}

		/**
		 * Return a type 5 (SHA-1 hash) uuid
		 *
		 * @return String
		 */
		public static function v5($namespace, $name) {

			if(!self::isValidUuid($namespace)) return false;

			Uuid::initialize();

			if (isset(Uuid::$uobject)) {
				uuid_make ( Uuid::$uobject, UUID_MAKE_V5 );
				uuid_export ( Uuid::$uobject, UUID_FMT_STR, &$uuidstring );
				return trim ( $uuidstring );
			}

			// Get hexadecimal components of namespace
			$nhex = str_replace(array('-','{','}'), '', $namespace);

			// Binary Value
			$nstr = '';

			// Convert Namespace UUID to bits
			for($i = 0; $i < strlen($nhex); $i+=2) {
				$nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
			}

			// Calculate hash value
			$hash = sha1($nstr . $name);

			return sprintf('%08s-%04s-%04x-%04x-%12s',
				// 32 bits for "time_low"
				substr($hash, 0, 8),
				// 16 bits for "time_mid"
				substr($hash, 8, 4),
				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 5
				(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,
				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
				// 48 bits for "node"
				substr($hash, 20, 12)
			);
		}


		/**
		 *
		 *
		 *
		 */
		public static function isValidUuid($uuid) {
			return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'.
				'[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
		}


		/**
		 * @brief Generates a Universally Unique IDentifier, version 1, 3, 4 or 5.
		 *
		 * This function generates a truly random UUID. The built in CakePHP String::uuid() function
		 * is not cryptographically secure. You should uses this function instead.
		 *
		 * @see http://tools.ietf.org/html/rfc4122#section-4.4
		 * @see http://en.wikipedia.org/wiki/UUID
		 * @return string A UUID, made up of 32 hex digits and 4 hyphens.
		 */
		function generate($version = null, $ns=null, $name=null) {
			if ($version == null) { $version = $this->version; }
			switch($version) {
				case Uuid::UUID_V1:
					return Uuid::v1();
				case Uuid::UUID_V3:
					return Uuid::v3($ns,$name);
				case Uuid::UUID_V4:
					return Uuid::v4();
				case Uuid::UUID_V5:
					return Uuid::v5($ns,$name);
			}

		}

		public function __toString() {
			return (string)$this->uuid;
		}

	}

	Uuid::initialize();



?>
