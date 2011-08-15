<?php

class MessageQueue {

    function __construct($queue) {

    }

}

interface IMessagePipeline {
    function open($queue);
    function close();
    function peekMessage();
    function getMessage();
    function pushMessage(MessageEnvelope $e);
    function purge();
}

abstract class MessagePipeline implements IMessagePipeline {

}

class IpcMessagePipeline extends MessagePipeline {

}

class DatabaseMessagePipeline extends MessagePipeline {

}

class MessageEnvelope {
    function __construct($msgtype,array $data) {
    
    }
    function getSourcePeer() {
    
    }
    function getTimestamp() {
    
    }
}
