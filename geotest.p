#!/usr/bin/php
<?php

require('sys/base.php');

using('lepton.geo.*');

$ip = '213.112.94.4';
$ip2 = '85.224.84.20';

$info1 = GeoLocation::getInformationFromIp($ip);
$info2 = GeoLocation::getInformationFromIp($ip2);
$pos1 = GeoLocation::getPositionFromIp($ip);
$pos2 = GeoLocation::getPositionFromIp($ip2);

var_dump($info1);
var_dump($info2);

echo GeoUtil::getDistance($pos1,$pos2);
