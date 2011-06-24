#!/usr/bin/php
<?php require('sys/base.php');

// Load hash library
using('lepton.crypto.hash');

// Create a sha256 hash through hash class
$ha = new Hash('sha256');
echo $ha->hash('hello world')."\n";

// Using static call
echo Hash::sha256('hello world')."\n";
