<?php __fileinfo("Package management for the Lepton utilities", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));

class PackageAction extends Action {
    private $extn;
    public static $commands = array(
        'install' => array(
            'arguments' => '[\u{packagefile.l2p}|\u{packagename}]',
            'info' => 'Install a package from the web or a local file'
        ),
        'remove' => array(
            'arguments' => '[\u{packagename}]',
            'info' => 'Remove an installed package'
        ),
        'check' => array(
            'arguments' => '',
            'info' => 'List installed packages'
        ),
        'update' => array(
            'arguments' => '',
            'info' => 'Update package list'
        ),
        'upgrade' => array(
            'arguments' => '',
            'info' => 'Upgrade all installed packages'
        ),
        'search' => array(
            'arguments' => '[\u{querystring}]',
            'info' => 'Search for a package.'
        )
    );

    public function install($packages=null) {
        using('lepton.utils.l2package');
        $pm = new L2PackageManager();
        $pkg = new L2Package($pkgname);
        $pm->installPackage($pkg);
    }
    
    public function remove($package=null) {
        using('lepton.utils.l2package');
        $pm = new L2PackageManager();
        $pkg = new L2Package($pkgname);
        $pm->removePackage($pkg);
    }

    public function check() {
        using('lepton.utils.l2package');
        $pm = new L2PackageManager();
        $pm->listPackages();
    }   
}

actions::register(
	new PackageAction(),
	'package',
	'Package manager',
	PackageAction::$commands
);
