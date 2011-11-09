<?php

    /**
     * Service Discovery class for Lepton
     *
     * Uses explorer classes to discover services such as RSS-feeds,
     * OpenSearch providers, or OpenID servers from a given location.
     *
     * 2009-12-29  Added oEmbed support as SERVICE_API_OEMBED
     *
     * @author Christopher Vagnetoft <noccy@chillat.net>
     */
    class Discovery {

        const KEY_EXPLORERS = 'lepton.discovery.explorers';

        /**
         * Examine the URL, optionally looking for a specific service. If no
         * service selection is done, all the services that the URL publishes
         * will be returned.
         *
         * @param string $url The URL to explore
         * @param string $service The service to look for
         * @return array An array of the exposed services at the URL
         */
        function discover($url, $services=null) {

            // Perform the query
            $ret = new HttpRequest($url);

            // Grab the data
            $status = $ret->status();
            $content = $ret->responseText();
            $headers = $ret->headers();

            $results = array();
            // Enumerate the explorers
            $explorers = config::get(Discovery::KEY_EXPLORERS, array());
            foreach($explorers as $explorer) {
                // Discover the service and merge the results
                $instance = new $explorer($url,$headers,$content);
                $instance->discover();
                if ($services) {
                    foreach($instance->getAllServices() as $stype => $sdata) {
                        // Return the service if it matches the type.
                        if ($stype == $service) return $sdata;
                    }
                } else {
                    // Merge the resultset otherwise
                    $results = array_merge($results,$instance->getAllServices());
                }
            }

            // Return null if we were looking for a specific service
            if ($services) return null;
            return $results;

        }

    }

    class KnownServices {
        const SERVICE_TRACKBACK =        'service:trackback';
        const SERVICE_PINGBACK =         'service:pingback';
        const SERVICE_OPENID_SERVER =    'service:openid:server';
        const SERVICE_OPENID_DELEGATE =  'service:openid:delegate';
        const SERVICE_OPENSEARCH =       'service:browser:opensearch';
        const SERVICE_FEED_RSS =         'service:feed:rss';
        const SERVICE_FEED_ATOM =        'service:feed:atom';
        const SERVICE_API_XMLRPC =       'service:api:xmlrpc';
        const SERVICE_API_OEMBED =       'service:api:oembed';
    }

    /**
     * Interface for the explorer
     */
    interface IExplorer {
        function discover();
    }

    /**
     * Base class for the explorer. Contains the key methods available to
     * all the explorer classes, as well as to the main Discovery class.
     */
    abstract class Explorer implements IExplorer {

        private $_url;
        private $_headers;
        private $_content;
        private $_services = array();

        /**
         * Constructor, caches the relevant data for use within the explorer.
         *
         * @param string $url The URL that is being queried
         * @param array $headers An array containing the response headers
         * @param string $content The content of the URL
         */
        function __construct($url,$headers,$content) {
            $this->_url = $url;
            $this->_headers = $headers;
            $this->_content = $content;
        }

        /**
         * Add a service to the result list. In order to allow several items
         * of the same type (f.ex. rss feeds) every item is pushed on the
         * stack with a key for the service id and a key for the data.
         *
         * NOTE: This may change very soon
         *
         * @param string $service The service key (one of KnownServices::*)
         * @param array $data The data relating to the service.
         */
        protected function addService($service,$data) {
            if (!isset($this->_services[$service]))
                $this->_services[$service] = array();
            if (!preg_match('/^http[s]?:\/\//i', $data['url'])) {
                $u = new Url($this->_url);
                $u->setPath($data['url']);
                $data['url'] = $u->getUrl();
            }
            $this->_services[$service][] = $data;
        }

        public function setServices($services) {
            $this->_services = $services;
        }

        /**
         * Returns a single service from the result set
         *
         * @param string $service The service to return.
         * @return array The service data (or null)
         */
        public function getService($service) {
            if (isset($this->_services[$service])) {
                return $this->_services[$service];
            } else {
                return null;
            }
        }

        /**
         * Returns an array containing all the discovered services.
         *
         * @return array The service list
         */
        public function getAllServices() {
            return $this->_services;
        }

        /**
         * Informs the explorer about the URL that is being queried.
         *
         * @return string The URL that's being queried
         */
        protected function getUrl() {
            return $this->_url;
        }

        /**
         * Returns the content of the URL that is being queried for the
         * explorer to scrape.
         *
         * @return string The content of the URL
         */
        protected function getContent() {
            return $this->_content;
        }

        /**
         * Returns the HTTP response-headers of the URL that is being
         * queried as an associative array.
         *
         * @return array The headers.
         */
        protected function getHeaders() {
            return $this->_headers;
        }
    }

    /**
     * HeaderExplorer: Locates information contained in the HTTP response
     * headers and returns the services that are available.
     */
    class HeaderExplorer extends Explorer {
        function discover() {
            $headers = $this->getHeaders();
            foreach($headers as $key=>$value) {
                switch(strtolower($key)) {
                case 'x-pingback':
                    $this->addService(KnownServices::SERVICE_PINGBACK, array(
                        'url' => $value
                    ));
                    break;
                }
            }
        }
    }

    /**
     * LinkExplorer: Locates information contained into HTML link elements
     * and returns the services that are available.
     */
    class LinkExplorer extends Explorer {
        function discover() {
            $dom = @DOMDocument::loadHTML($this->getContent());
            if (!$dom) return false;
            $nodes = $dom->getElementsByTagName('link');
            for ($n = 0; $n < $nodes->length; $n++) {
                $node = $nodes->item($n);
                $rel = $node->getAttributeNode('rel')->value;
                $href = $node->getAttributeNode('href')->value;
                $type = $node->getAttributeNode('type')->value;
                $title = $node->getAttributeNode('title')->value;
                switch(strtolower($rel)) {
                case 'openid.server':
                    $this->addService(KnownServices::SERVICE_OPENID_SERVER, array(
                        'url' => $href
                    ));
                    break;
                case 'openid.delegate':
                    $this->addService(KnownServices::SERVICE_OPENID_DELEGATE, array(
                        'url' => $href
                    ));
                    break;
                case 'search':
                    $this->addService(KnownServices::SERVICE_OPENSEARCH, array(
                        'url' => $href,
                        'title' => $title,
                        'type' => $type
                    ));
                    break;
                case 'pingback':
                    $this->addService(KnownServices::SERVICE_PINGBACK, array(
                        'url' => $href
                    ));
                    break;
                case 'alternate':
                    switch(strtolower($type)) {
                        case 'text/xml+oembed':
                        case 'text/json+oembed':
                            $this->addService(KnownServices::SERVICE_API_OEMBED, array(
                                'url' => $href,
                                'title' => $title,
                                'type' => $type
                            ));
                            break;
                        default:
                            $this->addService(KnownServices::SERVICE_FEED_RSS, array(
                                'url' => $href,
                                'title' => $title,
                                'type' => $type
                            ));
                            break;
                    }
                    break;
                case 'edituri':
                    $this->addService(KnownServices::SERVICE_API_XMLRPC, array(
                        'url' => $href,
                        'title' => $title,
                        'type' => $type
                    ));
                    break;
                }
            }
            return true;

        }
    }

    /**
     * RdfExplorer: Locates and parses RDF resources, returns the services that
     * are available.
     */
    class RdfExplorer extends Explorer {
        function discover() {

        }
    }

    /**
     * YadisExplorer: Locates and parses Yadis resources, returns the services
     * that are available.
     */
    class YadisExplorer extends Explorer {
        function discover() {
            $headers = $this->getHeaders();
            // Go over the response headers
            foreach($headers as $key=>$value) {
                if (strtolower($key) == 'x-xrds-location') {	
                    $xrdsurl = $value;
                    break;
                }
            }
            // If we don't get anything from the response headers, check the 
            // document headers.
            if (!$xrdsurl) { 

            }
            // If we got a proper yadis url, we go ahead and grab it.
        }
    }

