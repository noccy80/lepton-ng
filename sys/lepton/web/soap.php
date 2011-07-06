<?php module("SOAP Web Service Query Library");

ModuleManager::load('lepton.web.serviceconsumer');

class SoapServiceConsumer extends ServiceConsumer {

    const XSI_NS = "http://www.w3.org/2001/XMLSchema-instance"; 
    const _NULL_ = "xxx_replacedduetobrokephpsoapclient_xxx"; 

    protected $mustParseNulls = false;
    protected $serviceurl; 
    protected $headers = array();
    
    public function __construct($serviceurl,$username=null,$password=null) {

        $this->serviceurl = $serviceurl;
        $this->client = new SoapClient($this->serviceurl); 

        if (($username) && ($password)) {
            // Prepare SoapHeader parameters 
            $auth = array( 
                'Username'    =>    $username, 
                'Password'    =>    $password
            ); 
            $this->headers[] = new SoapHeader($this->serviceurl, 'UserCredentials', $auth); 
            $this->client->__setSoapHeaders($this->headers); 
        }

    }    
    
    public function addHeader($name,$value) {
        $this->headers[] = new SoapHeader($this->serviceurl, $name, $value);
        $this->client->__setSoapHeaders($this->headers); 
    }
    
    public function __destruct() {
        unset($soapClient); 
    }    

    public function query($method,$params) {

        // Call RemoteFunction () 
        $error = 0; 
        try { 
            $info = $this->client->__doRequest($method, $params);
            return $info;
        } catch (SoapFault $fault) { 
            // TODO: Trhow Exception
            $error = 1; 
            print(" 
            alert('Sorry, blah returned the following ERROR: ".$fault->faultcode."-".$fault->faultstring.". We will now take you back to our home page.'); 
            window.location = 'main.php'; 
            "); 
        } 
    
    }

    public function __doRequest($request, $location, $action, $version, $one_way = null) { 
        if($this->mustParseNulls) { 
            $this->mustParseNulls = false; 
            $request = preg_replace('/<ns1:(\w+)>'.self::_NULL_.'<\/ns1:\\1>/', 
                '<ns1:$1 xsi:nil="true"/>', 
            $request, -1, $count); 
            if ($count > 0) { 
                $request = preg_replace('/(<SOAP-ENV:Envelope )/', 
                    '\\1 xmlns:xsi="'.self::XSI_NS.'" ', 
                    $request); 
            } 
        } 
        return parent::__doRequest($request, $location, $action, $version, $one_way); 
    } 

    public function __call($method, $params) { 
        foreach($params as $k => $v) { 
            if($v === null) { 
                $this->mustParseNulls = true; 
                $params[$k] = self::_NULL_; 
            } 
        } 
        return parent::__call($method, $params); 
    } 

}
