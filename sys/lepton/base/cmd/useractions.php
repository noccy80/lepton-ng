<?php __fileinfo("UserActions for CLI Tool", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));

config::push('lepton.cmd.actionhandlers','UserActions');

class UserActions extends ConsoleActions {

    static $help = array(
        'ppp' => 'Perfect Paper Passwords test utilities',
        'pppuser' => 'Manipulate PPP-data for users',
        'adduser' => 'Add a user'
    );
    function _info($cmd) { return self::$help[$cmd->name]; }

    function adduser($username=null,$password=null) {
        using('lepton.user.*');
        if ($username && $password) {
            $u = new UserRecord();
            $u->username = $username;
            $u->password = $password;
            if (User::create($u)) {
                console::writeLn("User created.");
            } else {
                console::writeLn("Couldn't create user.");
            }
        } else {
            console::writeLn("Not enough arguments");
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
                    var_dump($key);
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

