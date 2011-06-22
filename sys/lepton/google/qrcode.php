<?php

using('lepton.graphics.canvas');
using('lepton.net.httprequest');

/**
 * @class QRCode
 * @brief Generate QR-Codes (Quick Response) embedding an URL or a message.
 *
 * @author Christopher Vagnetoft
 * @package lepton.google.qrcode
 */
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
	 * @return Canvas The canvas contianing the QR code
	 */
	public function getImage() {
		$irequest = new HttpRequest($this->getImageUrl());
		if ($irequest) {
			$img = new StringImage($irequest->getResponse());
			if ($img) return $img;
			throw new BaseException("Could not create canvas from response");
		}
		throw new BaseException("Error requesting QR Code");
	}
}

function QRCode($size,$data,$ecc=QRCode::ECC_LOW) {
	return new QRCode($size,$data,$ecc);
}
