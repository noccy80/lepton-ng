#!/usr/bin/php
<?php

include('sys/base.php');
using('lepton.graphics.canvas');
using('lepton.graphics.ffmpegframe');

$i = new FfmpegFrame($argv[1],50);

$i->saveImage("test.png");
