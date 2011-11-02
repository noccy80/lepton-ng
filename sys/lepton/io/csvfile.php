<?php

/**
 * @brief CSV Reader class for large imports
 * 
 * This class is designed to read data into an associative array based on the
 * headers provided to the constructor or on the first line of the file. It is
 * designed to be able to handle huge imports where seeking and telling could
 * be considered expensive, and contains functions to approximate the time
 * remaining based on the number of rows read, the number of bytes read, the
 * file size, and the elapsed number of seconds per row read.
 * 
 * @author Christopher Vagnetoft
 * @license GNU GPL v2 or later
 */

class CsvReader {
    
    private $headermap = null;
    private $options = null;
    private $filesize = null;
    private $fh = null;
    private $read = 0;
    private $timer = null;
    
    function __construct($filename,array $options=null, array $headermap=null) {
        $this->options = arr::defaults((array)$options, array(
            'delimiter' => ',',
            'enclosure' => '"',
            'escape' => "\\",
            'length' => 2048,
            'null' => '*NULL*'
        ));
        $this->headermap = $headermap;
        $this->fh = fopen($filename,'r');
        $this->filesize = filesize($filename);
        $this->timer = new Timer(false);
    }
    
    function __destruct() {
        if ($this->fh) fclose($this->fh);
    }
    
    function eof() {
        return (feof($this->fh));
    }
    
    function read() {
        if ($this->timer->getElapsed() == null) $this->timer->start();
        $rec = fgetcsv($this->fh, $this->options['length'], 
                $this->options['delimiter'], $this->options['enclosure'],
                $this->options['escape']);
        if ($this->headermap == null) {
            $this->headermap = $rec;
            return;
        }
        $out = array();
        foreach($this->headermap as $i=>$header) {
            if ($i < count($rec)) {
                $val = $rec[$i];
                if ($val == $this->options['null']) $val = null;
                $out[$header] = $val;
            } else {
                $out[$header] = null;
            }
        }
        $this->read++;
        return $out;
    }
    
    /**
     * @brief Get the status of the read operation
     * 
     * 
     * 
     * @return array Array of Rows read, Rows remaining, Elapsed time, and Remaining Time
     */
    function getStatus() {
        // Return rows read, and estimated number of rows remaining based on
        // file pointer and average length of record.
        $bread = ftell($this->fh);
        if ($this->read > 0) {
            $avgsize = $bread / $this->read;
            // Find out how many bytes are left
            $bleft = $this->filesize - $bread;
            // Remaining rows
            $rleft = round($bleft / $avgsize);
            $etime = $this->timer->getElapsed();
            // Get the time elapsed per record
            $trecord = $etime / $this->read;
            // And multiply it by the number of remaining records
            $rtime = $rleft * $trecord;
        } else {
            $rleft = 0; $etime = 0; $rtime = 0;
        }
        
        return array($this->read, $rleft, $etime, $rtime);
    }
    
}