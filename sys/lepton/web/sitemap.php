<?php

    /**
     * Sitemap helper library; creates sitemaps on the fly.
     *
     * @since 0.2
     * @author Christopher Vagnetoft <noccy@chillat.net>
     */
    class Sitemap {

        const FREQ_ALWAYS   = "always";
        const FREQ_HOURLY   = "hourly";
        const FREQ_DAILY    = "daily";
        const FREQ_WEEKLY   = "weekly";
        const FREQ_MONTHLY  = "monthly";
        const FREQ_YEARLY   = "yearly";
        const FREQ_NEVER    = "never";

        private $locations;
        private $base;

        /**
         * Constructor, sets the base URL for any subsequent calls to the
         * addLocation method.
         *
         * @param string $base The base URL
         */
        function __construct($base=null) {
            $this->base = $base;
        }

        /**
         * Add a location to the sitemap.
         *
         * @param string $location The location to add
         * @param string $lastmod The last modified date
         * @param float $priority The priority between 0.0 and 1.0
         * @param string $changefreq Change frequency
         */
        function addLocation($location,$lastmod=null,$priority=0.5,$changefreq=null) {

            $this->locations[] = array(
                'url' => $this->base.$location,
                'lastmod' => $lastmod,
                'priority' => $priority,
                'changefreq' => $changefreq
            );

        }

        /**
         * Outputs the sitemap to the client. Sets the appropriate content
         * type before sending.
         */
        function output() {
            $doc = new DOMDocument('1.0', 'UTF-8');
            $nod = $doc->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9','urlset');
            foreach($this->locations as $loc) {
                $url = $doc->createElement('url');
                $url->appendChild( $doc->createElement('loc',$loc['url']) );
                if ($loc['changefreq']) $url->appendChild( $doc->createElement('changefreq',$loc['changefreq']) );
                if ($loc['lastmod']) $url->appendChild( $doc->createElement('lastmod',$loc['lastmod']) );
                $url->appendChild( $doc->createElement('priority',$loc['priority']) );
                $nod->appendChild( $url );
            }
            $doc->appendChild($nod);
            response::contentType('text/xml');
            echo $doc->saveXML();
        }

    }
