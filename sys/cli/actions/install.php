<?php module("Install Application", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));

class ApplicationAction extends Action {
    private $extn;
    public static $commands = array(
            'install' => array(
                'arguments' => '',
                'info' => 'Install application'
            )
    );

    public function install() {

        actions::invoke('database',array('initialize'));
        actions::invoke('database',array('import'));
        if (config::has('installer.class')) {
            $ic = config::get('installer.class');
            $c = new $ic();
            $c->install();
        }

    }

}

actions::register(
    new ApplicationAction(),
    'application',
    'Application Install',
    ApplicationAction::$commands
);
