#!/usr/bin/php
<?php

require('sys/base.php');

using('lepton.net.mail.*');

$m = new MailMessage('bob@domain.com', 'Test message', new MimeMultipartEntity(
	new MimeEntity('This is a mime content', 'text/plain')
));

echo $m->getMessage();
