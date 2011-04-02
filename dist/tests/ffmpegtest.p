#!/usr/bin/php
<?php

include('sys/base.php');
using('lepton.graphics.canvas');
using('lepton.graphics.ffmpegframe');

$i = new FfmpegFrame('dist/test.avi');

$i->saveImage("test.png");
