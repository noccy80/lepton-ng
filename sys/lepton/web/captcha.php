<?php module("Captcha generation");

using('lepton.graphics.canvas');

/**
 * Captcha Protection Library for Lepton
 *
 * This library handles and generates captchas for a user session.
 * Generated captchas are saved in the user session with a unique ID, thus
 * allowing to have more than one captcha-related page open at once without
 * any conflicts surfacing.
 *
 * @todo Implement width and height properly
 * @author Christopher Vagnetoft <noccy@chillat.net>
 */
class Captcha {

    /**
     * Generate and save a new captcha challenge. The returned ID can be
     * passed through the form in order to have it point to the right
     * captcha string.
     *
     * @return String ID of the newly generated captcha
     */
    function generate() {

        $len = config::get('lepton.captcha.textlength', 5);
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // Generate the actual captcha text
        $out = array();
        for($n = 0; $n < $len; $n++) {
            $out[] = substr($chars, rand(0,strlen($chars)-1),1);
        }
        $str = join('',$out);

        // Generate the id referencing this captcha
        $id = dechex(rand(1,65535));

        // Store the keys
        $keys = Session::get('lepton.captcha.keys', array());
        $keys['__default'] = $id;
        $keys[$id] = $str;
        Session::set('lepton.captcha.keys', $keys);

        // Return the ID
        return $id;

    }

    /**
     * Displays a captcha (by rendering it and sending it to the user) for
     * verification. Do not output anything prior to calling this function
     * as it will change the content type and then attempt to output the
     * image
     *
     * If the ID is omitted, the last generated ID will be used. This
     * makes it easier to implement on basic sites where there is a lower
     * or no chance that the visitor ever have more than one captcha-based
     * form open at once.
     *
     * @param String $id The previously generated() ID (optional)
     */
    function display($id = null, $saveto = null) {

        // Grab and test the font
        $font = config::get('lepton.captcha.font',null);
        if (!$font) {
            throw new Exception("No font specified for captcha, set the lepton.captcha.font key first!");
        }

        // Make sure that we can grab a valid string
        $keys = Session::get('lepton.captcha.keys', array());
        $id = ($id)?$id:$keys['__default'];
        if (!($id && isset($keys[$id]))) {
            throw new Exception("No captcha generated or invalid ID passed, call generate() first!");
        }
        $str = $keys[$id];

        // And render the captcha
        $c = new Canvas(160,40);
        $p = $c->getPainter();

        // Fill with color
        $p->drawFilledRect(-1,-1,161,41,
            new RgbColor(80,80,80),
            new RgbColor(rand(0,64),rand(0,64),rand(0,64)));

        // Draw some arcs and lines
        for ($n = 0; $n < 20; $n++) {
            $p->drawArc(rand(0,160),rand(0,40),rand(0,60),rand(0,60),
                rand(0,360),rand(0,360),
                new RgbColor(rand(100,200),rand(100,200),rand(100,200)));
            $p->drawLine(rand(-40,200),rand(-40,120),rand(-40,200),rand(-40,120),
                new RgbColor(rand(100,200),rand(100,200),rand(100,200)));
        }

        // Draw the letters of the captcha
        $f = new TruetypeFont($font,20);
        $wid = 160 / (strlen($str)+1);
        for ($n = 0; $n < strlen($str); $n++) {
            $f->setAngle(rand(-45,45));
            $c->drawText($f, new RgbColor(rand(230,255),rand(230,255),rand(230,255)),
                ($wid*($n+1)), 8, substr($str,$n,1));
        }

        if ($saveto) {
            $c->save($saveto);
        } else {
            $c->output();
        }
    }

    /**
     * Verifies a captha string. The string is matched against a specific
     * ID (if requested) or against all the issued captchas for the user
     * session.
     *
     * @param String $text The challenge response text
     * @param String $id The ID of the captcha challenge (optional)
     * @return Boolean True if success
     */
    function verify($text, $id = null) {

        // Get the keys from the session store
        $keys = Session::get('lepton.captcha.keys', array());

        if ($id) {
            // Match a specific ID
            $expected = String::toLowerCase($keys[$id]);
            if ($keys['__default'] == $id) unset($keys['__default']);
            unset($keys[$id]);
            Session::set('lepton.captcha.keys', $keys);
            return (String::toLowerCase($text) == $expected);
        } else {
            // Match all IDs
            $match = String::toLowerCase($text);
            foreach($keys as $id=>$value) {
                if ($id!='__default') {
                    if (String::toLowerCase($value) == $match) {
                        unset($keys[$id]);
                        if ($keys['__default'] == $id) unset($keys['__default']);
                        Session::set('lepton.captcha.keys', $keys);
                        return true;
                    }
                }
            }
            return false;
        }

    }

    function getstring($id) {

        // Get the keys from the session store
        $keys = Session::get('lepton.captcha.keys', array());

        // Match a specific ID
        $expected = String::toLowerCase($keys[$id]);
        return $expected;

    }
}

