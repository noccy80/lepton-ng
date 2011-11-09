<?php

config::def('lepton.crypto.rndsources', array(
    'UrandomRndSource',
    'RandomRndSource',
    'TimebasedRndSource',
));

/**
 * @class RndGen
 * @brief Random number generation
 *
 * This class calls on the best supported random number source available to the
 * system. New sources can be added with the lepton.crypto.rndsources config
 * setting.
 *
 * @author Christopher Vagnetoft <noccy.com>
 */
class RndGen {
    
    const KEY_RND_SOURCES = 'lepton.crypto.rndsources';
    
    private $src;
    
    function __construct() {

        logger::debug(__CLASS__.': Finding suitable source of randomness');
        $sources = config::get(RndGen::KEY_RND_SOURCES);
        foreach($sources as $source) {
            logger::debug(__CLASS__.': Testing source %s', $source);
            $sh = new $source();
            if ($sh->supported()) {
                logger::debug(__CLASS__.': Supported source found: %s', $source);
                $sh->initialize();
                $this->src = $sh;
                return;
            }
            unset($sh);
        }
        throw new SecurityException("No available randomness sources!");
        
    }
    
    function __destruct() {
        if ($this->src) {
            $this->src->terminate();
            unset($this->src);
        }
    }
    
    function getRandom($bytes=1) {
        return $this->src->getRandom($bytes);
    }
    
}

/**
 * @interface IRndSource
 *
 * Random number generator source
 */
interface IRndSource {
    function supported();
    function getRandom($bytes);
    function initialize();
    function terminate();
}

abstract class RndSource implements IRndSource {

}

class RandomRndSource extends RndSource {
    private $fh;
    function supported() {
        if (file_exists('/dev/random')) return true;
        return false;
    }
    function initialize() {
        $this->fh = @fopen ( '/dev/random', 'rb' );
        if (!$this->fh) throw new SecurityException("Unable to initialize RndSource ".__CLASS__);
    }
    function terminate() {
        fclose($this->fh);
    }
    function getRandom($bytes) {
        $rd = fread($this->fh,$bytes);
        return $rd;
    }
}

class UrandomRndSource extends RndSource {
    private $fh;
    function supported() {
        if (file_exists('/dev/urandom')) return true;
        return false;
    }
    function initialize() {
        $this->fh = @fopen ( '/dev/urandom', 'rb' );
        if (!$this->fh) throw new SecurityException("Unable to initialize RndSource ".__CLASS__);
    }
    function getRandom($bytes) {
        logger::debug(__CLASS__.': Asking urandom for %d bytes', $bytes);
        $rd = fread($this->fh,$bytes);
        return $rd;
    }
    function terminate() {
        fclose($this->fh);
    }
}

class TimebasedRndSource extends RndSource {
    
    private $seed;
    private $ptr = 0;
    
    function supported() { 
        return false;
    }
    
    function getRandom($bytes) { 

    }
    
    function initialize() {

    }
    
    function terminate() {
        
    }
}
