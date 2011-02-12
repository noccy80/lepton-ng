<?php

class GnupgSignature {

	private $data = null;

	function __construct($data) {
		$this->data = $data;
	}

	/**
	 * Sign the chunk provided as with the specified key
	 *
	 * @param $data The data as a string blob
	 * @param $keyfp The key fingerprint
	 * @return The signature
	 */
	static function sign($keyfp) {

		$gp = gnupg_init();
		gnupg_addsignkey($gp,$keyfb);
		gnupg_setsignmode($gp,GNUPG_SIG_MODE_DETACH);
		$signature = gnupg_sign($gp,$this->data);
		return $signature;

	}

	/**
	 * Verify the signature
	 *
	 * @param $data The data to verify the signature of
	 * @param $signature The signature to match
	 * @return Boolean true if the signature is valid
	 */
	static function verify($signature) {

		$gp = gnupg_init();
		$result = "";
		if (gnupg_verify($gp,$this->data,$signature)) {
			return true;
		} else {
			return false;
		}
	}

}

class GnupgKeyRing {

	private $gpg = null;
	private $keys = null;

	function __construct($match='') {
		$this->gpg = gnupg_init();
		$this->keys = gnupg_keyinfo($this->gpg,$match);
		print_r($this->keys);
	}

	function getKeyByFingerprint($fp) {

	}

}
