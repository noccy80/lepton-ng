#!/usr/bin/php
<?php

require('sys/base.php');

$opts = array('a'=>'foo');
$defs = array('a'=>'bar','c'=>'baz');

var_dump(arr::merge($opts,$defs));
