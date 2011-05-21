#!/usr/bin/php
<?php

require('sys/base.php');
using('lepton.graphics.*');
using('lepton.utils.cache');

cache::set('foo','bar','1m');

echo cache::get('foo');
echo cache::get('bar');
