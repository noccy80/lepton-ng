<?php

	ModuleManager::checkExtension('mcrypt');

	abstract class CipherMode {
		const CM_OBC = 'obc';
		const CM_EBC = 'ebc';
		const CM_CBC = 'cbc';
	}

	class Cipher {

		private $iv;
		private $td;

		function __construct($cipher,$key,$mode=CipherMode::ECB) {
			$this->td = mcrypt_module_open($cipher, '', $mode, '');
			$this->iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($this->td), MCRYPT_RAND);
			mcrypt_generic_init($td, $key, $iv);
		}

		function __destruct() {
			mcrypt_generic_deinit($this->td);
			mcrypt_module_close($this->td);
		}

		function encrypt($data,$armor=true) {
			$buffer = mcrypt_generic($this->td,$data);
			if ($armor) $buffer = base64_encode($buffer);
			return $buffer;
		}

		function decrypt($data,$armored=true) {
			if ($armored) $data = base64_decode($data);
			$buffer = mdecrypt_generic($this->td,$data);
			return $buffer;
		}

	}
?>
