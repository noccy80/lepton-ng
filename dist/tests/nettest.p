#!/usr/bin/php
<?php

require('sys/base.php');
using('lepton.net.sockets');
using('lepton.net.dns');

console::writeLn("Local IP: %s", NsLookup::getLocalIp());
