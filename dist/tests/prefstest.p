#!/usr/bin/php
<?php

include('sys/base.php');
using('lepton.utils.prefs');

$p = new FsPrefs("prefs.db");
console::writeLn("Test was: %s", $p->test);
$p->test = "Hello World";
console::writeLn("Test is now: %s", $p->test);
