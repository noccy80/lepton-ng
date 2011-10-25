#!/usr/bin/php
<?php

require('sys/base.php');
using('lepton.web.forex');

$c = new CurrencyAmount('SEK 379.90 kr');

console::writeLn("Symbol: %s", $c->symbol);
console::writeLn("Amount: %.2f", $c->amount);
console::writeLn((string)$c);
console::writeLn($c->convert('USD'));
console::writeLn($c->convert('GBP'));

$c = new CurrencyAmount('USD 12345', 'SEK');
console::writeLn("Symbol: %s", $c->symbol);
console::writeLn("Amount: %.2f", $c->amount);
console::writeLn((string)$c);

$cd = new CurrencyAmount($c->amount, $c->symbol);
console::writeLn("And back to: %s", (string)$cd);
