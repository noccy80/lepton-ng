#!/usr/bin/php
<?php

require('sys/base.php');
using('lepton.google.qrcode');
using('lepton.graphics.canvas');

$qr = new QRCode(256,'http://lepton.noccylabs.info');
$qr->getImage()->save('qrcode.png');
