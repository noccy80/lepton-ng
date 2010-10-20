<?php __fileinfo("Site Management", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));

class SiteManagementActions {

    static $help = array(
        'deploy' => 'Deploy a Lepton instance'
    );
    function _info($cmd) { return TestActions::$help[$cmd->name]; }

    function _copy($from,$to) {
        console::writeLn('Installing %s...', $to);
        copy($from,$to);
    }
    function _mkdir($dir) {
        console::writeLn('Creating directory %s...', $dir);
        mkdir($dir);
    }
    function _symlink($target,$link) {
        console::writeLn('Installing symlink %s...', $link);
        symlink($target,$link);
    }

    function deploy() {
        // if (!file_exists('app')) {
            console::writeLn('Deploying...');
            $this->_mkdir('app');
            $this->_mkdir('app/config');
            $this->_mkdir('app/models');
            $this->_mkdir('app/controllers');
            $this->_mkdir('app/views');
            $this->_symlink(SYS_PATH, 'sys');
            $this->_symlink(SYS_PATH.'../docs', 'docs');
            $this->_symlink(SYS_PATH.'../dist', 'dist');
            $this->_copy(SYS_PATH.'/../dist/index.dist','index.php');
            $this->_copy(SYS_PATH.'/../dist/htaccess.dist','.htaccess');
            $g = glob(SYS_PATH.'../app/config/*.orig');
            foreach($g as $file) {
            	$fileto = 'app/config/'.basename($file);
                $this->_copy($file,$fileto);
            }
        //} else {
        //    console::writeLn('Site already deployed, try Update.');
        //}
    }

}

config::push('lepton.cmd.actionhandlers','SiteManagementActions');

