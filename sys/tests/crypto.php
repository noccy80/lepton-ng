<?php

using('lunit.*');

/**
 * @description Cryptography Tests
 * @extensions mcrypt
 */
class LeptonCryptoTests extends LunitCase {

	function __construct() {
		using('lepton.crypto.*');
	}

	/**
	 * @description MD5 Hashing Implementation
	 */
	function md5hash() {
		$this->assertEquals(hash::md5('helloworld'), 'fc5e038d38a57032085441e7fe7010b0');
	}

	/**
	 * @description SHA1 Hashing Implementation
	 */
	function sha1hash() {
		$this->assertEquals(hash::sha1('helloworld'), '6adfb183a4a2c94a2f92dab5ade762a47889a5a1');
	}

	/**
	 * @description Blowfish Cryptography Implementation
	 */
	function blowfishcrypto() {
		$c = new CryptoCipher(MCRYPT_BLOWFISH,'hello');
		$e = $c->encrypt('helloworld');
		$this->assertEquals($e, 'DBFXfU7vX9Afijttxwhmvg==');
		$this->assertEquals($c->decrypt($e),'helloworld');
	}

	/**
	 * @description Twofish Cryptography Implementation
	 */
	function twofishcrypto() {
		$c = new CryptoCipher(MCRYPT_TWOFISH,'hello');
		$e = $c->encrypt('helloworld');
		$this->assertEquals($e, 'uIfx79ilnvRVY5RT/znzXA==');
		$this->assertEquals($c->decrypt($e),'helloworld');
	}
    /**
	 * @description DES Cryptography Implementation
	 */
	function descrypto() {
		$c = new CryptoCipher(MCRYPT_DES,'hello');
		$e = $c->encrypt('helloworld');
		$this->assertEquals($e, 'q8dBduudWGAuIw4LPXMCFQ==');
		$this->assertEquals($c->decrypt($e),'helloworld');
	}
	
	/**
	 * @description Generate UUID V4
	 */
	function uuid_v4() {
		$uuid = uuid::v4();
		$this->assertEquals(strlen($uuid), uuid::LENGTH);
	}
}

Lunit::register('LeptonCryptoTests');
