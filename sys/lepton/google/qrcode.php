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

	private function getImageUrl() {
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
		return $url;
	}

	/**
	 * @brief Redirects to the image url
	 *
	 * This function used to be called "getImage"
	 */
	public function redirect() {
		$url = $this->getImageUrl();
		header('location: '.$url);
	}

	/**
	 * @brief Return the canvas containing the QR code
	 *
	 * @todo This function is a quick-hack, returning the canvas by calling
	 *   on new Image() to load it. This is depending on whether fopen can
	 *   operate on URLs and thus the function need to be optimized and
	 *   improved to use curl/httprequest where possible.
	 * @return Canvas The canvas contianing the QR code
	 */
	public function getImage() {
		return new Image($this->getImageUrl());
	}
}
