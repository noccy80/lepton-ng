<?php

class IpcResource {

    private $ipckey = null;
    private $shmres = null;

    function __construct($filename) {
        if (!file_exists($filename)) {
            touch($filename);
            chmod($filename,0666);
        }
        $this->ipckey = ftok($filename,'i');
        $this->shmres = shm_attach($this->ipckey,1<<16);
    }

    function __destruct() {
        shm_detach($this->shmres);
    }

    function destroy() {
        shm_remove($this->shmres);
    }

}

class Semaphore {

    private $ipckey = null;
    private $semres = null;
    private $hassem = false;

    function __construct($key,$acquire=true) {
        if (!file_exists($filename)) {
            touch($filename);
            chmod($filename,0666);
        }
        $this->ipckey = ftok($filename,'s');
        $this->semres = sem_get($this->ipckey);
        if ($acquire) $this->acquire();
    }
    
    function __destruct() {
        $this->release();
    }

    function acquire() {
        if (!$this->hassem) {
            $this->hassem = sem_acquire($this->semres);
        }
    }

    function release() {
        if ($this->hassem) {
            sem_release($this->semres);
            $this->hassem = false;
        }
    }

    function destroy() {
        if ($this->ipckey) sem_remove($this->ipckey);
    }

}

class MessageQueue {

    static function queueExists($queue) {
        if (!file_exists($queue)) {
            return false;
        }
        $this->ipckey = ftok($queue,'q');
        if(function_exists("msg_queue_exists")) {
            return msg_queue_exists($key);
        } else {
            $aQueues = array();
            exec("ipcs -q | grep \"^[0-9]\" | cut -d \" \" -f 1", $aQueues);
            return(in_array($key,$aQueues));
        }
    }

    function __construct($queue) {
        if (!file_exists($queue)) {
            touch($queue);
            chmod($queue,0666);
        }
        $this->ipckey = ftok($queue,'q');
        $this->queue = msg_get_queue($this->ipckey);
    }

    function __destruct() {
        // No need to close the queue...?
    }

    function pop($type=0) {
        if (msg_receive($this->queue, $type, $msgtype, 1<<16, $message, true)) {
            return $message;
        } else {
            return null;
        }
    }

    function popstat($type=0) {
        $message = $this->pop($type);
        $stat = msg_stat_queue($this->queue);
        return array($message, $stat);
    }

    function push($type, MessageEnvelope $message) {
        msg_send($this->queue, $type, $message, true, true);
    }

    function destroy() {
        msg_remove_queue($this->queue);
    }

}

class MessageEnvelope implements IteratorAggregate {

    private $data = null;
    private $type = null;
    private $ttl = null;

    const DEFAULT_TTL=32;

    function __construct($msgtype,array $msgdata) {
        $this->data = $msgdata;
        $this->type = $msgtype;
        $this->ttl = self::DEFAULT_TTL;
    }

    public function refresh() {
        $this->ttl--;
    }

    function getTimeToLive() {
        return $this->ttl;
    }

    function getMessageType() {
        return $this->type;
    }

    function getMessageData() {
        return $this->data;
    }

    function getIterator() {
        return new ArrayIterator($this->data);
    }

    function __get($key) {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        } else {
            return null;
        }
    }

    function __set($key,$value) {
        $this->data[$key] = $value;
    }

    function  __unset($key) {
        unset($this->data[$key]);
    }

    function __isset($key) {
        return (isset($this->data[$key]));
    }

}
