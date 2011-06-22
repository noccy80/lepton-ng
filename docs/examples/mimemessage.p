#!/usr/bin/php
<?php 

require('sys/base.php');

// Change this line to cause the message to be sent to the specific recipient
// rather than output to the console.
$recipient = null;

// This should be defined in your configuration
config::set('lepton.net.mail.from', '"Local Lepton Installation" <lepton@noccylabs.info>');

// Required libraries for sending an e-mail.
using('lepton.net.mail.*');
using('lepton.net.protocol.smtp');

// Explanation of the process; Creating a multipart MIME message for using with
// the Lepton or any other mail sending routine.
//
//   MESSAGE: Has subject and a body consisting of a multipart entity
//    '-- MIXED: Contains two relevant chunks - message and attachment
//         |-- ALTERNATIVE: Contains the two alternative versions of the content
//         |    |-- ENTITY: The text/plain entity --.
//         |    '-- ENTITY: The text/html entity  --'-- Only one will display!
//         '-- ATTACHMENT: The attachment
//
// The top level object is the "MailMessage" class, containing the element and
// *one* MimeEntity derivative. This could be the MimeMultipartEntity, the
// MimeAlternativeEntity, MimeAttachment or MimeEntity class.
//
// The MimeMultipartEntity defines a message consisting of several parts, each
// which is an addition to the message. The MimeAlternativeEntity works in a
// similar way but it consist of the same message wrapped in different entitys,
// of which the recipients client will chose the best one it can handle.
//
// The MimeEntity defines an entity consisting of a body and a content type.
// A number of options can be added, f.ex. indicating the character set.
//
// A MimeAttachment embeds an attachment. The attachment will be base64-
// encoded, and also accepts a number of options as the last parameter.
//
// Common for the Multipartentity and AlternativeEntity is that they can both
// handle any number of child entities, passed through the constructors 
// argument.

$m = new MailMessage($recipient, 'Test message', new MimeMultipartEntity(
	new MimeAlternativeEntity(
		new MimeEntity('This is a mime text entity', 'text/plain'),
		new MimeEntity('This is a mime <b>html</b> entity', 'text/html', array(
			'charset' => 'utf8'
		))
	),
	new MimeAttachment('qrcode.png')
));

if ($recipient) {
	mail::send($m);
} else {
	console::writeLn((string)$m);
}
