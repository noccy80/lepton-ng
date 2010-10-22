<?php __fileinfo("Site Management", array(
    'author' => 'Christopher Vagnetoft <noccy@chillat.net>',
    'version' => '1.0',
    'updater' => null
));

class SiteManagementActions {

    static $help = array(
        'deploy' => 'Deploy a Lepton instance'
    );
    function _info($cmd) { return self::$help[$cmd->name]; }

    function _copy($from,$to) {
        if (@file_exists($to)) {
            console::writeLn('Warning: File %s already exists, so skipping...', $to);
            return;
        }
        console::writeLn('Installing %s...', $to);
        if (!@copy($from,$to)) {
            console::writeLn('Error: Could not create file %s!', $to);
        }
    }
    function _mkdir($dir,$chmod=null) {
        console::writeLn('Creating directory %s...', $dir);
        if (!@mkdir($dir)) {
            console::writeLn('Error: Could not create directory %s!', $dir);
            return;
        }
        if ($chmod) chmod($dir,$chmod);
    }
    function _symlink($target,$link) {
        console::writeLn('Installing symlink %s...', $link);
        if (!@symlink($target,$link)) {
            console::writeLn('Error: Could not create symlink %s to %s!', $link, $target);
            return;
        }
    }

    function deploy() {
        // if (!file_exists('app')) {
            console::writeLn('Deploying...');
            $this->_mkdir('app');
            $this->_mkdir('app/config');
            $this->_mkdir('app/models');
            $this->_mkdir('app/controllers');
            $this->_mkdir('app/views');
            $this->_mkdir('cache',0777);
            $this->_symlink(SYS_PATH, 'sys');
            $this->_symlink(SYS_PATH.'../docs', 'docs');
            $this->_symlink(SYS_PATH.'../dist', 'dist');
            $this->_symlink(SYS_PATH.'../bin', 'bin');
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

