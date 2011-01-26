<?php __fileinfo("UserActions for CLI Tool", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));

class UserAction extends Action {
	public static $commands = array(
		'add' => array(
			'arguments' => '\u{username}',
			'info' => 'Add a new user to the database'
		),
        'remove' => array(
            'arguments' => '\u{username}',
            'info' => 'Remove an existing user'
        ),
        'match' => array(
            'arguments' => '[\u{pattern}]',
            'info' => 'List the existing users'
        ),
        'flags' => array(
            'arguments' => '\u{username} [\u{+}|\u{-}]\u{flags}',
            'info' => 'Set (or modify) user flags'
        )
	);

    function add($username=null) {
        using('lepton.user.*');
        if ($username) {
            console::write("New password: "); $p = console::readPass();
            console::write("Confirm: "); $pc = console::readPass();
            if ($p != $pc) { 
                console::fatal('Passwords mismatch.');
                exit(1);
            }
            $password = $p;
            console::write("DisplayName: "); $displayname = console::readLn();
            console::write("E-Mail: "); $email = console::readLn();
            console::write("Flags: "); $flags = console::readLn();
            console::write("Is this correct? [Y/n] "); $ok = console::readLn();
            if (strtolower($ok) == 'n') {
                exit(1);
            }
            $u = new UserRecord();
            $u->username = $username;
            $u->password = $password;
            $u->email = $email;
            $u->flags = $flags;
            $u->displayname = $displayname;
            if (User::create($u)) {
                console::writeLn("User created.");
            } else {
                console::writeLn("Couldn't create user.");
            }
        } else {
            console::writeLn("Use: adduser username");
        }
    }

    function match($pattern="*") {
        using('lepton.user.*');
        $ptn = str_replace('*','%',$pattern);
        $db = new DatabaseConnection();
        $results = $db->getRows("SELECT * FROM users WHERE username LIKE %s", $ptn);
        foreach($results as $user) {
            console::writeLn("%-30s %-10s %s (%s)", $user['username'], $user['flags'], $user['lastlogin'], $user['lastip']);
        }
    }

    function pppuser($command=null) {
        $args = func_get_args();
        $args = array_slice($args,1);
        using('lepton.user.providers.ppp');
        switch($command) {
            case 'genkey':
                if (count($args)>0) {
                    if (count($args)>1) {
                        $key = $args[0];
                    } else {
                        $key = null;
                    }
                    $user = $args[0];
                    PppAuthentication::setSecretKey($user,$key);
                    console::writeLn("Updated key for %s. Use 'pppuser printcard' to print a new card for this user.", $user);
                } else {
                    console::writeLn("Not enough parameters.");
                }
                break;
            case 'printcard':
                if (count($args)>1) {
                    $user = $args[0];
                    $key = PppAuthentication::getKeyForUser($user);
                    $card = (intval($args[1]) - 1);
                    PppAuthentication::printPasswordCard($key, 4, $card);
                } else {
                    console::writeLn("Not enough parameters.");
                }
                break;
            case '':
                console::writeLn(__astr("pppuser \b{genkey} \u{username} [\u{string}]   -- (Re)generate key for user"));
                console::writeLn(__astr("pppuser \b{printcard} \u{username} [\u{cardnumber}]   -- Print cards for user"));
                break;
            default:
                console::writeLn("Bad command.");
        }
    }

    function ppp($command=null) {
        $args = func_get_args();
        $args = array_slice($args,1);
        using('lepton.user.providers.ppp');
        switch($command) {
            case 'genkey':
                if (count($args)>0) {
                    $key = PppAuthentication::generateSequenceKeyFromString($args[0]);
                    console::writeLn(__astr("Plaintext String:   \b{%s}"), $args[0]);
                } else {
                    $key = PppAuthentication::generateRandomSequenceKey();
                }
                console::writeLn(__astr("Generated key:      \b{%s}"), $key);
                break;
            case 'printcard':
                if (count($args)>1) {
                    $key = PppAuthentication::generateSequenceKeyFromString($args[1]);
                    console::writeLn(__astr("Plaintext String:   \b{%s}"), $args[1]);
                } else {
                    $key = PppAuthentication::generateRandomSequenceKey();
                }
                console::writeLn(__astr("Generated key:      \b{%s}"), $key);
                if (count($args)>0) {
                    $card = (intval($args[0]) - 1);
                } else {
                    $card = 0;
                }
                PppAuthentication::printPasswordCard($key, 4, $card);
                break;
            case 'printcode':
                if (count($args)>2) {
                $key = PppAuthentication::generateSequenceKeyFromString($args[2]);
                console::writeLn(__astr("Plaintext String:   \b{%s}"), $args[2]);
                console::writeLn(__astr("Generated key:      \b{%s}"), $key);
                } else {
                $key = PppAuthentication::generateRandomSequenceKey();
                console::writeLn(__astr("Generated key:      \b{%s}"), $key);
                }
                if (count($args)>1) {
                $card = $args[0];
                $code = $args[1];
                } else {
                $card = 0;
                $code = 1;
                }
                console::writeLn(PppAuthentication::getCode($key, $code, 4));
                break;
            case 'getlotto':
                $key = PppAuthentication::generateRandomSequenceKey();
                $code = rand(0,65535);
                console::writeLn(PppAuthentication::getLotto($key, $code));
                break;
            case 'tostring':
                if (count($args) > 0) {
                console::writeLn(PppAuthentication::cardIndexToString($args[0]));
                } else {
                console::writeLn("Not enough parameters.");
                }
                break;
            case '':
                console::writeLn(__astr("ppp \b{printcard} [\u{card} [\u{key}]]   -- Print a specific card (with specific key)"));
                console::writeLn(__astr("ppp \b{printcode} [\u{card} \u{code} [\u{key}]]   -- Print code from card"));
                console::writeLn(__astr("ppp \b{genkey} [\u{string}]   -- Generate key (from string)"));
                console::writeLn(__astr("ppp \b{getlotto}   -- return lotto numbers"));
                console::writeLn(__astr("ppp \b{tostring} \u{code}   -- return string representation of code index"));
                break;
            default:
                console::writeLn("Bad command.");
        }
    }

}

actions::register(
	new UserAction(),
	'user',
	'Add, remove and manage users',
	UserAction::$commands
);

