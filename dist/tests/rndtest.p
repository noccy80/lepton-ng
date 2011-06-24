#!/usr/bin/php
<?php

require('sys/base.php');
using('lepton.crypto.rndgen');

$rg = new RndGen();

function hexstr($str) {
	$so = '';
	for($n=0; $n < strlen($str); $n++)  {
		$so.=sprintf('%02x',ord($str[$n]));
	}
	return $so;
}

console::writeLn('%s',hexstr($rg->getRandom(4)));
