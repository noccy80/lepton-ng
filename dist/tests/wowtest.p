#!/usr/bin/php
<?php require('sys/base.php');

using('blizzard.wow');

$api = new WowApiQuery('eu');
$rl = $api->getRealmStatus();

var_dump($rl);
