#!/usr/bin/php
<?php

require('sys/base.php');
using('lepton.google.qrcode');
using('lepton.graphics.canvas');

QRCode(256,'http://lepton.noccylabs.info')->getImage()->save('qrcode.png');
