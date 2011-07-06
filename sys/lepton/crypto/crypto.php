<?php

ModuleManager::checkExtension('mcrypt');

abstract class CipherMode {
    const CM_OBC = 'obc';
    const CM_ECB = 'ecb';
    const CM_CBC = 'cbc';
    const CM_CFB = 'cfb';
}

/**
 * @brief CryptoCipher class
 *
 * Encrypts and decrypts strings, possibly armored in base64. The data to
 * be encrypted is tagged with the length of the string as a 32bit unsigned
 * integer, allowing the string to be safely be decrypted back to its
 * original length and stripped of the PKCS7 padding.
 *
 * The rawencrypt() and rawdecrypt() methods does not do anything to pad
 * or unpad the data.
 */
class CryptoCipher {

    private $iv;
    private $td;

    /**
     * Constructor, accepts the name of the cipher and the key to use.
     *
     * @param string $cipher The cipher to use
     * @param string $key The key to use for encrypting/decrypting
     * @param string $mode The cipher mode
     */
    function __construct($cipher,$key,$mode=CipherMode::CM_ECB) {
        $this->td = mcrypt_module_open($cipher, '', $mode, '');
        $this->iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($this->td), MCRYPT_RAND);
        mcrypt_generic_init($this->td, $key, $this->iv);
    }

    /**
     * Destructor, deinits the cryptographic layer
     */
    function __destruct() {
        mcrypt_generic_deinit($this->td);
        mcrypt_module_close($this->td);
    }

    /**
     * Encrypts the data and armors it using base64
     *
     * @param string $data The data to encrypt
     * @param bool $armor if true, the enrypted data will be wrapped in base64
     * @return string The encrypted data
     */
    function encrypt($data,$armor=true) {
        $size = pack('N',strlen($data));
        $buffer = mcrypt_generic($this->td,$size.$data);
        if ($armor) $buffer = base64_encode($buffer);
        return $buffer;
    }

    /**
     * Decrypts the data and dearmors it using base64
     *
     * @param string $data The data to decrypt
     * @param bool $armor if true, the enrypted data will be unwrapped from base64
     * @return string The decrypted data
     */
    function decrypt($data,$armored=true) {
        if ($armored) $data = base64_decode($data);
        $buffer = mdecrypt_generic($this->td,$data);
        $pd = unpack('Nlen',$buffer);
        $buffer = substr($buffer,4,$pd['len']);
        return $buffer;
    }

    /**
     * Encrypts the data and armors it using base64 without tagging it with
     * the length of the data.
     *
     * @param string $data The data to encrypt
     * @param bool $armor if true, the enrypted data will be wrapped in base64
     * @return string The encrypted data
     */
    function rawencrypt($data,$armor=true) {
        $buffer = mcrypt_generic($this->td,$data);
        if ($armor) $buffer = base64_encode($buffer);
        return $buffer;
    }

    /**
     * Decrypts the data and dearmors it using base64 without removing 
     * any padding.
     *
     * @param string $data The data to decrypt
     * @param bool $armor if true, the enrypted data will be unwrapped from base64
     * @return string The decrypted data
     */
    function rawdecrypt($data,$armored=true) {
        if ($armored) $data = base64_decode($data);
        $buffer = mdecrypt_generic($this->td,$data);
        return $buffer;
    }

}
