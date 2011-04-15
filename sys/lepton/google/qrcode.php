<?php

class QRCode {

	const ECC_LOW='L';
	const ECC_MEDIUM='M';
	const ECC_HIGH='Q';
	const ECC_ULTRA='H';

	private $data = null;
	private $size = null;
	private $ecc = QRCode::ECC_LOW;

	function __construct($size,$data,$ecc=QRCode::ECC_LOW) {
		$this->data = $data;
		$this->size = $size;
		$this->ecc = $ecc;
	}

	function getImage() {
		$opts = array(
			'cht' => 'qr',
			'chs' => $this->size,
			'chl' => urlencode($this->data),
			'chld' => $this->ecc.'|1'
		);
		$optstr = array();
		foreach($opts as $k=>$v) {
			$optstr[] = $k.'='.$v;
		}
		$url = 'https://chart.googleapis.com/chart';
		$url.= '?'.join('&',$optstr);
		header('location: '.$url);
	}

}
