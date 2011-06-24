#!/usr/bin/php
<?php

require('sys/base.php');

using('lepton.geo.*');

/*
$ip = '213.112.94.4';
$ip2 = '85.224.84.20';

$info1 = GeoLocation::getInformationFromIp($ip);
$info2 = GeoLocation::getInformationFromIp($ip2);
$pos1 = GeoLocation::getPositionFromIp($ip);
$pos2 = GeoLocation::getPositionFromIp($ip2);

var_dump($info1);
var_dump($info2);
*/
$pos1 = new GeoPosition(55.2345,110.2328233);
$pos2 = new GeoPosition(56.2345,110.2328233);
printf('pos1<%s> pos2%s>', (string)$pos1, (string)$pos2);

echo GeoUtil::getDistance($pos1,$pos2);
