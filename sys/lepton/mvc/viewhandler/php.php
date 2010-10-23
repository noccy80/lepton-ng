<?php __fileinfo("Pure PHP View Handler", array(
    'version' => '1.0',
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>'
));

ModuleManager::load('lepton.mvc.view');

class PlainViewHandler extends ViewHandler {
    private $path;
    function loadView($view) {
        $path = APP_PATH.'views/'.$view;
        Console::debugEx(LOG_BASIC,__CLASS__,"Attempting to invoke view from %s", $path);
        $data = $this->getViewData();
        extract($this->_data, EXTR_SKIP);
        if (file_exists($path)) {
            Console::debugEx(LOG_BASIC,__CLASS__,"Invoking as Pure PHP View");
            $this->path = $path;
        } else {
            throw new ViewNotFoundException("The view ".$view." could not be found");
        }
    }
    function display() {
        if (!headers_sent()) header('200 Content Follows', true);
        require($this->path);
    }
    function import($view) {
        $path = APP_PATH.'views/'.$view;
        include($path);
    }
}

View::$_handlers['PlainViewHandler'] = '.*\.php$';
