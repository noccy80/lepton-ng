#!/usr/bin/php
<?php

require('sys/base.php');

config::set('lepton.net.mail.from', '"Local Lepton Installation" <lepton@noccylabs.info>');

using('lepton.net.mail.*');
using('lepton.net.protocol.smtp');

$m = new MailMessage('noccy@chillat.net', 'Test message', new MimeMultipartEntity(
	new MimeEntity('This is a mime text entity', 'text/plain'),
	new MimeEntity('This is a mime <b>html</b> entity', 'text/html', array(
		'charset' => 'utf8'
	)),
	new MimeAttachment('colortest-hsl.png')
));

$s = new SmtpConnection('127.0.0.1');
$s->sendMessage('lepton@noccylabs.info', 'noccy@chillat.net', $m->getMessage());
unset($s);
