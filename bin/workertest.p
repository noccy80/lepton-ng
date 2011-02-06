#!/usr/bin/php
<?php

require('sys/base.php');

using('lepton.geo.geonames');
using('ldwp.*');

Governor::registerWorker(config::get('ldwp.worker.uuid'), config::get('ldwp.worker.name'));

Governor::getWorker(LocalWorker::getUuid())->addJob(new GeoCountryAction('install'));

LocalWorker::getWorker()->addJob(new GeoCountryAction('install'));


