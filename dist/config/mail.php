<?php

// The SMTP server to use when delivering mail. This setting will and shall be
// used by all the mailer back-ends.
config::set('lepton.mail.smtp.server', 'localhost');

// If authentication is needed, set these options to the username and password
// that is to be used to authenticate against the server.
config::set('lepton.mail.smtp.authuser', null);
config::set('lepton.mail.smtp.authpass', null);

// The name of the local system. If null, it will try to be determined auto-
// matically by Lepton. This name will be used when greeting the server.
config::set('lepton.mail.smtp.localhost', null);

// Backend to use. These have different requirements and prerequisites but
// should use the same configuration values as specified above. Currently
// only the Pear backend supports authentication.
//
//    LeptonSmtpBackend  - Native PHP-based backend using sockets
//    PhpMailBackend   - Backend using PHPs mail() function
//    PearMailBackend  - Using the pear::smtp for sending data
config::set('lepton.mail.backend', 'LeptonSmtpBackend');
