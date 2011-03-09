<?php

class Thumbalizer {

    private $apikey = null;
    private $cachedir = null;
    private $url = null;

    function __construct($url) {
        if (config::has('lepton.services.thumbalizer.apikey')) {
            $this->apikey = config::get('lepton.services.thumbalizer.apikey');
        }
        $this->cachedir = config::get('lepton.services.thumbalizer.cachedir',base::basePath().'cache');
        if (!file_exists($this->cachedir)) {
            throw new ConfigurationException("Thumbalizer cache directory not found, define with lepton.services.thumbalizer.cachedir");
        }
    }

    function getThumbnailSrc() {
        
    }

}
