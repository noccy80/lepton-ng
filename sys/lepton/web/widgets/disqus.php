<?php __fileinfo("Disqus Widget");

using('lepton.web.element');
using('lepton.web.widgets');

class DiscusWidget extends HtmlWidget {
    function __construct(array $params) {
        $this->_shortname = $params['shortname'];
        $this->_identifier = $params['identifier'];
        $this->_permalink = $params['permalink'];
    }
    function render() {
        echo '<div id="disqus_thread"></div>';
        echo '<script type="text/javascript">';
        echo '    var disqus_shortname = \''.$this->_shortname.'\';';
        echo '    var disqus_identifier = \''.$this->_identifier.'\';';
        echo '    var disqus_url = \''.$this->_permalink.'\';';
        echo '(function() { ';
        echo "var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;";
        echo "dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';";
        echo "(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);";
        echo '})();';
        echo '</script>';
        echo '<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>';
        echo '<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>';
        
    }
}

