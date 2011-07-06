<?php module("Binary Blob Cache", array(
    'version' => '1.0'
));

class BinCache {

    private $fsh = null;

    public function __construct($filename,$memsize) {
        $ft = ftok($filename,'0');
        $this->fsh = shm_attach($ft, 0644, $memsize);
    }

    public function  __destruct() {
        shm_detach($this->fsh);
    }

    public function __get($var) {
        if (shm_has_var($this->fsh,$var)) {
            return shm_get_var($this->fsh, $var);
        } else {
            return null;
        }
    }

    public function __set($var,$value) {
        shm_put_var($this->fsh, $var, $value);
    }

    public function __unset($var) {
        shm_remove_var($this->fsh, $var);
    }

    public function  __isset($var) {
        return shm_has_var($this->fsh, $var);
    }

}