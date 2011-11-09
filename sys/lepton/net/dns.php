<?php

class NsQuery {
    private $hostname = null;
    private $authns = array();
    private $addtl = array();
    private $records = array();
    private $mxhosts = array();
    private $mxweight = array();

    function __construct($hostname) {

        $this->hostname = $hostname;

        $this->records = dns_get_record($hostname, DNS_ANY, $this->authns, $this->addtl);

        // getmxrr($hostname, $this->mxhosts, $this->mxweight);
    }

    function getRecord($type) {

        $ret = array();
        foreach($this->records as $record) if ($record['type'] == $type) $ret[] = $record;
        foreach($this->addtl as $record) if ($record['type'] == $type) $ret[] = $record;
        return $ret;

    }

    function getAuthorativeNs() {

        return $this->authns;

    }
}

class NsLookup {

    static function getIp($hostname) {
        $r = gethostbyname($hostname);
        return $r;
    }

    static function query($hostname,$type = DNS_ANY) {
        $ra = dns_get_record($hostname,$type);
        return $ra;
    }

    static function getHostname($ip) {
        $r = gethostbyaddr($ip);
        return $r;
    }

    static function getLocalIp() {
        if (WINDOWS) {
            exec('ipconfig /all',$catch);
            foreach($catch as $line){
                if(eregi('IP Address',$line)){
                    list($t,$ip) = split(':',$line);
                    $ip = trim($ip);
                    if(ip2long($ip > 0)){
                        return $ip;
                    }
                }
            }		
        } else {
            exec('ifconfig',$catch);
            foreach($catch as $line){
                if(eregi('inet addr:',$line)){
                    list($t,$ip) = split(':',$line);
                    list($ip) = split(' ',$ip);
                    return $ip;
                }
            }
        }
    }
    
    static function getRemoteIp() {
        if ( arr::hasKey($_SERVER,"REMOTE_ADDR") )    { 
            $ip = $_SERVER["REMOTE_ADDR"]; 
        } else if ( arr::hasKey($_SERVER,"HTTP_X_FORWARDED_FOR") )    { 
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"]; 
        } else if ( arr::hasKey($_SERVER,"HTTP_CLIENT_IP") )  { 
            $ip = $_SERVER["HTTP_CLIENT_IP"]; 
        } else {
            $ip = null;
        }
        return $ip;
    }

}

class NetworkResolver {
    static function resolve($hostname) {
        return gethostbyname($hostname);
    }
}
