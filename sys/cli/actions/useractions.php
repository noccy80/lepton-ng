<?php __fileinfo("UserActions for CLI Tool", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));

class UserAction extends Action {
    public static $commands = array(
        'add' => array(
            'arguments' => '\g{username}',
            'info' => 'Add a new user to the database',
            'alias' => 'add-user'
        ),
        'remove' => array(
            'arguments' => '\g{username}',
            'info' => 'Remove an existing user'
        ),
        'password' => array(
            'arguments' => '\g{username}',
            'info' => 'Change the password for user'
        ),
        'match' => array(
            'arguments' => '[\g{pattern}]',
            'info' => 'List the existing users'
        ),
        'flags' => array(
            'arguments' => '\g{username} [\g{+}|\g{-}]\g{flags}',
            'info' => 'Set (or modify) user flags'
        ),
        'activate' => array(
            'arguments' => '\g{username}',
            'info' => 'Activates a user'
        ),
        'deactivate' => array(
            'arguments' => '\g{username} ["\g{reason}"]',
            'info' => 'Deactivates a user'
        ),
        'show' => array(
            'arguments' => '\g{username}',
            'info' => 'Show detailed information on a user'
        ),
        'password' => array(
            'arguments' => '\g{username}',
            'info' => 'Change password for user'
        ),
        'setprop' => array(
            'arguments' => '\g{username} \g{property} \g{value}',
            'info' => 'Set specific property for user'
        ),
        'addgroup' => array(
            'arguments' => '\g{groupname}',
            'info' => 'Add a usergroup'
        ),
        'remgroup' => array(
            'arguments' => '\g{groupname}',
            'info' => 'Removes a usergroup'
        ),
        'matchgroup' => array(
            'arguments' => '[\g{pattern}]',
            'info' => 'List the groups matching pattern'
        ),
    );

    function password($username=null) {
        using('lepton.user.*');
        if ($username) {
            $u = user::find($username);
            if ($u) {
                console::write("New password: "); $p = console::readPass();
                console::write("Confirm: "); $pc = console::readPass();
                if ($p != $pc) {
                    console::fatal('Passwords mismatch.');
                    exit(1);
                }
                $u->password = $p;
                console::writeLn("Password for user %s updated", $username);
            } else {
                console::writeLn("Could not find record for user %s", $username);
            }

        }
    }

    function setprop($username=null,$property=null,$value=null) {
        using('lepton.user.*');
        if ($username) {
            $u = user::find($username);
            if ($u) {
                $u->{$property} = $value;
                console::writeLn("Property %s for user %s updated", $property, $username);
            } else {
                console::writeLn("Could not find record for user %s", $username);
            }

        }
    }

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
            console::writeLn("Use: user add username");
        }
    }

    function remove($username=null) {
        using('lepton.user.*');
        if ($username) {
            if (user::remove($username)) {
                console::writeLn("Removed user");
            } else {
                console::writeLn("User not removed, does it exist?");
            }
        } else {
            console::writeLn("Use: user remove username");
        }
    }

    function match($pattern="*") {
        using('lepton.user.*');
        $ptn = str_replace('*','%',$pattern);
        $db = new DatabaseConnection();
        $results = $db->getRows("SELECT * FROM users WHERE username LIKE %s", $ptn);
        console::writeLn(__astr("\b{%-20s %-10s %-37s %-5s %s}"), 'Username', 'Flags', 'UUID', 'Act', 'Last login');
        if (!$results) {
            console::writeLn("No matching user records found for %s.", $ptn);
            return;
        }
        foreach($results as $user) {
            console::writeLn("%-20s %-10s %-37s %-5s %s (from %s)", $user['username'], $user['flags'], $user['uuid'], ($user['active']==1)?'Yes':'No', ($user['lastlogin'])?$user['lastlogin']:'Never', ($user['lastip'])?$user['lastip']:'Nowhere');
        }
    }

    function activate($username=null) {
        using('lepton.user.*');
        $u = user::find($username);
        if ($u) {
            $u->active = true;
            console::writeLn("User %s activated", $username);
        } else {
            console::writeLn("User %s not activated, does it exist?", $username);
        }
    }

    function deactivate($username=null) {
        using('lepton.user.*');
        $u = user::find($username);
        if ($u) {
            $u->active = false;
            console::writeLn("User %s deactivated", $username);
        } else {
            console::writeLn("User %s not deactivated, does it exist?", $username);
        }
    }

    function flags($username=null,$flags=null) {
        using('lepton.user.*');
        if ($username) {
            $u = user::find($username);
            if ($u) {
                $fl = $u->flags;
                $fn = $fl;
                $op = '+';
                for($n = 0; $n < strlen($flags); $n++) {
                    switch($flags[$n]) {
                        case '+': $op = '+'; break;
                        case '-': $op = '-'; break;
                        default:
                            if (($op == '+') && (strpos($fn,$flags[$n]) === false)) {
                                $fn .= $flags[$n];
                            } elseif (($op == '-') && (strpos($fn,$flags[$n]) !== false)) {
                                $fn = str_replace($flags[$n],'',$fn);
                            }
                    }
                }
                $u->flags = $fn;
                console::writeLn("Flags for %s changed from '%s' to '%s'", $username, $fl, $fn);
            } else {
                console::writeLn("No such user.");
            }
        } else {
            console::writeLn("Use: user flags username flags");
        }
    }

    function test($username=null) {
        using('lepton.user.*');
        using('lepton.mvc.request');
        if ($username) {
            console::write("Password: "); $p = console::readPass();
            if (!user::authenticate(new PasswordAuthentication($username, $p))) {
                console::writeLn('Authentication failure');
            } else {
                console::writeLn("Success!");
            }
        } else {
            console::writeLn("Use: user test username");
        }
    }

    function show($username=null) {
        using('lepton.user.*');
        using('lepton.mvc.request');
        if ($username) {
            $ur = user::find($username);
            console::writeLn(__astr('\b{%-20s}: %s'), 'Username', $ur->username);
            console::writeLn(__astr('\b{%-20s}: %s'), 'Displayname', $ur->displayname);
            console::writeLn(__astr('\b{%-20s}: %s'), 'E-Mail', $ur->email);
            console::writeLn(__astr('\b{%-20s}: %s'), 'UUID', $ur->uuid);
            console::writeLn(__astr('\b{%-20s}: %s'), 'Flags', $ur->flags);
            console::writeLn(__astr('\b{%-20s}: %s'), 'Active', ($ur->active==1)?'Yes':'No');
            console::writeLn(__astr('\b{%-20s}: %s'), 'Firstname', $ur->firstname);
            console::writeLn(__astr('\b{%-20s}: %s'), 'Lastname', $ur->lastname);
            console::writeLn(__astr('\b{%-20s}: %s'), 'Last login', $ur->lastlogin);
            console::writeLn(__astr('\b{%-20s}: %s'), 'Last IP', $ur->lastip);
            console::writeLn(__astr('\b{%-20s}: %s'), 'Registered', $ur->registered);
        } else {
            console::writeLn("Use: user show username");
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

