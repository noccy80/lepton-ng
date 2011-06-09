#!/usr/bin/php
<?php

require('sys/base.php');

using('lepton.net.mail.*');

$m = new MailMessage('bob@domain.com', 'Test message', new MimeMultipartEntity(
	new MimeEntity('This is a mime text entity', 'text/plain'),
	new MimeEntity('This is a mime <b>html</b> entity', 'text/html', array(
		'charset' => 'utf8'
	)),
	new MimeAttachment('mimetest.p')
));

echo $m->getMessage();
