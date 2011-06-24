#!/usr/bin/php
<?php

require('sys/base.php');
using('lepton.user.authentication');
using('lepton.user.user');
using('lepton.user.avatars');

$u = new UserRecord();
echo $u->getAvatar(128);
echo "\n";

$u = User::find('noccy');
echo $u->getAvatar(128);
echo "\n";
