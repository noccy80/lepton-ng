<?php __fileinfo("Robots.txt generation classes");

/**
 * @class Robots
 * @brief Generates and outputs robots.txt robot access control files.
 *
 * To use, create a new instance of the Robots class and assign the permissions
 * through the provided methods setSitemap, addAllow, addDisallow and
 * setCrawlDelay. Finally output the file using the output method.
 *
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @package lepton.web
 * @license GNU GPL v2
 */
class Robots {

    private $_rules = null;
    private $_sitemap = null;

    /**
     * @brief Assign a sitemap to the robots file.
     *
     * @param String $sitemap The URL of the site map to assign
     */
    function setSitemap($sitemap) {
        $this->__sitemap = $sitemap;
    }

    /**
     * @brief Add an allow rule
     *
     * @param String $resource The resource to configure
     * @param String $agent User agent match
     */
    function addAllow($resource, $agent='*') {
        $this->_rules[$agent]['allow'][] = $resource;
    }

    /**
     * @brief Add a disallow rule
     *
     * @param String $resource The resource to configure
     * @param String $agent User agent match
     */
    function addDisallow($resource, $agent='*') {
        $this->_rules[$agent]['disallow'][] = $resource;
    }

    /**
     * @brief Set the crawling delay
     *
     * @param Int $delay Delay in minutes
     * @param String $agent User agent match
     */
    function setCrawlDelay($delay, $agent='*') {
        $this->_rules[$agent]['crawldelay'] = $resource;
    }

    /**
     * @brief Tell robots to access the site during a specific time span
     *
     * @param String $starttime Starting time
     * @param String $endtime Ending time
     * @param String $agent User agent match
     */
    function setVisitTime($starttime, $endtime, $agent='*') {
        // Visit-time: hhmm-hhmm
    }

    /**
     * @brief Tell robots to throttle their request rate
     *
     * @param Int $pages Pages to request per interval
     * @param Int $per Interval in minutes
     * @param String $agent User agent match
     */
    function setRequestRate($pages, $per, $agent='*') {
        // Request-rate: pages/seconds [hhmm-hhmm]
    }

    /**
     * @brief Output the robots.txt file to the browser
     */
    function output() {
        response::contentType('text/plain');
        foreach($this->_rules as $agent=>$ruleset) {
            printf("User-Agent: %s\n", $agent);
            foreach((array)$ruleset['allow'] as $rule) {
                printf("Allow: %s\n", $rule);
            }
            foreach((array)$ruleset['disallow'] as $rule) {
                printf("Disallow: %s\n", $rule);
            }
        }
    }

}

