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

class GnupgKey {

	const FOR_ANY = 0x00;
	const FOR_SIGNING = 0x01;
	const FOR_ENCRYPTING = 0x02;

	private $disabled = false;
	private $expired = false;
	private $revoked = false;
	private $cansign = false;
	private $canencrypt = false;
	private $secret = false;
	
	private $uids = array();
	private $subkeys = array();

	function __construct($keyblob) {
		$this->disabled = $keyblob['disabled'];
		$this->expired = $keyblob['expired'];
		$this->revoked = $keyblob['revoked'];
		$this->secret = $keyblob['is_secret'];
		$this->cansign = $keyblob['can_sign'];
		$this->canencrypt = $keyblob['can_encrypt'];
		$this->uids = $keyblob['uids'];
		$this->subkeys = $keyblob['subkeys'];
	}

}

class GnupgKeyRing {

	private $gpg = null;
	private $keys = null;

	function __construct($match='') {
		$this->gpg = gnupg_init();
		$this->keys = gnupg_keyinfo($this->gpg,$match);
	}
	
	function getAllKeys() {
		$kl = array();
		foreach($this->keys as $key) {
			$kl[] = new GnupgKey($key);
		}
		return $kl;
	}

	function getKeyByFingerprint($fp) {
		foreach($this->keys as $key) {
			foreach($key['subkeys'] as $subkey) {
				if ($subkey['fingerprint'] == $fp) {
					return new GnupgKey($key);
				}
			}
		}
	}

	function getKeyByEmail($email,$purpose = self::FOR_ANY) {
		foreach($this->keys as $key) {
			if (
				(($purpose & GnupgKey::FOR_SIGNING) && ($key['can_sign']))
				||
				(($purpose & GnupgKey::FOR_ENCRYPTING) && ($key['can_encrypt']))
				||
				(($purpose == GnupgKey::FOR_ANY))
			)
			foreach($key['uids'] as $subkey) {
				if ($subkey['email'] == $email) {
					return new GnupgKey($key);
				}
			}
		}
	}

}
