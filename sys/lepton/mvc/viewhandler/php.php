<?php module("Pure PHP View Handler", array(
    'version' => '1.0',
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>'
));

using('lepton.mvc.view');

class PlainViewHandler extends ViewHandler {
    private $path;
    function loadView($view) {
        $path = base::expand($view,'/views');
        Console::debugEx(LOG_BASIC,__CLASS__,"Attempting to invoke view from %s", $path);
        if (file_exists($path)) {
            Console::debugEx(LOG_BASIC,__CLASS__,"Invoking as Pure PHP View");
            $this->path = $path;
        } else {
            throw new ViewNotFoundException("The view ".$view." could not be found");
        }
    }
    function display() {
        // TODO: Investigate the consequences of forcing HTTP/1.1 here as just "200" triggers a fatal error on some systems
        if (!headers_sent()) header('HTTP/1.1 200 Content Follows', true);
        $data = $this->getViewData();
        extract($this->_data, EXTR_SKIP);
        include($this->path);
    }
    function import($view) {
        $path = base::appPath().'/views/'.$view;
        include($path);
    }
}

ViewHandler::register('PlainViewHandler','.*\.php$');

