#!/usr/bin/php
<?php

require('sys/base.php');
using('lepton.data.*');
using('lepton.data.charting.piechart');

$set = new DataSet('a','b');
$data = new DataSeries(1, 2, 3, 4);
$set->addSeries('Chart',$data);

$pc = new PieChart(400,400);
$pc->setData($set);

$pc->render()->save('chart.jpg');
