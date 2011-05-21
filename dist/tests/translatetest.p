#!/usr/bin/php
<?php

require('sys/base.php');
using('lepton.google.translate');
$t = new GoogleTranslate('en','sv');
echo $t->translate('Hello world');

