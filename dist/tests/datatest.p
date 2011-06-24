#!/usr/bin/php
<?php

require('lepton-framework');
using('lepton.graphics.filters.*');
using('lepton.data.*');
using('lepton.data.charting.piechart');

$ds = new DataSet('A','B','C','D');
$ds->addSeries('Sales', new DataSeries(50, 150, 75, 120));
$ds->addSeries('Services', new DataSeries(50, 55, 60, 65));

$pc = new PieChart(400,400);
$pc->setData($ds);
$pc->addObject(new ChartLegend($ds), rect(10,10,150,100));

$c = $pc->render();
$c->apply(new PixelateImageFilter(50));
$c->save('chart.png');
