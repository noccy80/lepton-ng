#!/usr/bin/php
<?php

require('sys/base.php');
using('lepton.graphics.colorspaces.*');

$pc = pantone('299c');
echo (string)$pc."\n";
