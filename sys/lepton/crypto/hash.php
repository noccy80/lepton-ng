<?php

__fileinfo("Hashing Functions");

/**
 * @class Hash
 * @brief Hash algorithm wrapper
 *
 * Wraps the HASH and MHASH extensions as well as the native PHP hash algorithms
 * into one single class, unifying the APIs needed to communicate thus making
 * it abstracted.
 */
class Hash {

	// Supported backends
	const MOD_HASH = 'hash';
	const MOD_MHASH = 'mhash';
	const MOD_PHP = 'php';

	private $algo = null; ///< @var The algorithm selection
	private $module = null; ///< @var The backend selection
	
	/**
	 * @brief Static call wrapper
	 *
	 * Not to be called directly. Rather use Hash::<algo>(<string>>)
	 *
	 * @param String $algo Algorithm
	 * @param Array $args The arguments
	 * @return String The hash
	 */
	static function __callStatic($algo,$args) {

		$ha = new Hash($algo);
		return $ha->hash($args[0]);

	}

	/**
	 * @brief Legacy static call wrapper
	 *
	 * Not to be called directly. Rather use Hash::<algo>(<string>>)
	 *
	 * @param String $algo Algorithm
	 * @param Array $args The arguments
	 * @return String The hash
	 */
	public function __call($algo,$args) {

		$ha = new Hash($algo);
		return $ha->hash($args[0]);

	}

	/**
	 * @brief Constructor
	 *
	 * @param String $algo The algorithm
	 */
	public function __construct($algo) {
	
		$algo = strtolower($algo);
	
		// Check for support
		if (extension_loaded('hash')) {
			// We got the hashing support, so let's check if the algorithm is
			// supported.
			if (arr::hasValue(hash_algos(),$algo)) {
				$this->module = self::MOD_HASH;
				$this->algo = $algo;
				return;
			}
		} 

		if (extension_loaded('mhash')) {
			// No hash support but mhash support, can it handle the algorithm?
			$num = mhash_count();
			for ($i = 0; $i <= $num; $i++) {
				if (mhash_get_hash_name($i) == $algo) {
					$this->module = self::MOD_MHASH;
					$this->algo = $algo;
					return;
				}
			}
		}

		// Fall back on legacy spport here, is the algorithm one of the
		// by php supported ones?
		if (arr::hasValue(array('md5','sha1','crc32'),$algo)) {
			$this->module = self::MOD_PHP;
			$this->algo = $algo;
			return;
		}

		// No support, throw exception
		throw new SecurityException("Request for unsupported hash algorithm");
	
	}

	/**
	 * @brief Calculate and return a hash
	 *
	 * @param String $string The string to hash
	 * @return String The hash
	 */
	public function hash($string) {

		switch($this->module) {
			case self::MOD_HASH:
				return hash($this->algo,$string);
				break;
			case self::MOD_MHASH:
				return mhash($this->algo,$string);
				break;
			case self::MOD_PHP:
				switch($this->algo) {
					case 'md5': return md5($string);
					case 'sha1': return sha1($string);
					case 'crc32': return crc32($string);
				}
				break;
		}
	
	}

}
