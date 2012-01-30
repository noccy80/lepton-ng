<?php

/**
 * @brief IPC Resource Handle
 *
 *
 *
 *
 */
class IpcResource {

    private $ipckey = null;
    private $shmres = null;

    /**
     * @brief
     *
     * @param
     * @return
     */
    function __construct($filename) {
        if (!file_exists($filename)) {
            touch($filename);
            chmod($filename,0666);
        }
        $this->ipckey = ftok($filename,'i');
        $this->shmres = shm_attach($this->ipckey,1<<16);
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function __destruct() {
        shm_detach($this->shmres);
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function destroy() {
        shm_remove($this->shmres);
    }

}

/**
 * @brief Semaphore wrapper
 *
 *
 *
 *
 */
class Semaphore {

    private $ipckey = null;
    private $semres = null;
    private $hassem = false;

    /**
     * @brief
     *
     * @param
     * @return
     */
    function __construct($key,$acquire=true) {
        if (!file_exists($filename)) {
            touch($filename);
            chmod($filename,0666);
        }
        $this->ipckey = ftok($filename,'s');
        $this->semres = sem_get($this->ipckey);
        if ($acquire) $this->acquire();
    }
    
    /**
     * @brief
     *
     * @param
     * @return
     */
    function __destruct() {
        $this->release();
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function acquire() {
        if (!$this->hassem) {
            $this->hassem = sem_acquire($this->semres);
        }
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function release() {
        if ($this->hassem) {
            sem_release($this->semres);
            $this->hassem = false;
        }
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function destroy() {
        if ($this->ipckey) sem_remove($this->ipckey);
    }

}

/**
 * @brief Message Queue implementation
 *
 *
 *
 *
 */
class MessageQueue {

    /**
     * @brief
     *
     * @param
     * @return
     */
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

    /**
     * @brief
     *
     * @param
     * @return
     */
    function __construct($queue) {
        if (!file_exists($queue)) {
            touch($queue);
            chmod($queue,0666);
        }
        $this->ipckey = ftok($queue,'q');
        $this->queue = msg_get_queue($this->ipckey);
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function __destruct() {
        // No need to close the queue...?
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function nbpop($type=0) {
        if (msg_receive($this->queue, $type, $msgtype, 1<<16, $message, true, MSG_IPC_NOWAIT)) {
            return $message;
        } else {
            return null;
        }
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function pop($type=0) {
        if (msg_receive($this->queue, $type, $msgtype, 1<<16, $message, true)) {
            return $message;
        } else {
            return null;
        }
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function popstat($type=0) {
        $message = $this->pop($type);
        $stat = msg_stat_queue($this->queue);
        return array($message, $stat);
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function push($type, MessageEnvelope $message) {
        msg_send($this->queue, $type, $message, true, true);
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function flush() {
        $msgtype = null;
        while(msg_receive($this->queue, 0, $msgtype, 1<<16, $message, true, MSG_NOERROR | MSG_IPC_NOWAIT)) { }
     }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function destroy() {
        msg_remove_queue($this->queue);
    }

}

/**
 * @brief Envelope fo rmessages
 *
 *
 *
 *
 */
class MessageEnvelope implements IteratorAggregate {

    const DEFAULT_TTL = 255;

    private $data = array();
    private $type = null;
    private $ttl = null;

    /**
     * @brief
     *
     * @param
     * @return
     */
    function __construct($msgtype,array $msgdata) {
        $this->data = $msgdata;
        $this->type = $msgtype;
        $this->ttl = self::DEFAULT_TTL;
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    public function refresh() {
        $this->ttl--;
        return $this->ttl;
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function getTimeToLive() {
        return $this->ttl;
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function getMessageType() {
        return $this->type;
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function getMessageData() {
        return $this->data;
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function getAll() {
        return $this->data;
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function getIterator() {
        return new ArrayIterator($this->data);
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function __get($key) {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        } else {
            return null;
        }
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function __set($key,$value) {
        $this->data[$key] = $value;
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function  __unset($key) {
        unset($this->data[$key]);
    }

    /**
     * @brief
     *
     * @param
     * @return
     */
    function __isset($key) {
        return (isset($this->data[$key]));
    }

}
