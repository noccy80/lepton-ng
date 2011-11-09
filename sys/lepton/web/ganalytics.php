<?php

class GoogleAnalytics {

    function onHeader($event,$data) {

        if (config::get('ganalytics.account') == NULL) {
            logger::err("No Google Analytics account key set in configuration");
            return;
        }
        
        printf("<script type=\"text/javascript\">");
        printf("var _gaq = _gaq || [];");
        printf("_gaq.push(['_setAccount', '%s']);", config::get('ganalytics.account'));
        printf("_gaq.push(['_setDomainName', '%s']);", request::getDomain());
        printf("_gaq.push(['_trackPageview']);");
        printf("</script>");

        printf("<script type=\"text/javascript\"> (function() { var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s); })();  </script>");

    }

}

using('lepton.mvc.document');
event::register(document::EVENT_HEADER, new EventHandler('GoogleAnalytics','onHeader'));