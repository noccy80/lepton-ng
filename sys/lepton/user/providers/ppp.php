<?php __fileinfo("Perfect Paper Password (PPP) Authentication", array(
    'version' => '0.1',
    'depends' => array(
        'lepton.user.*'
    )
));

/**
 * @brief Perfect Paper Password Authentication provider
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @author Jaden Bjorn <mikeboers.com>
 * @author Orson Jones
 * @author Bob Somers <www.bobsomers.com>
 * @license GNU GPL Version 2 or later
 *
 * @note This should never be used on its own. Instead use this class
 *   together with a username/password combination.
 */
class PppAuthentication extends AuthenticationProvider {

    const KEY_CHARSET = 'lepton.user.ppp.charset';
    const STR_CHARSET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    private $username;
    private $passcode;
    private $userid;

    /**
     * @brief Constructor for PPP Authentication
     *
     * Use the static function getNextIdentifier to get the identifier. 
     *
     * @see PppAuthentication::getNextIdentifier
     * @param String $username The username for which to validate the token
     * @param String $passcode The 4 letter passcode
     */
    public function __construct($username, $passcode) {
        $this->username = $username;
        $this->passcode = $passcode;
    }

    /**
     * @brief Get the next code for the user
     *
     * Use the cardIndexToString to cardIndexToArray to get a human readable
     * format. If null is returned this user hasn't got a key setup.
     *
     * @see PppAuthentication::cardIndexToString
     * @see PppAuthentication::cardIndexToArray
     * @param String $username The username to authenticate against
     * @return Integer The next code for the user
     */
    static function getNextIdentifier($username) {
        $user = User::find($username);
        if ($user) {
            $userid = $user->userid;
            $db = new DatabaseConnection();
            $rs = $db->getSingleRow("SELECT * FROM userppp WHERE id=%d", $userid);
            if ($rs) {
                $code = $rs['codeindex'];
                return intval($code);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @brief Reset the PPP data by assigning a new private secret.
     *
     * If secret is left blank a key will be generated automatically. This
     * also
     *
     * @param String $username The username to update
     * @param String $secret Optional secret to use
     */
    static function setSecretKey($username,$secret=null) {
        if ($secret == null) {
            $key = self::generateRandomSequenceKey();
        } else {
            $key = self::generateSequenceKeyFromString($secret);
        }
        $user = User::find($username);
        if ($user) {
            $userid = $user->userid;
            $db = new DatabaseConnection();
            $db->updateRow(
                "REPLACE INTO userppp (id,secretkey,codeindex) VALUES (%d,%s,0)",
                $userid,
                $key
            );
        }
    }

    /**
     * @brief Check if the token used for authentication is valid
     *
     * @return boolean True on success, false otherwise.
     */
    public function isTokenValid() {
        $user = User::find($this->username);
        if ($user) {
            $userid = $user->userid;
            $db = new DatabaseConnection();
            $rs = $db->getSingleRow("SELECT * FROM userppp WHERE id=%d", $userid);
            $db->updateRow("UPDATE userppp SET codeindex=codeindex+1 WHERE id=%d", $userid);
            if ($rs) {
                $codekey = $rs['secretkey'];
                $codeindex = $rs['codeindex'];
                $codematch = self::getCode($codekey, $codeindex);
                // printf('Key <b>%s</b>, index: <b>%s</b>, code: <b>%s</b>, token: <b>%s</b>',
                // $codekey, $codeindex, $codematch, $this->passcode);
                if ($codematch == $this->passcode) {
                    $this->userid = $user->userid;
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @brief Authenticate the specified user.
     *
     * @return boolean True on success
     */
    function login() {
        if ($this->userid) {
            $this->setUser($this->userid);
            // console::writeLn("Authenticated as user %d", $this->userid);
            return true;
        }
        throw new AuthenticationException("No user available to login()");
    }

    /**
     * @brief Regenerate the user's key.
     *
     * This is an alias for setSecretKey.
     *
     * @param String $username The username to regenerate the key for
     */
    function regenerateKeyForUser($username) {
        self::setSecretKey($username,null);
    }

    function getKeyForUser($username) {
        $user = User::find($username);
        if ($user) {
            $userid = $user->userid;
            $db = new DatabaseConnection();
            $rs = $db->getSingleRow("SELECT * FROM userppp WHERE id=%d", $userid);
            if ($rs) {
                $codekey = $rs['secretkey'];
            } else {
                $codekey = null;
            }
            return $codekey;
        } else {
            throw new UserException('User not found.');
        }
    }

    /**
     * @brief Convert the card index to a string
     *
     * @param <type> $index
     * @return <type>
     */
    function cardIndexToString($index) {
        $cardinfo = self::cardIndexToArray($index);
        // var_dump($cardinfo);
        return sprintf('%s (Card %d)', $cardinfo['code'], $cardinfo['card']);
    }

    /**
     * @brief Convert the card index to an array of card, row and column.
     *
     * @param <type> $index
     * @return <type>
     */
    function cardIndexToArray($index) {
        $row = ceil($index / 7) - 1;
        $col = ($index - ($row * 7));
        $colchar = substr('ABCDEFG', $col, 1);
        $card = (floor($row / 10));
        $row = $row - ($card * 10);
        return array(
            'index' => $index,
            'card' => ($card+1),
            'code' => $colchar.($row+1),
            'column' => $col,
            'row' => ($row+1)
        );
    }

    /**
     * @brief generates a 256-bit sequence key by hashing the passed string with SHA256
     *
     * it isn't recommended to use this method, rather, you should generate a random
     * key with GenerateRandomSequenceKey() instead
     * returns the sequence key as a hex string
     *
     * @param String $passphrase The key to use for the passphrase generation
     * @return String Sequence key
     */
    function generateSequenceKeyFromString($passphrase) {
        return hash('sha256', $passphrase);
    }

    /**
     * @brief generates a random 256-bit sequence key
     *
     * @retrun String The sequence key as a hex string
     */
    function generateRandomSequenceKey() {
        $randomness = get_loaded_extensions();
        $randomness[] = php_uname();
        $randomness[] = memory_get_usage();
        $randomness = implode(microtime(), $randomness);
        return hash('sha256', $randomness);
    }

    /**
     * @brief pack the 128 bit number into a binary string  (bcmath style
     *    number to binary)
     *
     * @param Integer $num Number
     * @return String The binary string
     */
    function pack128( $num ) {
        $pack = '' ;
        while( $num ) {
            $pack .= chr( bcmod( $num, 256 ) ) ;
            $num = bcdiv( $num, 256 ) ;
        }
        return $pack ;
    }

    /**
     * @brief unpack the 128 bit integer from a binary string (binary to bcmath style number)
     * 
     *
     * @param <type> $pack
     * @return <type> 
     */
    function unpack128( $pack ) {
        $pack = str_split( strrev( $pack )) ;
        $num = '0' ;
        foreach( $pack as $char ) {
            $num = bcmul( $num, 256 ) ;
            $num = bcadd( $num, ord( $char )) ;
        }
        return $num ;
    }

    /**
     * @brief calculate the number of characters in a crypto block for a
     *     character set of given length.
     *
     * @param <type> $length
     * @return <type>
     */
    function blockChars( $length ) {
        return floor(128/(log($length, 2)));
    }

    /**
     * @brief sort the character set
     *
     * @param <type> $charset
     * @return <type>
     */
    function sortChars($charset) {
        $newchars = str_split($charset,1);
        sort($newchars);
        return implode('',$newchars);
    }

    /**
     * @brief returns lotto numbers
     *
     * @param <type> $key
     * @param <type> $code
     * @return <type>
     */
    function getLotto($key, $code) {
        $sk = pack("H*", $key);
        $n_bits = self::pack128($code);
        $enc_bits = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sk, $n_bits, MCRYPT_MODE_ECB, str_repeat( "\0", 16 ));
        $numdec = self::unpack128($enc_bits);
        $chars = "";
        for ($i = 0; $i < 5; $i++) {
            $chars .= bcadd(bcmod($numdec,56),1);
            if ($i < 4)
                $chars .= ", ";
            else
                $chars .= " / ";
            $numdec = bcdiv($numdec,56);
        }
        $chars .= bcadd(bcmod($numdec,46),1);
        return $chars;
    }

    /**
     * @brief returns the nth number (Ex.: Port numbers, etc.)
     *
     * @param <type> $key
     * @param <type> $code
     * @param <type> $codemin
     * @param <type> $codemax
     * @return <type>
     */
    function getNum($key, $code, $codemin, $codemax) {
        $length = 1+$codemax-$codemin;
        $sk = pack("H*", $key);
        $n_bits = self::pack128($code);
        $enc_bits = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sk, $n_bits, MCRYPT_MODE_ECB, str_repeat( "\0", 16 ));
        $numdec = self::unpack128($enc_bits);
        return bcadd(bcmod($numdec,$length),$codemin);
    }

    /**
     * @brief returns the nth port and code
     * Based on idea from Hank Beaver in the GRC newsgroups
     *
     * @param <type> $key
     * @param <type> $code
     * @param <type> $codemin
     * @param <type> $codemax
     * @return <type>
     *
     */
    function getPortCode($key, $code, $codemin, $codemax) {
        $codes = array();
        $length = 1+$codemax-$codemin;
        $sk = pack("H*", $key);
        $n_bits = pack128($code);
        $enc_bits = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sk, $n_bits, MCRYPT_MODE_ECB, str_repeat( "\0", 16 ));
        $numdec = unpack128($enc_bits);
        array_push($codes, bcadd(bcmod($numdec,$length),$codemin));
        $numdec = bcdiv($numdec,$length);
        $charset = config::get(PppAuthentication::KEY_CHARSET, PppAuthentication::STR_CHARSET);
        $length = strlen($charset);
        $codelength = 2;
        $chars = "";
        for ($i = 0; $i < $codelength; $i++) {
            $chars .= substr($charset,bcmod($numdec,$length),1);
            $numdec = bcdiv($numdec,$length);
        }
        array_push($codes, $chars);
        return $codes;
    }

    /**
     * @brief returns the nth code
     *
     * @param <type> $key
     * @param <type> $code
     * @param <type> $codelength
     * @return <type>
     */
    function getCode($key, $code, $codelength = 4) {
        $charset = self::sortchars(config::get(PppAuthentication::KEY_CHARSET, PppAuthentication::STR_CHARSET));
        $length = strlen($charset);
        $blockchars = self::blockchars($length);
        $sk = pack("H*", $key);
        $n_bits = self::pack128($code);
        $enc_bits = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sk, $n_bits, MCRYPT_MODE_ECB, str_repeat( "\0", 16 ));
        $numdec = self::unpack128($enc_bits);
        $chars = "";
        for ($i = 0; $i < $codelength; $i++) {
            $chars .= substr($charset,bcmod($numdec,$length),1);
            $numdec = bcdiv($numdec,$length);
        }
        return $chars;
    }

    /**
     * @brief return an array of the codes requested
     *
     * @param <type> $key
     * @param <type> $code
     * @param <type> $num
     * @param <type> $codelength
     * @return <type>
     */
    function getCodes($key, $code, $num, $codelength) {
        $charset = self::sortchars(config::get(PppAuthentication::KEY_CHARSET, PppAuthentication::STR_CHARSET));
        $codes = array();
        $first = $code;
        $last = bcadd($code, $num);
        $length = strlen($charset);
        $blockchars = self::blockchars($length);
        $sk = pack("H*", $key);
        for ($h = $first; bccomp($h,$last) < 0; $h = bcadd($h,1)) {
            $n_bits = self::pack128($h);
            $enc_bits = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $sk, $n_bits, MCRYPT_MODE_ECB, str_repeat( "\0", 16 ));
            $numdec = self::unpack128($enc_bits);
            $chars = "";
            for ($i = 0; $i < $codelength; $i++) {
                $chars .= substr($charset,bcmod($numdec,$length),1);
                $numdec = bcdiv($numdec,$length);
            }
            array_push($codes, $chars);
        }
        return $codes;
    }

    /**
     * @brief prints a card
     *
     * @param String $key
     * @param Integer $codelength
     * @param Integer $cardnum
     * @param String $title
     */
    function printPasswordCard($key,$codelength=4,$cardnum=0,$title="PPP Card") {
        $charset = self::sortchars(config::get(PppAuthentication::KEY_CHARSET, PppAuthentication::STR_CHARSET));
        printf("%-30.30s%8s\n",$title,"[".($cardnum+1)."]");
        $rows = 10;
        $cols = floor(35/($codelength+1));
        $total = $rows*$cols;
        // var_dump($total);
        echo "    ";
        for ($i = 0; $i < $cols; $i++) {
            echo str_pad(chr(ord("A")+$i), $codelength+1, " ", STR_PAD_BOTH);
        }
        echo "\n";
        $codes = self::getCodes($key,bcmul($cardnum,$total),$total,$codelength);
        for ($i = 0; $i < $total; $i++) {
            $code = $codes[$i];
            if ($i % $cols == 0)
                printf("%2s: ",ceil(($i+1)/$cols));
            if ($i % $cols < $cols-1)
                echo "$code ";
            else
                echo "$code\n";
        }
    }

}


