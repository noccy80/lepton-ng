#!/usr/bin/php
<?php

$recipient = 'your@email.com';

require('sys/base.php');

config::set('lepton.net.mail.from', '"Local Lepton Installation" <lepton@noccylabs.info>');

using('lepton.net.mail.*');
using('lepton.net.protocol.smtp');

//
//  MESSAGE: Has subject and a body consisting of a multipart entity
//    '-- MIXED: Contains two relevant chunks - message and attachment
//         |-- ALTERNATIVE: Contains the two alternative versions of the content (html/plain)
//         |    |-- ENTITY: The text/plain entity
//         |    '-- ENTITY: The text/html entity
//         '-- ATTACHMENT: The attachment
//

$m = new MailMessage($recipient, 'Test message', new MimeMultipartEntity(
	new MimeAlternativeEntity(
		new MimeEntity('This is a mime text entity', 'text/plain'),
		new MimeEntity('This is a mime <b>html</b> entity', 'text/html', array(
			'charset' => 'utf8'
		))
	),
	new MimeAttachment('qrcode.png')
));

$s = new SmtpConnection('127.0.0.1');
$s->sendMessage('lepton@noccylabs.info', $recipient, $m->getMessage());
unset($s);
